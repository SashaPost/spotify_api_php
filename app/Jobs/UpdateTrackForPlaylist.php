<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

use App\Services\CreateIfNotService;
use App\Services\SpotifySessionService;

class UpdateTrackForPlaylist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $playlist;
    public $track; 

    public function __construct(
        $playlist,
        $track,
    )
    {
        //
        $this->playlist;
        $this->track;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        // SpotifySessionService $spotifySessionService,
        CreateIfNotService $createIfNotService,
    )
    {
        //
        $newSong = $createIfNotService->songFromSong($this->track);
        
        $newArtist = $createIfNotService->artistFromSong($this->track);  
        $newSong->artist()->associate($newArtist);

        $newAlbum = $createIfNotService->albumFromSong($this->track);
        $newSong->album()->associate($newAlbum);

        $this->playlist->songs()->syncWithoutDetaching($newSong);
        $this->playlist->artists()->syncWithoutDetaching($newArtist);
    }
}
