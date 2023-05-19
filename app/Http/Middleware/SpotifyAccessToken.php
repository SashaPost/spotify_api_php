<?php

namespace App\Http\Middleware;

use Closure;
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

    public function handle(Request $request, Closure $next)
    {
        

        return $next($request);
    }
}
