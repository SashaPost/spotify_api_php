<?php

namespace App\Http\Controllers;

use Exception;
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

class SpotifyController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public string $token = "";

    public function __construct(Request $request)
    {
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
            ],
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
        return view('template-test', ['token' => $token]);
    }

    public function myPlaylists(Request $request)
    {
        $token = SessionLaravel::get('spotify_token');
        $spot_sess = new SpotifyWebAPI();

        $playlistsFormatted = [];
        $spot_sess->setAccessToken($token);
        $playlists = $spot_sess->getMyPlaylists();

        foreach ($playlists->items as $playlist) {
            $tracks = Cache::get($playlist->id);

            if (!$tracks) {
                $playlistTracks = $spot_sess->getPlaylistTracks($playlist->id);
                $tracks = $playlistTracks->items;
                Cache::set($playlist->id, $tracks);
            }

            $playlistsFormatted[] = [
                'name' => $playlist->name,
                'tracks' => $tracks
            ];
        }

        return view('playlists', ['playlists' => $playlistsFormatted]);
    }
}
