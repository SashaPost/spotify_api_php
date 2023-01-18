<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SpotifyToken
{
        
    public function handle(Request $request, Closure $next)
    {
        if ($request->get('code')) {
            Session::put('spotify_token', $request->get('code'));
            return redirect('/token-test');
        }
        
        return $next($request);
    }
}
