<!DOCTYPE html>

{{-- {{ dd($tokens) }} --}}
<h3>Tokens:</h3>
<ul>
    <li>id: {{ $tokens['id'] }}</li>    
    <li>access_token: {{ $tokens['access_token'] }}</li>
    <li>refresh_token: {{ $tokens['refresh_token'] }}</li>
    <li>expiration: {{ $tokens['expiration'] }}</li>
    <li>created_at: {{ $tokens['created_at'] }}</li>
    <li>updated_at: {{ $tokens['updated_at'] }}</li>
    <li>code: {{ $tokens['code'] }}</li> 
</ul>
    {{-- <p>{{ dd($session) }}</p> --}}
<a href="{{ route('index') }}">Index Page</a>