<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'spotify_id',
        'spotify_url',
        'collaborative',
        'public',
        'total_tracks',
        'owner_id'
    ];

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'playlist_songs', 'playlist_id', 'song_id');
    }

    public function owners()
    {
        return $this->belongsToMany(User::class, 'playlist_owners', 'user_id', 'playlist_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'playlist_users', 'playlist_id', 'user_id');
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'playlist_artists', 'playlist_id', 'artist_id');
    }

    public function duration()
    {
        return $this->hasOne(PlaylistDuration::class, 'playlist_id');
    }
}
