<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\FeedbackQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'question',
        'type'
    ];

    public function feedbackGenerals(): HasMany
    {
        return $this->hasMany(FeedbackGeneral::class);
    }
}
