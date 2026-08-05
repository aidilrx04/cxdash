<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackGeneral extends Model
{
    /** @use HasFactory<\Database\Factories\FeedbackGeneralFactory> */
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'feedback_question_id',
        'respond',
        'sentiment',
        'theme'
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function feedbackQuestion(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestion::class);
    }
}
