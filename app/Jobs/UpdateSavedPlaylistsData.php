<?php

namespace App\Jobs;

// use Illuminate\Http\Client\Request;

// this shit doesn't work; don't know why
// ini_set('max_execution_time', 720);

use Exception;
use Throwable;

use App\Models\User;
use App\Models\Playlist;

use Illuminate\Bus\Queueable;

use Illuminate\Support\Facades\Log;
use App\Services\CreateIfNotService;
use Illuminate\Queue\SerializesModels;

use App\Services\SpotifySessionService;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

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
            private User $user,
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
        try {
            // $api = $spotifySessionService->instantiateSession();
            // $playlists = $api->getMyPlaylists();
            // $total = $playlists->total;
            
            // $limit = 50;
            // $offset = 0;
            // $all_playlists = [];

            // while ($playlists = $api->getMyPlaylists([
            //     'limit' => $limit,
            //     'offset' => $offset
            // ])) 
            // {
            //     $all_playlists = array_merge($all_playlists, $playlists->items);
            //     $offset += $limit;

            //     if ($offset > $playlists->total)
            //     {
            //         break;
            //     }
            // }

            // the code above replaced by this:
            $all_playlists = $spotifySessionService->getAllPlaylists($this->user);

            // need to fix the code below:
            foreach ($all_playlists as $playlist)
            {
                // trigger here 'UpdatePlaylistDuration'
                $new_playlist = $createIfNotService->playlist($playlist);

                $fetchedPlaylist = Playlist::where('spotify_id', $playlist->id)->first();
                if($fetchedPlaylist->total_tracks != $playlist->tracks->total)
                {
                    UpdatePlaylistTracksData::dispatch($fetchedPlaylist->id, $this->user);
                    UpdatePlaylistDuration::dispatch($fetchedPlaylist->id, $this->user);
                }

                // if($fetchedPlaylist->duration === null)
                // {
                //     UpdatePlaylistDuration::dispatch($fetchedPlaylist->id);
                // }

                // $test = $fetchedPlaylist->duration->duration_ms;
                // $secondTest = $fetchedPlaylist->duration;

                // try {
                //     $fetchedPlaylist->duration;
                // } catch (Throwable $e) {
                //     UpdatePlaylistDuration::dispatch($fetchedPlaylist->id);
                // }
            }
        } catch (Exception $e) {
            Log::error('Error executing UpdateSavedPlaylistsData job: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}
