<?php

namespace App\Http\Controllers;

use Exception;

use App\Models\Song;
use App\Models\User;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\SpotifyToken;
use App\Models\PlaylistDuration;

use SpotifyWebAPI\Session;
use Illuminate\Http\Request;
use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


use App\Services\TimeConverter;
use App\Services\SpotifySessionService;
use App\Services\CreateIfNotService;

use SpotifyWebAPI\SpotifyWebAPIException;
use Illuminate\Foundation\Bus\DispatchesJobs;

// use Telegram\Bot\Api;
// use App\Http\Middleware\SpotifyToken;
// use Telegram\Bot\Laravel\Facades\Telegram;
// use App\Http\Middleware\SpotifyTokenAutorefresh;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session as SessionLaravel;

use App\Jobs\UpdateSavedSongsData;
use App\Jobs\UpdateSavedPlaylistsData;
use App\Jobs\UpdatePlaylistTracksData;
use App\Jobs\UpdatePlaylistDuration;



class SpotifyController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public string $token = "";

    public function __construct(
        Request $request,
        protected TimeConverter $timeConverter,
        protected SpotifyWebAPI $spotifyClient,
        protected SpotifySessionService $spotifySessionService,
        protected CreateIfNotService $createIfNotService,
    ) {
        
    }
    
    // works fine:
    public function myPlaylists(Request $request) 
    {
        ini_set('max_execution_time', 720); // 300 seconds = 5 minutes

        UpdateSavedPlaylistsData::dispatch();
        
        $playlists = Playlist::paginate(50);    // simplePaginate()

        $totalCount = Playlist::count();
        // $playlists = Playlist::paginate(30);

        $user = auth()->user();

        return view('my-playlists', [
            'playlists' => $playlists,
            'totalCount' => $totalCount,
            'user' => $user,
        ]);
    }

    public function owedPlaylists(Request $request)
    {
        UpdateSavedPlaylistsData::dispatch();

        // current 'parent' template requires this in every method:
        $api = $this->spotifySessionService->instantiateSession();
        $user = $api->me();

        // need to fix this;
        // use the Playlist model method 'owners()' - fill this at first;
        $playlists = Playlist::where('owner_id', $user->id)->get(); 
        $count = count($playlists);   

        return view('owed-playlists', [
            'playlists' => $playlists,
            'count' => $count,
            'user' => $user,
        ]);
    }

    public function renderPlaylist(
        Request $request,
        $playlistId,
    )
    {   
        // add a check to dispatch 'UpdatePlaylistTracksData':
        UpdatePlaylistTracksData::dispatch($playlistId);
        
        $playlist = Playlist::where('id', $playlistId)->first();
        $playlistSongs = $playlist->songs;
        // $playlistDuration = $playlist->duration->duration_ms;

        // not sure if need this (dispatches in 'UpdateSavedPlaylistsData')
        if($playlist->duration === null)
        {
            UpdatePlaylistDuration::dispatch($playlistId);
        }
        // correct this if needed:
        // try {
            //     $fetchedPlaylist->duration;
            // } catch (Throwable $e) {
            //     UpdatePlaylistDuration::dispatch($playlistId);
            // }

        // $playlistDuration = PlaylistDuration::where('playlist_id', $playlistId)->first();
        $playlistDuration = $playlist->duration;

        return view('playlist-songs', [
            'name' => $playlist->name,
            'description' => $playlist->description,
            'total_tracks' => $playlist->total_tracks,
            'duration' => $playlistDuration->duration_ms,

            'playlistSongs' => $playlistSongs,
        ]);
    }



    // new tests here:

    public function renderToken(Request $request)
    {
        // $token = SessionLaravel::get('spotify_token');
        // dump(SessionLaravel::all());   

        // dump(SpotifyToken::latest()->first());

        $tokens = SpotifyToken::latest()->first();

        $user = auth()->user();
        $user_token = $user->spotify_tokens;

        $api = $this->spotifySessionService->instantiateSession();
        $me = $api->me();
        
        return view('template-test', [
            'tokens' => $tokens,
            'user_token' => $user_token,
            'me' => $me,
        ]);
    }
    public function test(Request $request)
    {
        $api = $this->spotifySessionService->instantiateSession();
        $me = $api->me();
        
        $playlistsSpotify = $api->getMyPlaylists();
        
        $playlists = Playlist::all();
        
        $playlist = Playlist::latest()->first();

        UpdatePlaylistDuration::dispatch($playlist->spotify_id);

        $playlistSongs = $playlist->songs;
        // foreach $playlistSongs $totalDuration += $song->duration_ms
        $totalDurationMs = 0;
        foreach ($playlistSongs as $song)
        {
            $totalDurationMs += $song->duration_ms;
        }

        $playlistDuration = $this->createIfNotService->playlistDuration($playlist->id, $totalDurationMs);

        $pD = $playlist->duration;

        return view('test', [
            'me' => $me,
            // 'playlists' => $playlistsSpotify,
        ]);
    }

    public function myAlbums(Request $request)
    {
        $token = SessionLaravel::get('spotify_token');
        $spot_sess = new SpotifyWebAPI();
        $spot_sess->setAccessToken($token);
        
        $limit = 50;
        $offset = 0;
        $albums = [];

        while ($response = $spot_sess->getMySavedAlbums([
            'limit' => $limit,
            'offset' => $offset
        ])) {
            $albums = array_merge($albums, $response->items);
            // break;
            $offset += $limit;

            if ($offset > $response->total) {
                break;
            }
        }

        return view('my-albums', [
            'albums' => $albums
            // 'test' => $test
        ]);
    }



    
    
    public function playlistTitles(Request $request) 
    {
        $session = $this->spotifySessionService->instantiateSession();
        $playlists = $session->getMyPlaylists();

        $total = $playlists->total;

        return view('playlist-titles', [
            'playlists' => $playlists,
            'total' => $total
        ]);
    }

    // create a job to automatically put all the user data into database
    // after login;
    // will trigger 'savePlaylistTracks'
    public function savePlaylists(Request $request)
    {
        $api = $this->spotifySessionService->instantiateSession();
        $playlists = $api->getMyPlaylists();
        $total = $playlists->total;
        
        $limit = 50;
        $offset = 0;
        $all_playlists = [];

        while ($playlists = $api->getMyPlaylists([
            'limit' => $limit,
            'offset' => $offset
        ])) 
        {
            $all_playlists = array_merge($all_playlists, $playlists->items);
            $offset += $limit;

            if ($offset > $playlists->total)
            {
                break;
            }
        }

        foreach ($all_playlists as $playlist)
        {
            $new_playlist = $this->createIfNotService->playlist($playlist);
        }

        $fetched_playlists = Playlist::all();

        return view('save-my-playlists', [
            'total' => $total,
            'playlists' => $playlists,
            'all_playlists' => $all_playlists,
            'fetched_playlists' => $fetched_playlists
        ]);
    }

    // make job from this - done
    // check the job 'UpdatePlaylistTracksData'
    public function savePlaylistTracks(
        Request $request,
        // $playlistSpotifyId
    )
    {
        $api = $this->spotifySessionService->instantiateSession();   // not in use 'cause 'UpdateSavedPlaylistsData' shoud perform this;
        // job is not working yet (something wrong with the construct method)
        // fixed
        UpdateSavedPlaylistsData::dispatch();

        // $fetched_playlists = Playlist::all();

        // $playlist = Playlist::latest()->first();
        $playlist = Playlist::latest()->skip(1)->first();

        $playlist_tracks = $api->getPlaylistTracks($playlist['spotify_id']);

        $limit = 50;
        $offset = 0;
        $total = $playlist_tracks->total;

        $tracks = [];

        while ($playlist_tracks = $api->getPlaylistTracks($playlist['spotify_id'], [
            'limit' => $limit,
            'offset' => $offset
        ]))
        {
            $tracks = array_merge($tracks, $playlist_tracks->items);
            $offset += $limit;

            if ($offset > $total)
            {
                break;
            }
        }

        foreach ($tracks as $track)
        {
            $newSong = $this->createIfNotService->songFromSong($track);
            
            $newArtist = $this->createIfNotService->artistFromSong($track);
            $newSong->artist()->associate($newArtist);

            $newAlbum = $this->createIfNotService->albumFromSong($track);
            $newSong->album()->associate($newAlbum);

            $playlist->songs()->syncWithoutDetaching($newSong);
            $playlist->artists()->syncWithoutDetaching($newArtist);
        }
        return $playlist->songs;
    }

    public function auth(Request $request)
    {
        $session = new Session(
            env('SPOTIFY_CLIENT_ID'),
            env('SPOTIFY_CLIENT_SECRET'),
            env('REDIRECT_URI')
        );

        $options = [
            'scope' => [
                'playlist-read-private',
                'user-read-private',
                'user-read-email',
                'playlist-read-collaborative',
                'user-follow-read',
                'user-library-read'
            ]
        ];

        return redirect($session->getAuthorizeUrl($options));
    }
        
    public function getSavedTracksToDatabase(Request $request)
    {   
        UpdateSavedSongsData::dispatch();

        $tracks = Song::all();

        return view('save-my-tracks', [
            'tracks' => $tracks,
        ]);
    }



    // public function token(Request $request)
    // {
    //     if (1) {
    //     }
    //     return SessionLaravel::get('spotify_token');
    // }

    // needs improvement
    // doesn't even show the length of playlist
    // or would be even better to write another method
    // to render the full playlist's contents and other info
    // and the other one to list all playlists and redirects to previous after choosing one of them
    // public function myPlaylists(Request $request)
    // {

    //     // set in the SpotifyToken middleware after /auth
    //     /** @see SpotifyToken */
    //     $spotifyTokens = SpotifyToken::find(1);

    //     $accessToken = $spotifyTokens->access_token;

    //     if ((string)now() < $spotifyTokens->expiration) {
    //         $session = new Session(
    //             'CLIENT_ID',
    //             'CLIENT_SECRET',
    //             'REDIRECT_URI'
    //         );

    //         $session->refreshAccessToken($spotifyTokens->refresh_token);

    //         $accessToken = $session->getAccessToken();

    //         $spotifyTokens->update([
    //             'access_token' => $accessToken,
    //             'refresh_token' => $session->getRefreshToken(),
    //             'expiration' => $session->getTokenExpiration(),
    //         ]);
    //     }


        
    //     $spot_sess = new SpotifyWebAPI();
    //     $spot_sess->setAccessToken($accessToken);




    //     // $spot_sess->setRefreshToken($spotifyTokens->refresh_token);
    //     // $spot_sess->setSession($session);

    //     try {
    //         $playlists = $spot_sess->getMyPlaylists();
    //     } catch(SpotifyWebAPIException $e) {
    //         if ($e->hasExpiredToken()) {
    //             $session = new Session(
    //                 'CLIENT_ID',
    //                 'CLIENT_SECRET',
    //                 'REDIRECT_URI'
    //             );
    
    //             $session->refreshAccessToken($spotifyTokens->refresh_token);
    
    //             $accessToken = $session->getAccessToken();
    
    //             $spotifyTokens->update([
    //                 'access_token' => $accessToken,
    //                 'refresh_token' => $session->getRefreshToken(),
    //                 'expiration' => $session->getTokenExpiration(),
    //             ]);
    //         }

    //         $playlists = $spot_sess->getMyPlaylists();
    //     }

    //     $playlistsFormatted = [];

    //     foreach ($playlists->items as $playlist) {
    //         // pick up cached tracks for playlist to avoid requests for each playlist all the time
    //         $tracks = Cache::get($playlist->id);

    //         if (!$tracks) {
    //             $playlistTracks = $spot_sess->getPlaylistTracks($playlist->id);
    //             $tracks = $playlistTracks->items;
    //             // putting tracks from playlist into cache identified by playlist ID
    //             Cache::set($playlist->id, $tracks);
    //         }

    //         $playlistsFormatted[] = [
    //             'name' => $playlist->name,
    //             'tracks' => $tracks
    //         ];
    //     }

    //     // added by me:
    //     $my_acc = $spot_sess->me();
    //     $my_name = $my_acc->display_name;

    //     return view('playlists', [
    //         'playlists' => $playlistsFormatted,
    //         'my_name' => $my_name
    //     ]);
    // }

    // created a Job for that:
    public function myLikedSongs(Request $request)
    {
        $token = SessionLaravel::get('spotify_token');
        $spot_sess = new SpotifyWebAPI();
        $spot_sess->setAccessToken($token);

        $saved_tracks = $spot_sess->getMySavedTracks();

        $limit = 50;
        $offset = 0;
        $all_tracks = [];

        while ($saved_tracks = $spot_sess->getMySavedTracks([
            'limit' => $limit,
            'offset' => $offset
        ])) {
            $all_tracks = array_merge($all_tracks, $saved_tracks->items);
            $offset += $limit;

            if ($offset > $saved_tracks->total) {
                break;
            }
        }

        $tracks_properties = [];
        foreach ($all_tracks as $track) {
            $track_name = $track->track?->name;
            $artist = $track->track?->album->artists[0]->name;
            $album = $track->track?->album->name;
            $release_date = $track->track?->album->release_date;
            $duration_ms = $track->track?->duration_ms;
            $isrc = $track->track?->external_ids->isrc;
            $spotify_id = $track->track?->id;
            $uri = $track->track?->uri;

            $duration = $this->timeConverter->convertMilliseconds($duration_ms);

            array_push($tracks_properties, array(
                'track_name' => $track_name,
                'artist' => $artist,
                'album' => $album,
                'release_date' => $release_date,
                'duration' => $duration,
                'isrc' => $isrc,
                'spotify_id' => $spotify_id,
                'uri' => $uri
            ));
        }

        // add an option to render list divided on pages by 100 records
        // $paginated_tracks = collect($tracks_properties)->chunk(100);

        // Configure the Cache usage

        return view('my-tracks', [
            'tracks_properties' => $tracks_properties
        ]);
    }



    // not gonna work - bots are unable to "communicate":
    // public function testTgBot(Request $request) {

    //     $songs = Song::take(1)->get();
        
    //     $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    //     $chatId = '@spotify_down_bot';
    //     $message = 'The Strange Boys';

    //     $telegram->sendMessage([
    //         'chat_id' => $chatId,
    //         'text' => $message
    //     ]);

    //     return response('Message sent successfully');
        
    //     // return view('test-bot', [
    //     //     'songs' => $songs
    //     // ]);
    // }

}
