<!DOCTYPE HTML>

@extends('parent')

@section('title', 'Dashboard')
    
@section('content')
    <div>
        {{ $user }}
    </div>
    <br>

    <form action="/spotify-authorize" method="POST">
        @csrf
        <button>Authorize spotify</button>
    </form>

    <ul>
        <a href="{{ route('playlists') }}">Playlists</a>
    </ul>
@endsection

{{-- @if (! $user)
    <p>Not Authorized</p>
    <form action="/login" method="GET">
        @csrf
        <button>Login</button>
    </form>
    <form action="/register" method="GET">
        @csrf
        <button>Register</button>
    </form>
@else
    <div>
        {{ $user }}
    </div>
    <br>

    <form action="/spotify-authorize" method="POST">
        @csrf
        <button>Authorize spotify</button>
    </form>

    <ul>
        <a href="{{ route('playlists') }}">Playlists</a>
    </ul>

    <form action="/logout" method="POST">
        @csrf
        <button>Logout</button>
    </form>
@endif --}}
