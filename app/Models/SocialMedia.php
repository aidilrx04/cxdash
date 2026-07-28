<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMedia extends Model
{
    /** @use HasFactory<\Database\Factories\SocialMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'platform',
        'url'
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }
}
