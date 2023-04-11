<?php

namespace App\Jobs;

// this shit doesn't work; don't know why
// ini_set('max_execution_time', 720);

use App\Models\Song;

use App\Models\User;
use App\Models\Album;

use App\Models\Artist;
use App\Models\Playlist;

use Illuminate\Bus\Queueable;
use App\Jobs\UpdateTrackForPlaylist;
use App\Services\CreateIfNotService;
use Illuminate\Queue\SerializesModels;
use App\Services\SpotifySessionService;
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
        // none of these works:
        // set_time_limit(7200);
        // ini_set('max_execution_time', 0);
        // echo phpinfo();

        //
        $playlist = Playlist::where('id', $this->playlistId)->first();

        $api = $spotifySessionService->instantiateSession($this->user);
        $playlist_tracks = $api->getPlaylistTracks($playlist->spotify_id);

        $limit = 50;
        $offset = 0;
        $total = $playlist_tracks->total;

        $tracks = [];

        while ($playlist_tracks = $api->getPlaylistTracks($playlist->spotify_id, [
            'limit' => $limit,
            'offset' => $offset
        ]))
        {
            foreach ($playlist_tracks->items as $track)
            {
                // $tracks = array_merge($tracks, $playlist_tracks->items);
                // just added:
                UpdateTrackForPlaylist::dispatch($playlist, $track);

                $offset += $limit;

                if ($offset > $total)
                {
                    break;
                }
            }

            // foreach ($tracks as $track)
            // {
            //     $newSong = $createIfNotService->songFromSong($track);
            //     // $newSong = Song::firstOrCreate(
            //     //     ['spotify_id' => $track->track?->id],
            //     //     [
            //     //         'name' => $track->track?->name,
            //     //         'duration_ms' => $track->track?->duration_ms,
            //     //         'spotify_url' => $track->track?->uri,
            //     //         'isrc' => $track->track?->external_ids->isrc,
            //     //         'added_at' => $track->added_at
            //     //     ]
            //     // );

            //     $newArtist = $createIfNotService->artistFromSong($track);  
            //     // $newArtist = Artist::firstOrCreate(
            //     //     ['spotify_id' => $track->track?->artists[0]->id],
            //     //     [
            //     //         'name' => $track->track?->artists[0]->name,
            //     //         'spotify_url' => $track->track?->artists[0]->external_urls->spotify
            //     //     ]
            //     // );
            //     $newSong->artist()->associate($newArtist);

            //     $newAlbum = $createIfNotService->albumFromSong($track);
            //     // $artist = Artist::where('spotify_id', $track->track?->artists[0]->id)->first();
            //     // $artistId = $artist->id;
            //     // $newAlbum = Album::firstOrCreate(
            //     //     ['spotify_id' => $track->track?->album->id],
            //     //     [
            //     //         'name' => $track->track?->album->name,
            //     //         'release_date' => $track->track?->album->release_date,
            //     //         'artist' => $track->track?->artists[0]->name,
            //     //         'spotify_url' => $track->track?->album->external_urls->spotify,
            //     //         'total_tracks' => $track->track?->album->total_tracks,
            //     //         'artist_id' => $artistId
            //     //     ]
            //     // );
            //     $newSong->album()->associate($newAlbum);

            //     $playlist->songs()->syncWithoutDetaching($newSong);
            //     $playlist->artists()->syncWithoutDetaching($newArtist);
            //     // $playlist->owners()->;

            //     // $newSong->save();
            // }
        }
    }
}