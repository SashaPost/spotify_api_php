<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SpotifyWebAPI::class, function ($app) {
            $client = new SpotifyWebAPI();
            $client->setAccessToken(Session::get('spotify_token'));
            // $client->setAccessToken("BQB2vEqFQokaTpgauGdpBr4s8Q_nvos8jUjLpXCo1BnDlxmzcnxSaZNbwYU72H5nAQhwAp7aLavWMBOfYGCse6uOyYaEf0bvw7wL5EiKOwE7-oFkel9Bon9ZIy4X0FEsbQ4G1U3cGkPRyAzCZqppRPTVXPEmflYs52QQDg7BMJHYkGJPA6mwCYgl4YAaK_cefWrqYpTyEZUecdpNxTy6kKGsRdyCItQ-zou-gryviy7Ojw3Q");
            // $user = Auth::user();
            // $accessToken = SpotifyTokenModel::where('user_id', $user->id)->first();
            // $client->setAccessToken($accessToken->access_token);
            // $spotifyToken = $user->access_token;
            // if ($spotifyToken) {
            //     $client->setAccessToken($spotifyToken);
            // }
            return $client;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        ini_set('max_execution_time', 180);
    }
}
