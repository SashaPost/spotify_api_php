<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistDuration extends Model
{
    use HasFactory;

    protected $table = 'playlist_duration';

    protected $fillable = [
        'playlist_id',
        'duration_ms',
    ];
}
