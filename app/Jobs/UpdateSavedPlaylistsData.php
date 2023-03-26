<?php

namespace App\Jobs;

// use Illuminate\Http\Client\Request;

use App\Models\Playlist;
use App\Services\CreateIfNotService;
use App\Services\SpotifySessionService;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Throwable;

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
        SpotifySessionService $spotifySessionService,
        CreateIfNotService $createIfNotService,
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
            // trigger here 'UpdatePlaylistDuration'
            $new_playlist = $createIfNotService->playlist($playlist);

            $fetchedPlaylist = Playlist::where('spotify_id', $playlist->id)->first();
            
            if($fetchedPlaylist->total_tracks != $playlist->tracks->total)
            {
                UpdatePlaylistTracksData::dispatch($fetchedPlaylist->id);
                UpdatePlaylistDuration::dispatch($fetchedPlaylist->id);
            }

            if($fetchedPlaylist->duration === null)
            {
                UpdatePlaylistDuration::dispatch($fetchedPlaylist->id);
            }


            // $test = $fetchedPlaylist->duration->duration_ms;
            // $secondTest = $fetchedPlaylist->duration;

            // try {
            //     $fetchedPlaylist->duration;
            // } catch (Throwable $e) {
            //     UpdatePlaylistDuration::dispatch($fetchedPlaylist->id);
            // }
        }
    }
}
