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

Route::get('/', function () {
    return view('welcome');
});


Route::get('template-test/', function () {
    return view('test');
});



Route::get('controller-test/', [Controller::class, 'index']);

Route::get('auth/', [SpotifyController::class, 'auth', 'auth']);

// Route::get('token-test', [SpotifyController::class, 'token']);
Route::get('token-test', [SpotifyController::class, 'renderToken']);

Route::get('playlists', [SpotifyController::class, 'myPlaylists']);

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

Route::get('test2/', function (Request $request) {
    $session = new Session(
         env('SPOTIFY_CLIENT_ID'),
         env('SPOTIFY_CLIENT_SECRET'),
         env('REDIRECT_URI')
    );

    $session->requestAccessToken($request->get('code')); 
    // $session->requestAccessToken($code);
    $api = new SpotifyWebAPI(['auto_refresh' => true], $session);
    $api->setAccessToken($session->getAccessToken());

    return $api->me();
});
