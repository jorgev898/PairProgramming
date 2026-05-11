<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PairSession extends Model
{
    protected $fillable = [
        'code',
        'lesson_id',
        'driver',
        'navigator',
        'status',
        'thread_id',
        'chat_history',
        'participant_chat',
        'code_content',
        'cursors',
    ];
    protected $casts = [
        'chat_history' => 'array',
        'participant_chat' => 'array',
        'cursors' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}