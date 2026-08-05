<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainee extends Model
{
    /** @use HasFactory<\Database\Factories\TraineeFactory> */
    use HasFactory;

    protected $fillable = [
        'training_reports_id',
        'name'
    ];

    public function trainingReport(): BelongsTo
    {
        return $this->belongsTo(TrainingReport::class);
    }

    public function feedbackGenerals(): HasMany
    {
        return $this->hasMany(FeedbackGeneral::class);
    }
}
