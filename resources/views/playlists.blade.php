<!DOCTYPE html>

<h1>Playlists</h1>

<h3>User: {{ $my_name }}</h3>

@foreach ($playlists as $plist)
    <h3>{{ $plist['name'] }}</h3>
    {{-- <ol>
        @foreach ($plist['tracks'] as $track)
            <li>{{ $track->track?->name }}</li>
        @endforeach
    </ol> --}}
    <ol>
        @foreach ($plist['tracks'] as $track)
            <li>
                <div>Track: {{ $track->track?->name }};</div>
                <div>Artist: {{ $track->track?->album->artists[0]->name }};</div>
                <div>Album: {{ $track->track?->album->name }};</div>
                <div>Release Date: {{ $track->track?->album->release_date }};</div>
                <div>Duration (ms): {{ $track->track?->duration_ms }};</div>
                {{-- ISRC causes an error --}}
                {{-- <div>ISRC: {{ $track->track?->external_ids->isrc }};</div>  --}}
                {{-- <div>Spotify ID: {{ $track->track?->id }};</div> --}}
                {{-- <div>URI: {{ $track->track?->uri }};</div> --}}
            </li>
        @endforeach
    </ol>
@endforeach
