<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class UserAvatar extends Model
{
    use HasFactory, Notifiable, Searchable;

    protected $fillable = ['path', 'is_main'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
