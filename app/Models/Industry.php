<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Industry extends Model
{
    /** @use HasFactory<\Database\Factories\IndustryFactory> */
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(Trainer::class);
    }
}
