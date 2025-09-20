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

    public function users(){
        return $this->belongsToMany(User::class, 'chat_users', 'chat_id', 'user_id');
    }
}
