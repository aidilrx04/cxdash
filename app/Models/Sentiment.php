<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sentiment extends Model
{
    /** @use HasFactory<\Database\Factories\SentimentFactory> */
    use HasFactory;

    protected $fillable = [
        'training_report_id',
        'trainee_id',
        'feedback_question_id',
        'sentiment',
        'theme',
    ];

    public function trainingReport(): BelongsTo
    {
        return $this->belongsTo(TrainingReport::class);
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function feedbackQuestion(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestion::class);
    }
}
