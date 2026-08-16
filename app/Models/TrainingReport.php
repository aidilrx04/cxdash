<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingReport extends Model
{
    /** @use HasFactory<\Database\Factories\TrainingReportFactory> */
    use HasFactory;

    protected $fillable = [
        'file_name',
        'program_title',
        'client_name',
        'client_id',
        'trainer_name',
        'total_participants',
        'total_evaluation',
        'overall_satisfaction',
        'status',
        'pss_score',
        'executive_summary',
        'file_path',
    ];

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainee::class);
    }

    public function feedbackQuestions(): HasMany
    {
        return $this->hasMany(FeedbackQuestion::class);
    }

    public function sentiments(): HasMany
    {
        return $this->hasMany(Sentiment::class);
    }

    // Trainee model act as proxies for filament relation manager to show feedbacks
    public function feedbackGenerals()
    {
        return $this->hasMany(Trainee::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
