<!DOCTYPE html>

<h1>My Saved Tracks</h1>

{{-- {{ dd($tracks_properties) }} --}}

<div class="container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Track</th>
                <th>Duration</th>
                <th>Artist</th>
                <th>Album</th>
                <th>Release Date</th>
                <th>ISRC</th>
                <th>Spotify ID</th>
                {{-- <th>URI</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($tracks_properties as $property)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $property['track_name'] }}</td>
                    <td>{{ $property['duration'][0] }}:{{ $property['duration'][1] }}</td>
                    <td>{{ $property['artist'] }}</td>
                    <td>{{ $property['album'] }}</td>
                    <td>{{ $property['release_date'] }}</td>
                    <td>{{ $property['isrc'] }}</td>
                    <td>{{ $property['spotify_id'] }}</td>
                    {{-- <td>{{ $property['uri'] }}</td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
