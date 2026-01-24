<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'is_group',
        'title',
        'is_self',
    ];

    public function scopeNotFromUser($query, $userId)
    {
        return $query->where('user_id', '!=', $userId);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_users', 'chat_id', 'user_id')
            ->withPivot('role', 'last_read_message_id')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function chatUsers()
    {
        return $this->hasMany(ChatUser::class);
    }

    public function chatAvatars()
    {
        return $this->hasMany(ChatAvatar::class);
    }

    public function mainAvatar()
    {
        return $this->hasOne(ChatAvatar::class)->where('is_main', true);
    }
}
