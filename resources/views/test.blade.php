<!DOCTYPE html>

<h1>Contents:</h1>
<div>
    <a href="{{ url('auth') }}">Link to the 'auth'</a>
</div>
<div>
    <a href="{{ url('token-test') }}">Link to the 'token test'</a>
</div>

<div>
    <ul>
        <li><a href="{{ url('playlists') }}">My Playlists</a></li>
        <li><a href="{{ url('my-albums') }}">My Albums</a></li>
        <li><a href="{{ url('my-tracks') }}">Liked Songs</a></li>
    </ul>
</div>