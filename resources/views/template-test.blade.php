<!DOCTYPE html>

{{-- {{ dd($me) }} --}}

<h3>Tokens:</h3>


{{ $user = App\Models\User::where('id', auth()->user()->id)->first() }}

<ul>
    <li>id: {{ $user_token['id'] }}</li>    
    <li>access_token: {{ $user_token['access_token'] }}</li>
    <li>refresh_token: {{ $user_token['refresh_token'] }}</li>
    <li>expiration: {{ $user_token['expiration'] }}</li>
    <li>created_at: {{ $user_token['created_at'] }}</li>
    <li>updated_at: {{ $user_token['updated_at'] }}</li>
    <li>code: {{ $user_token['code'] }}</li> 
    <li>user_id: {{ $user_token['user_id'] }}</li>
</ul>

{{-- <ul>
    <li>id: {{ $tokens['id'] }}</li>    
    <li>access_token: {{ $tokens['access_token'] }}</li>
    <li>refresh_token: {{ $tokens['refresh_token'] }}</li>
    <li>expiration: {{ $tokens['expiration'] }}</li>
    <li>created_at: {{ $tokens['created_at'] }}</li>
    <li>updated_at: {{ $tokens['updated_at'] }}</li>
    <li>code: {{ $tokens['code'] }}</li> 
    <li>user_id: {{ $tokens['user_id'] }}</li>
</ul> --}}

{{-- <div>
    {{ $user_token }}
</div> --}}
    {{-- <p>{{ dd($session) }}</p> --}}
<a href="{{ route('index') }}">Index Page</a>