<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Song;
use App\Models\User;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;
use SpotifyWebAPI\Session;
use Illuminate\Http\Request;
use SpotifyWebAPI\SpotifyWebAPI;
use App\Jobs\UpdateSavedSongsData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;
// use App\Http\Middleware\SpotifyToken;
use App\Http\Middleware\SpotifyTokenAutorefresh;
use Illuminate\Support\Facades\Cache;
use SpotifyWebAPI\SpotifyWebAPIException;
// use Telegram\Bot\Laravel\Facades\Telegram;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session as SessionLaravel;

// use Telegram\Bot\Api;

use App\Services\TimeConverter;
use App\Services\SpotifySessionService;

class SpotifyController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public string $token = "";

    public function __construct(
        Request $request,
        protected TimeConverter $timeConverter,
        protected SpotifyWebAPI $spotifyClient,
        protected SpotifySessionService $spotifySessionService
    ) {
        // $session = new Session(
        //     env('SPOTIFY_CLIENT_ID'),
        //     env('SPOTIFY_CLIENT_SECRET'),
        //     env('REDIRECT_URI')
        // );

        // $options = [
        //     'scope' => [
        //         'playlist-read-private',
        //         'user-read-private'
        //     ],
        // ];
    }
    public function test(Request $request)
    {
        $session = $this->spotifySessionService->instantiateSession();
        $me = $session->me();
        return view('test', [
            'me' => $me
        ]);
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



    public function token(Request $request)
    {
        if (1) {
        }
        return SessionLaravel::get('spotify_token');
    }

    public function renderToken(Request $request)
    {
        $token = SessionLaravel::get('spotify_token');
        
        $session_keys = dump(SessionLaravel::all());

        return view('template-test', [
            'token' => $token,
            'session' => $session_keys
        ]);
    }

    // needs improvement
    // doesn't even show the length of playlist
    // or would be even better to write another method
    // to render the full playlist's contents and other info
    // and the other one to list all playlists and redirects to previous after choosing one of them
    public function myPlaylists(Request $request)
    {

        // set in the SpotifyToken middleware after /auth
        /** @see SpotifyToken */
        $token = SessionLaravel::get('spotify_token');
        $spot_sess = new SpotifyWebAPI();
        $spot_sess->setAccessToken($token);

        $playlists = $spot_sess->getMyPlaylists();

        $playlistsFormatted = [];

        foreach ($playlists->items as $playlist) {
            // pick up cached tracks for playlist to avoid requests for each playlist all the time
            $tracks = Cache::get($playlist->id);

            if (!$tracks) {
                $playlistTracks = $spot_sess->getPlaylistTracks($playlist->id);
                $tracks = $playlistTracks->items;
                // putting tracks from playlist into cache identified by playlist ID
                Cache::set($playlist->id, $tracks);
            }

            $playlistsFormatted[] = [
                'name' => $playlist->name,
                'tracks' => $tracks
            ];
        }

        // added by me:
        $my_acc = $spot_sess->me();
        $my_name = $my_acc->display_name;

        return view('playlists', [
            'playlists' => $playlistsFormatted,
            'my_name' => $my_name
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

    public function getSavedTracksToDatabase(Request $request)
    {   
        UpdateSavedSongsData::dispatch();

        $tracks = Song::all();

        return view('save-my-tracks', [
            'tracks' => $tracks,
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
