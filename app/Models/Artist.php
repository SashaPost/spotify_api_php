<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    public function songs()
    {
        return $this->hasMany('App\Song', 'artist_id', 'id');
    }

    public function albums()
    {
        return $this->hasMany('App\Album', 'artist_id', 'id');
    }

    public function playlists()
    {
        return $this->belongsToMany('App\Playlist', 'artist_playlists', 'artist_id', 'playlist_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'artist_users', 'artist_id', 'user_id');
    }
}
