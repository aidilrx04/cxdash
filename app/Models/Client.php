<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function trainingReports(): HasMany
    {
        return $this->hasMany(TrainingReport::class);
    }

    /**
     * Get maximum participants across all training reports.
     * Uses eager-loaded subquery attribute when available, falling back to query.
     */
    public function maxPax(): int
    {
        return (int) ($this->training_reports_max_total_participants
            ?? $this->trainingReports()->max('total_participants')
            ?? 0);
    }
}
