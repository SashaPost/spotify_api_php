<?php

use Psr\Container\ContainerInterface;


interface TracksInterface
{
    /**
     * @return Track[]
     */
    function getTracks(): array;
}

class SpotifyService implements TracksInterface
{
    function getTracks(): array
    {
        $token = SessionLaravel::get('spotify_token');
        $spot_sess = new SpotifyWebAPI();
        $spot_sess->setAccessToken($token);

        $saved_tracks = $spot_sess->getMySavedTracks();

        $formattedTracks = [];


        foreach($saved_tracks as $saved_track) {
            $formattedTracks[] = new Track(
                new Time($saved_track->duration),
                $saved_track->author,
                $saved_track->name,
            );
        }

        return $formattedTracks;
    }
}

class TrackServiceManager
{
    function __construct(public ContainerInterface $container)
    {
        
    }

    /**
     * Summary of getTracks
     * @return Track[]
     */
    function getServicesTracks(): array
    {
        $services = $this->container->get(TracksInterface::class);

        $tracks = [];

        foreach($services as $service) {
            $tracks = array_merge($tracks, $service->getTracks());
        }

        return $tracks;
    }
}

class Track
{
    function __construct(
        public Time $time,
        public string $author,
        public string $name
    ) {
    }


}

new Track(
    new Time('5:43'),
    'Kendrick Lamar',
    'Ni**er'
);

class Time implements Stringable
{
    public $value;
    function __construct(string $time)
    {
        if (!true) { // if value wrong fromat (validation)
            // throw exception
        }

        $this->value = $time;
    }

    function __toString()
    {
        return $this->value;
    }
}