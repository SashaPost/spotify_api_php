<?php

namespace App\Jobs;

use App\Models\Playlist;

use App\Services\CreateIfNotService;
use App\Services\SpotifySessionService;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdatePlaylistTracksData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        SpotifySessionService $spotifySessionService,
        CreateIfNotService $createIfNotService,
        $playlistId
    )
    {
        //
        $playlist = Playlist::where('id', $playlistId)->first();

        $api = $spotifySessionService->instantiateSession();
        $playlist_tracks = $api->getPlaylistTracks($playlistId);

        $limit = 50;
        $offset = 0;
        $total = $playlist_tracks->total;

        $tracks = [];

        while ($playlist_tracks = $api->getPlaylistTracks($playlistId, [
            'limit' => $limit,
            'offset' => $offset
        ]))
        {
            $tracks = array_merge($tracks, $playlist_tracks->items);
            $offset += $limit;

            if ($offset > $total)
            {
                break;
            }
        }

        foreach ($tracks as $track)
        {
            $newSong = $createIfNotService->songFromSong($track);

            $newArtist = $createIfNotService->artistFromSong($track);    
            $newSong->artist()->associate($newArtist);

            $newAlbum = $createIfNotService->albumFromSong($track);
            $newSong->album()->associate($newAlbum);

            $playlist->songs()->syncWithoutDetaching($newSong);
            $playlist->artists()->syncWithoutDetaching($newArtist);

            $newSong->save();
        }
    }
}
