<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Song;
use App\Http\Middleware\SpotifyToken;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use SpotifyWebAPI\Session;
use Illuminate\Http\Request;
use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session as SessionLaravel;
use SpotifyWebAPI\SpotifyWebAPIException;
use Illuminate\Support\Facades\Cache;

use App\Services\TimeConverter;
use App\Services\SpotifyFactory;
use App\Models\SpotifyToken as SpotifyTokenModel;


// use TimeConverter;


class SpotifyController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public string $token = "";
    // protected $timeConverter;
    // protected $spotifyFactory;

    public function __construct(
        Request $request,
        protected TimeConverter $timeConverter,
        protected SpotifyFactory $spotifyFactory,
        protected SpotifyWebAPI $spotifyClient
    ) {
        $session = new Session(
            env('SPOTIFY_CLIENT_ID'),
            env('SPOTIFY_CLIENT_SECRET'),
            env('REDIRECT_URI')
        );

        $options = [
            'scope' => [
                'playlist-read-private',
                'user-read-private'
            ],
        ];

        // $this->timeConverter = $timeConverter;
        // $this->spotifyFactory = $spotifyFactory;

        // header('Location: ' . $session->getAuthorizeUrl($options));
        // $this->token = $session->requestAccessToken($request->get('code'));
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
            ],
            'auto_refresh' => true
        ];

        // new \Illuminate\ Support\Facades\Session::put('spotify_token', );

        return redirect($session->getAuthorizeUrl($options));
    }

    public function test()
    {
        return SessionLaravel::get('spotify_token');
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
        // $tokens = [];
        // if (Auth::check()) {
        //     // $user = Auth::user();
        //     $token_table = new SpotifyTokenModel();
        //     $tokens = $token_table->all();
        // }
        return view('template-test', ['token' => $token]);
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

        $playlistsFormatted = [];
        $spot_sess->setAccessToken($token);
        $playlists = $spot_sess->getMyPlaylists();

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
        // $options = [
        //     'auto_refresh' => true
        // ];

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

        // add an option to render list divited on pages by 100 records
        // $paginated_tracks = collect($tracks_properties)->chunk(100);

        // Configure the Cache usage

        return view('my-tracks', [
            'tracks_properties' => $tracks_properties
        ]);
    }

    // trigger for the job:
    // public function handle(array $payload)
    // {
    //     $saved_tracks = $spot_sess->getMySavedTracks([
    //         'limit' => $payload['limit'],
    //         'offset' => $payload['offset']
    //     ]);

    //     if (!($offset > $saved_tracks->total)) {
    //         $offset = $limit;
    //         ParseJob::dispatch([
    //             'limit' => $limit,
    //             'offset' => $offset
    //         ]);
    //     }
    // }

    public function getSavedTracksToDatabase(Request $request)
    {   
        $token = SessionLaravel::get('spotify_token');
        $spot_sess = new SpotifyWebAPI();
        $spot_sess->setAccessToken($token);
        $saved_tracks = $this->spotifyClient->getMySavedTracks();

        $limit = 50;
        $offset = 0;
        $all_tracks = [];

        while ($saved_tracks = $this->spotifyClient->getMySavedTracks([
            'limit' => $limit,
            'offset' => $offset
        ])) {
            $all_tracks = array_merge($all_tracks, $saved_tracks->items);
            $offset += $limit;

            if ($offset > $saved_tracks->total) {
                break;
            }
        }

        foreach ($all_tracks as $track) {

            $newSong = new Song();
            $newSong->name = $track->track?->name;
            $newSong->duration_ms = $track->track?->duration_ms;
            $newSong->spotify_url = $track->track?->uri;
            $newSong->isrc = $track->track?->external_ids->isrc;
            $newSong->added_at = $track->added_at;
            $newSong->spotify_id = $track->track?->id;

            $newArtist = Artist::firstOrCreate(
                ['spotify_id' => $track->track?->artists[0]->id], 
                [
                    'name' => $track->track?->artists[0]->name,
                    'spotify_url' => $track->track?->artists[0]->external_urls->spotify
                ]
            );
            $newSong->artist()->associate($newArtist);

            $newAlbum = Album::firstOrCreate(
                ['spotify_id' => $track->track?->album->id],
                [
                    'name' => $track->track?->album->name,
                    'release_date' => $track->track?->album->release_date,
                    'artist' => $track->track?->artists[0]->name,
                    'spotify_url' => $track->track?->album->external_urls->spotify,
                    'total_tracks' => $track->track?->album->total_tracks,
                    'artist_id' => $newArtist->id
                ]
            );
            $newSong->album()->associate($newAlbum);
            $newSong->save();
       }

        $tracks = Song::all();

        return view('save-my-tracks', [
            'tracks' => $tracks
        ]);
    }
    // public function getSavedTracksToDatabase(Request $request)
    // {   
    //     $token = SessionLaravel::get('spotify_token');
    //     $spot_sess = new SpotifyWebAPI();
    //     $spot_sess->setAccessToken($token);
    //     $saved_tracks = $this->spotifyClient->getMySavedTracks();

    //     // $spot_sees = $this->spotifyFactory;
    //     // $spot_sees->createSpotifySession();
    //     // $saved_tracks = $spot_sees->getMySavedTracks();

    //     $limit = 50;
    //     $offset = 0;
    //     $all_tracks = [];

    //     while ($saved_tracks = $this->spotifyClient->getMySavedTracks([
    //         'limit' => $limit,
    //         'offset' => $offset
    //     ])) {
    //         $all_tracks = array_merge($all_tracks, $saved_tracks->items);
    //         $offset += $limit;

    //         if ($offset > $saved_tracks->total) {
    //             break;
    //         }
    //     }

    //     // the Job:
    //     // ParseJob::dispatch([
    //     //     'limit' => $limit,
    //     //     'offset' => $offset
    //     // ]);



    //     // save this to the database from $all_tracks:
    //     // - track_name;
    //     // - track_spotify_id;
    //     // - track_spotify_url;
    //     // - artist_name;
    //     // - artist_spotify_id;
    //     // - artist_spotify_url;
    //     // - duration; 
    //     // - release_date;
    //     // - genre(?);
    //     // - album_name;
    //     // - album_spotify_id;
    //     // - album_spotify_url;
    //     // - album_total_tracks;
    //     // - isrc;
    //     // - added_at(probably need new migration);
    //     // - explicit(bool; probably need new migration);
    //     // - popularity(probably need new migration);
    //     // - album_covers(probably need new migration);

    //     // to be fixed:
    //     // $tracks_properties = [];
    //     foreach ($all_tracks as $track) {

    //         $newSong = new Song();
    //         $newSong->name = $track->track?->name;
    //         $newSong->duration_ms = $track->track?->duration_ms;
    //         $newSong->spotify_url = $track->track?->uri;
    //         $newSong->isrc = $track->track?->external_ids->isrc;
    //         $newSong->added_at = $track->added_at;
    //         $newSong->spotify_id = $track->track?->id;

    //         $newArtist = Artist::firstOrCreate(
    //             ['spotify_id' => $track->track?->artists[0]->id], // first argument is an identifier for the 'firstOrCreate' method;
    //             [
    //                 'name' => $track->track?->artists[0]->name,
    //                 'spotify_url' => $track->track?->artists[0]->external_urls->spotify
    //             ]
    //         );
    //         $newSong->artist()->associate($newArtist);

    //         $newAlbum = Album::firstOrCreate(
    //             ['spotify_id' => $track->track?->album->id],
    //             [
    //                 'name' => $track->track?->album->name,
    //                 'release_date' => $track->track?->album->release_date,
    //                 'artist' => $track->track?->artists[0]->name,
    //                 'spotify_url' => $track->track?->album->external_urls->spotify,
    //                 'total_tracks' => $track->track?->album->total_tracks,
    //                 'artist_id' => $newArtist->id
    //             ]
    //         );
    //         $newSong->album()->associate($newAlbum);
    //         $newSong->save();

    //         // $duration_str = implode(':', $this->timeConverter->convertMilliseconds($duration_ms));
    //         // $newSong->duration_ms = $duration_str;

    //         // $newSong->artist_id = $track->track?->duration_ms; // no need; have to modify tables to implement
    //         // $newSong->album_id = $track->track?->duration_ms + 1;
    //         // $album = $track->track?->album->name;
    //         // $release_date = $track->track?->album->release_date;



    //         // $newPlaylist = Playlist::firstOrCreate(
    //         //     ['' => ],
    //         // );
    //         // $newSong->playlist()->associate($newPlaylist);


    //         // $user = User::firstOrCreate([

    //         // ]);
    //         // $newSong->user()->associate($user);
    //     }

    //     $tracks = Song::all();

    //     return view('save-my-tracks', [
    //         // 'all_tracks' => $all_tracks
    //         'tracks' => $tracks
    //     ]);
    // }
}
