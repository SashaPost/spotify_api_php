<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\SpotifySessionService;

class SpotifyAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    protected $spotifySessionService;

    public function __construct(SpotifySessionService $spotifySessionService)
    {
        $this->spotifySessionService = $spotifySessionService;
    }

    public function handle(
        Request $request, 
        Closure $next, 
        User $user = null,
    )
    {
        if (!auth()->user()) {
            return redirect(route('login'));
        }

        $user = $user ?? User::where('id', auth()->user()->id)->first();
        $userTokens = $user->spotify_tokens;
        // $accessToken = $userTokens->access_token;

        // if user has no access token:
        if ($userTokens === null) {
            // no need to do this, just redirect to 'oauth':
            return redirect(route('oauth'));
        }

        // if token is expired - refresh it:
        
        
        // if tokens are ok - proceed:
        return $next($request);
    }
}
