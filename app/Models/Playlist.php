<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    public function songs()
    {
        return $this->belongsToMany('App\Song', 'playlist_songs', 'playlist_id', 'song_id');
    }

    public function owners()
    {
        return $this->belongsToMany('App\User', 'playlist_owners', 'user_id', 'playlist_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'playlist_users', 'playlist_id', 'user_id');
    }

    public function artists()
    {
        return $this->belongsToMany('App\User', 'playlist_artists', 'playlist_id', 'artist_id');
    }
}
