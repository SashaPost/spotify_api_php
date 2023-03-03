<?php

namespace App\Models;

// use App\Models\Artist;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_ms',
        'artist_id',
        'album_id',
        'spotify_url',
        'isrc',
        'added_at',
        'spotify_id'
    ];
    // protected $fillable = [
    //     // table fields
    //     // fill that in other tables as well
    //     // '$fillable' allows to use the create method 
    //     // or the update method to assign values to the 
    //     // listed attributes in a single line, 
    //     // rather than setting each attribute individually
    //     'name', 
    // ];
    public function artist() 
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    public function album()
    {
        return $this->belongsTo(Album::class, 'album_id');
    }

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'song_playlists', 'song_id', 'playlist_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'song_users', 'song_id', 'user_id');
    }
}
