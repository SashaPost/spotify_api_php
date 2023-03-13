<?php

namespace App\Jobs;

// use Illuminate\Http\Client\Request;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

use App\Models\Playlist;
use App\Services\SpotifySessionService;

class UpdateSavedPlaylistsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct
        (

        )
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        SpotifySessionService $spotifySessionService
    )
    {
        // 

        $api = $spotifySessionService->instantiateSession();
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

        foreach ($all_playlists as $playlist)
        {
            $new_playlist = Playlist::firstOrCreate(
                ['spotify_id' => $playlist->id],
                [
                    'name' => $playlist->name,
                    'description' => $playlist->description,
                    'spotify_url' => $playlist->external_urls->spotify,
                    'collaborative' => $playlist->collaborative,
                    'public' => $playlist->public,
                    'total_tracks' => $playlist->tracks->total,
                    'owner_id' => $playlist->owner->id
                ]
            );
            $new_playlist->save();
        }
    }
}
