<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use SpotifyWebAPI\Session as SpotifySession;

class SpotifyTokenAutorefresh
{

    public function handle(Request $request, Closure $next)
    {
        // when we redirected from Spotify to redirect URI we receive *code*
        if ($request->get('code')) {
            $session = new SpotifySession(
                env('SPOTIFY_CLIENT_ID'),
                env('SPOTIFY_CLIENT_SECRET'),
                env('REDIRECT_URI')
            );

            // code is used to get access token
            $session->requestAccessToken($request->get('code'));

            $accessToken = $session->getAccessToken();
            $refreshToken = $session->getRefreshToken();
            $expiresAt = time() + $session->getTokenExpiration();

            Session::put('spotify_token', $accessToken); // this code stores the 'access token' in the session;
            Session::put('spotify_refresh_token', $refreshToken);
            Session::put('spotify_token_expires_at', $expiresAt);

            return redirect('/token-test');
        // } else {
        //     $accessToken = Session::get('spotify_token');
        //     $expiresAt = Session::get('spotify_token_expires_at');

        //     var_dump(Session::get('spotify_token'));
        //     var_dump(Session::get('spotify_refresh_token'));
        //     var_dump(Session::get('spotify_token_expires_at'));

        //     // check if token has expired
        //     if (time() >= $expiresAt) {
        //         $session = new SpotifySession(
        //             env('SPOTIFY_CLIENT_ID'),
        //             env('SPOTIFY_CLIENT_SECRET'),
        //             env('REDIRECT_URI')
        //         );
        //         $refreshToken = Session::get('spotify_refresh_token');
        //         $session->refreshAccessToken($refreshToken);

        //         $accessToken = $session->getAccessToken();
        //         $refreshToken = $session->getRefreshToken();
        //         $expiresAt = time() + $session->getTokenExpiration();
    
        //         Session::put('spotify_token', $accessToken); // this code stores the 'access token' in the session;
        //         Session::put('spotify_refresh_token', $refreshToken);
        //         Session::put('spotify_token_expires_at', $expiresAt);        

        //         return redirect('/token-test');
        //     }
        }
        return $next($request);
    }
}