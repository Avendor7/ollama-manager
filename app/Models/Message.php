<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_session_id',
        'content',
        'role',
        'token_count',
        'metadata',
    ];

    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            ChatSession::class,
            'id',           // Foreign key on ChatSession table...
            'id',           // Foreign key on Users table...
            'chat_session_id',  // Local key on Message table...
            'user_id'       // Local key on ChatSession table...
        );
    }
}
