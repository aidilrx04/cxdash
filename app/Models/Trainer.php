<?php

namespace App\Models;

use Database\Factories\TrainerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainer extends Model
{
    /** @use HasFactory<TrainerFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'home_address',
        'email',
        'phone_number',
        'years_experience',
        'notable_clients',
        'avg_evaluation_score',
        'fee_structure',
        'professional_summary',
        'profile_picture',
        'cv_path'
    ];

    public function socialMedia(): HasMany
    {
        return $this->hasMany(SocialMedia::class);
    }

    public function education(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class);
    }

    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(Industry::class);
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class);
    }

    public function trainingMethods(): BelongsToMany
    {
        return $this->belongsToMany(TrainingMethod::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }

    public function aiSkills(): HasMany
    {
        return $this->hasMany(AiSkill::class);
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(WorkExperience::class);
    }
}
