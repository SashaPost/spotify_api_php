<?php

namespace App\Jobs;

use App\Models\Song;
use App\Models\Album;
use App\Models\Artist;
use Illuminate\Bus\Queueable;
use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Session as SessionLaravel;


class UpdateSavedSongsData implements ShouldQueue
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

    // change the api call in the 'handle()' method
    public function handle()
    {
        $token = SessionLaravel::get('spotify_token');
        $spot_sess = new SpotifyWebAPI();
        $spot_sess->setAccessToken($token);
        $saved_tracks = $spot_sess->getMySavedTracks();

        $limit = 50;
        $offset = 0;
        $all_tracks = [];

        while ($saved_tracks = $spot_sess->getMySavedTracks([
            'limit' => $limit,
            'offset' => $offset
        ])) 
        {
            $all_tracks = array_merge($all_tracks, $saved_tracks->items);
            $offset += $limit;

            if ($offset > $saved_tracks->total) 
            {
                break;
            }
        }

        // shouls i make a service from the code below?
        foreach ($all_tracks as $track) {

            $newSong = Song::firstOrCreate(
                ['spotify_id' => $track->track?->id],
                [
                    'name' => $track->track?->name,
                    'duration_ms' => $track->track?->duration_ms,
                    'spotify_url' => $track->track?->uri,
                    'isrc' => $track->track?->external_ids->isrc,
                    'added_at' => $track->added_at
                ]
            );

            $newArtist = Artist::firstOrCreate(
                ['spotify_id' => $track->track?->artists[0]->id],
                [
                    'name' => $track->track?->artists[0]->name,
                    'spotify_url' => $track->track?->artists[0]->external_urls->spotify
                ]
            );
            $newSong->artist()->associate($newArtist);

            $newAlbum = Album::firstOrCreate(
                ['spotify_id' => $track->track?->album->id],
                [
                    'name' => $track->track?->album->name,
                    'release_date' => $track->track?->album->release_date,
                    'artist' => $track->track?->artists[0]->name,
                    'spotify_url' => $track->track?->album->external_urls->spotify,
                    'total_tracks' => $track->track?->album->total_tracks,
                    'artist_id' => $newArtist->id
                ]
            );
            $newSong->album()->associate($newAlbum);
            $newSong->save();
        }
    }
}
