<?php

use App\Models\Song;
use App\Models\User;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\SpotifyToken;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SpotifyController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// tests:
Route::get('/', function () {
    return view('welcome');
});
Route::get('test', [SpotifyController::class, 'test']);
// was a part of an old middleware
Route::get('token-test', [SpotifyController::class, 'renderToken']);

// works fine:
Route::get('my-playlists', [SpotifyController::class, 'myPlaylists'])->name('playlists');
Route::get('my-playlists/{playlistId}', [SpotifyController::class, 'renderPlaylist'])->name('playlist.songs');

// auth:
Route::get('/dashboard', function() {
    return view('dashboard', ['user' => auth()->user()]);
})->name('dashboard');
Route::get('spotify-code', function(Request $request) {
    
    $session = new Session(
        env('SPOTIFY_CLIENT_ID'),
        env('SPOTIFY_CLIENT_SECRET'),
        env('REDIRECT_URI')
    );

    $code = $request->get('code');

    // code is used to get access token
    $session->requestAccessToken($code);
    
    SpotifyToken::firstOrCreate(
        [
            'code' => $code
        ],
        [
            'access_token' => $session->getAccessToken(),
            'refresh_token' => $session->getRefreshToken(),
            'expiration' => $session->getTokenExpiration(),
        ]
    );

    return redirect(route('dashboard'));
});
Route::post('spotify-authorize', function() {
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
    
});



// old routes
// first:
Route::get('auth', [SpotifyController::class, 'auth', 'auth']);

// redirects here:

// functional links:
// Route::get('playlists', [SpotifyController::class, 'myPlaylists']);
Route::get('my-albums', [SpotifyController::class, 'myAlbums']);

// home page:
Route::get('index', function () {
    return view('index');
})->name('index');

// under construction:
Route::get('my-tracks', [SpotifyController::class, 'myLikedSongs']);

// now in progress:
Route::get('playlist-titles', [SpotifyController::class, 'playlistTitles']);

// get 'my saved tracks' to the database
Route::get('save-my-tracks', [SpotifyController::class, 'getSavedTracksToDatabase']);

Route::get('save-my-playlists', [SpotifyController::class, 'savePlaylists']);
Route::get('save-playlist-tracks', [SpotifyController::class, 'savePlaylistTracks']);

// Route::get('my-playlists/{playlistId}', function ($playlistId) {
    //     $playlist = Playlist::where('id', $playlistId)->first();
    //     // $playlistSongs = $playlist->songs;
    //     return view('playlist-songs', [
        //         'playlistSongs' => $playlist->songs,
        //     ]);
        // })->name('playlist.songs');



// old tests:
// Route::get('test-the-bot', [SpotifyController::class, 'testTgBot']);

// Route::get('controller-test/', [Controller::class, 'index']);

// Route::get('token-test', [SpotifyController::class, 'token']);

// transfered to SpotifyController
// Route::get('test/', function () {
//     $session = new Session(
//          env('SPOTIFY_CLIENT_ID'),
//          env('SPOTIFY_CLIENT_SECRET'),
//         // 'ngrok-redirect-here'
//         env('REDIRECT_URI')
//     );


//     $options = [
//         'scope' => [
//             'user-read-email',
//             'user-read-private',
//         ],
//     ];

//     header('Location: ' . $session->getAuthorizeUrl($options));
//     die();
// });

// Route::get('test2/', function (Request $request) {
//     $session = new Session(
//          env('SPOTIFY_CLIENT_ID'),
//          env('SPOTIFY_CLIENT_SECRET'),
//          env('REDIRECT_URI')
//     );

//     $session->requestAccessToken($request->get('code')); 
//     // $session->requestAccessToken($code);
//     $api = new SpotifyWebAPI(['auto_refresh' => true], $session);
//     $api->setAccessToken($session->getAccessToken());

//     return $api->me();
// });
