<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpotifyToken extends Model
{
    protected $table = 'spotify_tokens';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'expiration',
        'code',
    ];

    use HasFactory;
}
