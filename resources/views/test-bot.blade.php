<!DOCTYPE html>

{{ $songs }}

@foreach ($songs as $song)
    <ul>
        <li>{{ $song['spotify_id'] }}</li>
    </ul>
@endforeach