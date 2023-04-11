<?php

namespace App\Jobs;

// this shit doesn't work; don't know why
// ini_set('max_execution_time', 720);

use App\Models\User;
use App\Models\Playlist;
use Illuminate\Bus\Queueable;

use App\Models\PlaylistDuration;
use App\Services\CreateIfNotService;
use Illuminate\Queue\SerializesModels;

use App\Services\SpotifySessionService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdatePlaylistDuration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $playlistId;
    public function __construct(
        $playlistId,
        private User $user,
    )
    {
        //
        $this->playlistId = $playlistId;
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
        $databasePlaylist = Playlist::where('id', $this->playlistId)->first();

        $api = $spotifySessionService->instantiateSession($this->user);
        $playlistTracks = $api->getPlaylistTracks($databasePlaylist->spotify_id);
        $updTotalTracks = $playlistTracks->total;
        
        $limit = 50;
        $offset = 0;
        $tracks = [];
        while ($playlist_tracks = $api->getPlaylistTracks($databasePlaylist->spotify_id, [
            'limit' => $limit,
            'offset' => $offset
        ]))
        {
            $tracks = array_merge($tracks, $playlist_tracks->items);
            $offset += $limit;

            if ($offset > $updTotalTracks)
            {
                break;
            }
        }

        $totalDurationMs = 0;
        foreach ($tracks as $track)
        {
            $totalDurationMs += $track->track?->duration_ms;
        }

        $playlistDuration = $createIfNotService->playlistDuration($this->playlistId, $totalDurationMs);
        $databasePlaylistDuration = $databasePlaylist->duration['duration_ms'];
        if ($databasePlaylistDuration != $totalDurationMs)
        {
            $pD = PlaylistDuration::where('playlist_id', $this->playlistId)->first();
            $pD->update(['duration_ms' => $totalDurationMs]);
        }
    }
}
