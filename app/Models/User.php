<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'spotify_name',
        'email',
        'password',
        'username'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function songs()
    {
        return $this->belongsToMany('App\Song', 'user_songs', 'user_id', 'song_id');
    }

    public function playlists()
    {
        return $this->belongsToMany('App\Playlist', 'user_playlist', 'user_id', 'playlist_id');
    }

    public function albums()
    {
        return $this->belongsToMany('App\Album', 'user_albums', 'user_id', 'album_id');
    }

    public function artists()
    {
        return $this->belongsToMany('App\Artist', 'user_artists', 'user_id', 'artist_id');
    }

    public function spotify_tokens()
    {
        return $this->hasOne(SpotifyToken::class, 'user_id');
    }

    public function isSpotifyAuthorized()
    {
        return false; //(bool)$this->spotify_tokens;
    }
}
