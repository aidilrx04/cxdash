<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    /** @use HasFactory<\Database\Factories\WorkExperienceFactory> */
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'title',
        'company_name',
        'start_date',
        'end_date',
        'is_current',
        'responsibilities',
        'achievements',
    ];
}
