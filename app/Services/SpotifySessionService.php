<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session as SessionLaravel;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;


class SpotifySessionService {
    public function instantiateSession() {
        $token = SessionLaravel::get('spotify_token');
        $spot_sess = new SpotifyWebAPI();
        $spot_sess->setAccessToken($token);
        return $spot_sess;
    }
}
