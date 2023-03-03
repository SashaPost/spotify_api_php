<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use SpotifyWebAPI\Session as SpotifySession;

class SpotifyToken
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
            $token = $session->getAccessToken();
            Session::put('spotify_token', $token); // this code stores the 'access token' in the session;
            
            return redirect('/token-test');
        }
        return $next($request);
    }
}
