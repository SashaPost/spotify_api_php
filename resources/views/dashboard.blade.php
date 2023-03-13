<!DOCTYPE HTML>

{{-- @if ($user->isSpotifyAuthorized())
    
@endif --}}

<form action="/spotify-authorize" method="POST">
    <button>Authorize spotify</button>
    @csrf
</form>

