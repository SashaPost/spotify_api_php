<!DOCTYPE HTML>

@extends('parent')

@section('title', 'User Playlists')

{{-- if (!$user) breaks the page here --}}
@section('content')

    <a href="{{ route('dashboard') }}">Dashboard</a>

    <a href="{{ route('playlists') }}">All Playlists</a>

    <h3>Playlists</h3>
    <h5>{{ $count }} playlists</h5>        
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Total</th>
                    <th>Duration</th>
                    {{-- provided wrong values from the database --}}
                    {{-- <th>Created</th>
                    <th>Updated</th> --}}
                    <th>Spoify URL</th>
                    <th>Public</th>
                    <th>Collaborative</th>
                    {{-- <th>Owner</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($playlists as $playlist)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>
                            @if($playlist->name != "")
                                <a href="{{ route('playlist.songs', $playlist->id) }}">{{ $playlist->name }}</a>
                            @else
                                <a href="{{ route('playlist.songs', $playlist->id) }}">{{ '*blank_name*' }}</a>
                            @endif 
                        </td>
                        <td>{{ $playlist->total_tracks }} songs</td>
                        <td>{{ gmdate("H:i:s", $playlist->duration->duration_ms / 1000) }}</td>
                        {{-- <td>{{ $playlist->created_at }}</td>
                        <td>{{ $playlist->updated_at }}</td> --}}
                        <td><a href="{{ $playlist->spotify_url }}">{{ $playlist->spotify_url }}</a></td>
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
                        </td>

                        {{-- something interesting; from 'my-tracks' --}}
                        {{-- <td>{{ $property['duration'][0] }}:{{ $property['duration'][1] }}</td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
