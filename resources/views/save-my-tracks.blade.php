<!DOCTYPE html>

<h1>My Saved Tracks</h1>

<div class="container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Track</th>
                <th>Duration</th>
                <th>Artist</th>
                <th>Album</th>
                {{-- <th>Spotify URL</th> --}}
                <th>ISRC</th>
                <th>Added at</th>
                <th>Spotify ID</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tracks as $track)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $track['name'] }}</td>
                    <td>{{ $track['duration'] }}</td>
                    <td>{{ $track->artist->name }}</td>
                    <td>{{ $track->album->name }}</td>
                    {{-- <td>{{ $track['spotify_url'] }}</td> --}}
                    <td>{{ $track['isrc'] }}</td>
                    <td>{{ $track['added_at'] }}</td>
                    <td>{{ $track['spotify_id'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
