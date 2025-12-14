<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'user_id',
        'chat_id',
        'body',
        'reply_for_message_id',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function chat(){
        return $this->belongsTo(Chat::class);
    }

    public function replyTo(){
        return $this->belongsTo(Message::class, 'reply_for_message_id');
    }

    public function media(){
        return $this->hasMany(MessageMedia::class);
    }
}
