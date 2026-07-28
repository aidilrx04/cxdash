<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingExperience extends Model
{
    /** @use HasFactory<\Database\Factories\TrainingExperienceFactory> */
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'program_name',
        'client',
        'date_start',
        'date_end',
        'participant_count',
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }
}
