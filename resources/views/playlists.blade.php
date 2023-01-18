<!DOCTYPE html>

<h1>Playlists</h1>

@foreach ($playlists as $plist)
    <h3>{{ $plist['name'] }}</h3>
    <ul>
        @foreach ($plist['tracks'] as $track)
            <li>{{ $track->track?->name }}</li>
        @endforeach
    </ul>
@endforeach
