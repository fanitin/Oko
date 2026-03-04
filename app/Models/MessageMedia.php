<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = ['url'];

    protected static function booted()
    {
        static::deleting(function ($media) {
            if ($media->path) {
                Storage::disk('public')->delete($media->path);
            }
        });
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    public function message(){
        return $this->belongsTo(Message::class);
    }
}
