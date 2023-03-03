<?php

use SpotifyWebAPI\Session;
use Illuminate\Http\Request;
use SpotifyWebAPI\SpotifyWebAPI;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Route;
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

// first:
Route::get('auth', [SpotifyController::class, 'auth', 'auth']);

// redirects here:
Route::get('token-test', [SpotifyController::class, 'renderToken']);

// functional links:
Route::get('playlists', [SpotifyController::class, 'myPlaylists']);
Route::get('my-albums', [SpotifyController::class, 'myAlbums']);

// home page:
Route::get('index', function () {
    return view('index');
});

// under construction:
Route::get('my-tracks', [SpotifyController::class, 'myLikedSongs']);

// get 'my saved tracks' to the database
Route::get('save-my-tracks', [SpotifyController::class, 'getSavedTracksToDatabase']);

Route::get('test-the-bot', [SpotifyController::class, 'testTgBot']);



// old tests:
Route::get('/', function () {
    return view('welcome');
});

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
