<?php

use App\Models\User;
use SpotifyWebAPI\Session;

use App\Models\SpotifyToken;

use Illuminate\Http\Request;
use SpotifyWebAPI\SpotifyWebAPI;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
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

// 21.03.2022:
Route::get('/', function () {
    return view('welcome');
});

// tests:
Route::get('/token-test', [SpotifyController::class, 'renderToken']);
Route::get('/user-playlists', [SpotifyController::class, 'owedPlaylists'])->name('user-playlists');

// DON'T TOUCH!
Route::get('/playlists', [SpotifyController::class, 'myPlaylists'])->name('playlists');
// Spotify API authorization:
Route::get('/dashboard', function() {
    return view('dashboard', ['user' => auth()->user()]);
})->name('dashboard');  
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
        // ->name('spotify-authorize')
});
Route::get('spotify-code', function(Request $request) {
    
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

    $code = $request->get('code');

    // code is used to get access token
    $session->requestAccessToken($code);

    $user = User::where('id', auth()->user()->id)->first();
    
    SpotifyToken::firstOrCreate(
        [
            'code' => $code
        ],
        [
            'access_token' => $session->getAccessToken(),
            'refresh_token' => $session->getRefreshToken(),
            'expiration' => $session->getTokenExpiration(),
            'user_id' => $user->id,
        ]
    );

    $spotifyApi = new SpotifyWebAPI($options, $session);

    $user->update([
        'spotify_name' => $spotifyApi->me()->display_name,
    ]);

    return redirect(route('dashboard'));
});
// web app login/registration:
Route::controller(LoginController::class)->group(function() {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/authenticate', 'loginRequest')->name('authenticate');
    Route::get('/register', 'register')->name('register');
    Route::post('/create-user', 'registerRequest')->name('create-user');
    // Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', 'logout')->name('logout');
});

// functional links:
Route::get('my-albums', [SpotifyController::class, 'myAlbums']);

// now in progress:
Route::get('playlist-titles', [SpotifyController::class, 'playlistTitles']);


// etu huietu snova perekomponovat':

// first:
Route::get('auth', [SpotifyController::class, 'auth', 'auth']);

// home page:
Route::get('index', function () {
    return view('index');
})->name('index');

// under construction:
Route::get('my-tracks', [SpotifyController::class, 'myLikedSongs']);


// get 'my saved tracks' to the database
Route::get('save-my-tracks', [SpotifyController::class, 'getSavedTracksToDatabase']);

Route::get('save-my-playlists', [SpotifyController::class, 'savePlaylists']);
Route::get('save-playlist-tracks', [SpotifyController::class, 'savePlaylistTracks'])->name('playlist.songs');

// old tests:
// Route::get('/login-test', function () {
//     return view('login');
// });

// Route::get('test-the-bot', [SpotifyController::class, 'testTgBot']);

// Route::get('controller-test/', [Controller::class, 'index']);

// Route::get('token-test', [SpotifyController::class, 'token']);

Route::get('test', [SpotifyController::class, 'test']);

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
