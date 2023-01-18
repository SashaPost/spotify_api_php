<?php

namespace App\Http\Controllers;

use SpotifyWebAPI\Session;
use Illuminate\Http\Request;
use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session as SessionLaravel;


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
    
    public function auth (Request $request) {
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
        
        // new \Illuminate\ Support\Facades\Session::put('spotify_token', );
        
        return redirect($session->getAuthorizeUrl($options));
    }

    public function test()
    {
        return SessionLaravel::get('spotify_token');
    }
    

    public function token(Request $request)
    {
        if (1) {}
        return SessionLaravel::get('spotify_token');    
    }

    public function renderToken(Request $request)
    {
        $token = SessionLaravel::get('spotify_token');
        return view('template-test', ['token' => $token]);
    }

    public function myPlaylists(Request $request)
    {
        // $token = SessionLaravel::get('spotify_token');
        $token = 'BQCKImO32s3IQxdznS3f40q_21vhx6p-tXquqwqTZSKIJks4cibfDgbceO2rsP_UUK-XoDumSFw__X31u0lCT89yxTyHGm-aUG29ICJeCJtHFrTu4V0AoL9vVr0RBuF1gH1n08yDrQuEDRjQw9D2JK-BRXWCyG1M7w94a4Ygu7BRz5d184YvjbseNWIOzBpENCW7y6NWX1ve';
        $spot_sess = new SpotifyWebAPI();
        $spot_sess->setAccessToken($token);
        $playlists = $spot_sess->getMyPlaylists();
        return view('playlists', ['playlists' => $playlists]);
    }
}




?>