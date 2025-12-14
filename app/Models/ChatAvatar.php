<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAvatar extends Model
{
    protected $fillable = [
        'chat_id',
        'path',
        'is_main',
    ];
}
