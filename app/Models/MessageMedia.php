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

    protected static function booted()
    {
        static::deleting(function ($media) {
            if ($media->path) {
                Storage::delete($media->path);
            }
        });
    }

    public function message(){
        return $this->belongsTo(Message::class);
    }
}
