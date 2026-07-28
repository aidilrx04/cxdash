<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSkill extends Model
{
    /** @use HasFactory<\Database\Factories\AiSkillFactory> */
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'ai_usage',
        'ai_tools'
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }
}
