<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'release_date',
        'artist',
        'spotify_id',
        'spotify_url',
        'total_tracks',
        'artist_id'
    ];

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'album_songs', 'album_id', 'song_id');
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'album_users', 'album_id', 'user_id');
    }
}
