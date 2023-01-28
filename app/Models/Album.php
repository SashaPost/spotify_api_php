<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    public function songs()
    {
        return $this->belongsToMany('App\Songs', 'album_songs', 'album_id', 'song_id');
    }

    public function artist()
    {
        return $this->belongsTo('App\Artist', 'artist_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\Users', 'album_users', 'album_id', 'user_id');
    }
}
