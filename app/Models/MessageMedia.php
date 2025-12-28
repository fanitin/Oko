<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageMedia extends Model
{
    protected $fillable = [
        'message_id',
        'media_type',
        'path',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function message(){
        return $this->belongsTo(Message::class);
    }
}
