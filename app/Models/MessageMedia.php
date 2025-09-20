<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageMedia extends Model
{
    protected $table = [
        'message_id',
        'media_type',
        'path',
        'meta',
    ];

    public function message(){
        return $this->belongsTo(Message::class);
    }
}
