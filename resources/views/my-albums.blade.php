<!DOCTYPE html>

<h1>My Saved Albums</h1>

{{-- {{ dd($albums) }} --}}

{{-- {{ $test }} --}}

{{-- {{ $albums }} --}}

<ol>
@foreach ($albums as $album)
    <li>
        <div>Album Name: {{ $album->album?->name }};</div> 
        <div>Artist: {{ $album->album?->artists[0]->name }};</div> 
        <div>Release Date: {{ $album->album?->release_date }};</div>
    </li>
@endforeach
</ol>