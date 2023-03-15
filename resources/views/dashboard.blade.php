<!DOCTYPE HTML>

{{-- @if ($user->isSpotifyAuthorized())
    
@endif --}}

<form action="/spotify-authorize" method="POST">
    @csrf
    <button>Authorize spotify</button>
</form>

<ul>
    <a href="{{ route('playlists') }}">Playlists</a>
</ul>
