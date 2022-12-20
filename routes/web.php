<?php

use Illuminate\Support\Facades\Route;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Http\Request;

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

Route::get('/test', function () {
    $session = new Session(
         env('SPOTIFY_CLIENT_ID'),
         env('SPOTIFY_CLIENT_SECRET'),
        'ngrok-redirect-here'
    );


    $options = [
        'scope' => [
            'user-read-email',
            'user-read-private',
        ],
    ];

    header('Location: ' . $session->getAuthorizeUrl($options));
    die();
});

Route::get('test2', function (Request $request) {
    $session = new Session(
         env('SPOTIFY_CLIENT_ID'),
         env('SPOTIFY_CLIENT_SECRET'),
        'ngrok-redirect-here'
    );

    $session->requestAccessToken($request->get('code')); // $session->requestAccessToken($code);
    $api = new SpotifyWebAPI(['auto_refresh' => true], $session);
    $api->setAccessToken($session->getAccessToken());

    return $api->me();
});
