<?php

namespace App\Services;

use App\Models\User;

use SpotifyWebAPI\Session;
use App\Models\SpotifyToken;

use Illuminate\Http\Request;
use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Auth\Authenticatable;

class SpotifySessionService {

    public function __construct(
        private bool $autoRefresh = true,
        private bool $autoRetry = true,
        // private Authenticatable $authUser = null,

        // private bool $autoRefresh,
        // private bool $autoRetry,
        // public $authUser
    ) {
        // $this->autoRefresh = true,
        
        // $this->authUser = $authUser;
    }

    // creates a Spotify session:
    public function instantiateSession(User $user = null) {
        // $spotifyTokens = SpotifyToken::latest()->first();
        // $user = $user ?? auth()->user();   
        
        $user = $user ?? User::where('id', auth()->user()->id)->first();
        
        // $user = User::where('id', $this->authUser->id)->first();
        $spotifyTokens = $user->spotify_tokens;
        $accessToken = $spotifyTokens->access_token;
        $refreshToken = $spotifyTokens->refresh_token;

        $options = [
            'auto_refresh' => $this->autoRefresh, 
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

    // gets all user's playlists from the Spotify API:
    public function getAllPlaylists(User $user = null)
    {
        $api = $this->instantiateSession($user);
        $playlists = $api->getMyPlaylists();
        $total = $playlists->total;
        
        $limit = 50;
        $offset = 0;
        $all_playlists = [];

        while ($playlists = $api->getMyPlaylists([
            'limit' => $limit,
            'offset' => $offset
        ])) 
        {
            $all_playlists = array_merge($all_playlists, $playlists->items);
            $offset += $limit;

            if ($offset > $playlists->total)
            {
                break;
            }
        }

        return $all_playlists;
    }
}
