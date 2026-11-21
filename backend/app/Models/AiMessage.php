<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ai_conversation_id',
        'sender',
        'message',
        'suggested_actions',
        'prompt_tokens',
        'completion_tokens',
        'created_at',
    ];

    protected $casts = [
        'suggested_actions' => 'array',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'created_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }
}
