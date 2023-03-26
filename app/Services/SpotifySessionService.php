<?php

namespace App\Services;

use App\Models\User;
use App\Models\SpotifyToken;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifySessionService {

    public function __construct(
        private bool $autoRefreh = true,
        private bool $autoRetry = true,
    ) {
    }

    public function instantiateSession() {
        // $spotifyTokens = SpotifyToken::latest()->first();
        $user = User::where('id', auth()->user()->id)->first();
        $spotifyTokens = $user->spotify_tokens;
        $accessToken = $spotifyTokens->access_token;
        $refreshToken = $spotifyTokens->refresh_token;

        $options = [
            'auto_refresh' => $this->autoRefreh, 
            'auto_retry' => $this->autoRetry,
        ];

        $spotifySession = new Session(
            env('SPOTIFY_CLIENT_ID'),
            env('SPOTIFY_CLIENT_SECRET'),
            env('REDIRECT_URI')
        );

        if (now()->timestamp < $spotifyTokens->expiration) {
            $spotifySession->setAccessToken($accessToken);
            $spotifySession->setRefreshToken($refreshToken);
        } else {
            // проверить 'refreshAccessToken' - не работал
            $spotifySession->refreshAccessToken($refreshToken);
            // 
            // $spotifySession->getAccessToken();
            // $spotifySession->getRefreshToken();

            $newAccessToken = $spotifySession->getAccessToken();
            $newRefreshToken = $spotifySession->getRefreshToken();
            // эта дрочь какого-то, сука, хуя возвращает 0. Заебала нахуй хуета ебаная
            $newTokenExpiration = $spotifySession->getTokenExpiration();

            $spotifyTokens->update([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                // 'expiration' => $spotifyTokens->expiration,
            ]);
        }
        $spotifyApi = new SpotifyWebAPI($options, $spotifySession);

        
        return $spotifyApi;
    }
}
