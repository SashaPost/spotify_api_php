<!DOCTYPE HTML>

<a href="{{ route('dashboard') }}">Dashboard</a>
<h3>{{ $name }}</h3>
@if($description != "")
    <p>Description: {{ $description }}</p>
@else
    <p>Description: none</p>
@endif
<h5>Duration: {{ gmdate("H:i:s", $duration / 1000) }}</h5>
<h5>{{ $total_tracks }} tracks</h5>

<div class="container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Song</th>
                <th>Artist</th>
                <th>Album</th>
                <th>Duration</th>
                <th>Spoify URL</th>
                <th>ISRC</th>
            </tr>

            {{-- <tr>
                <th>#</th>
                <th>Title</th>
                <th>Total</th>
                <th>Duration</th> --}}
                {{-- provided wrong values from the database --}}
                {{-- <th>Created</th>
                <th>Updated</th> --}}
                {{-- <th>Spoify URL</th>
                <th>Public</th>
                <th>Collaborative</th> --}}
                {{-- <th>Owner</th> --}}
            {{-- </tr> --}}
        </thead>
        <tbody>
            @foreach ($playlistSongs as $song)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $song->name }}</td>
                    <td>{{ $song->artist->name }}</td>
                    <td>{{ $song->album->name }}</td>
                    <td>{{ gmdate("i:s", $song->duration_ms / 1000) }}</td>
                    <td><a href="https://open.spotify.com/track/{{ $song->spotify_id }}">https://open.spotify.com/track/{{ $song->spotify_id }}</a></td>
                    <td>{{ $song->isrc }}</td>
                </tr>
                
                {{-- <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>
                        @if($playlist->name != "")
                            <a href="{{ route('playlist.songs', $playlist->id) }}">{{ $playlist->name }}</a>
                        @else
                            <a href="{{ route('playlist.songs', $playlist->id) }}">{{ '*blank_name*' }}</a>
                        @endif 
                    </td>
                    <td>{{ $playlist->total_tracks }} songs</td>
                    <td>{{ gmdate("H:i:s", $playlist->duration->duration_ms / 1000) }}</td> --}}
                    {{-- <td>{{ $playlist->created_at }}</td>
                    <td>{{ $playlist->updated_at }}</td> --}}
                    {{-- <td><a href="{{ $playlist->spotify_url }}">{{ $playlist->spotify_url }}</a></td>
                    <td>
                        @if($playlist->public == 1)
                            <p>true</p>
                        @else
                            <p>false</p>
                        @endif
                    </td>
                    <td>
                        @if($playlist->collaborative == 1)
                            <p>true</p>
                        @else
                            <p>false</p>
                        @endif
                    </td> --}}

                    {{-- something interesting; from 'my-tracks' --}}
                    {{-- <td>{{ $property['duration'][0] }}:{{ $property['duration'][1] }}</td> --}}
                {{-- </tr> --}}
            @endforeach
        </tbody>
    </table>
</div>
