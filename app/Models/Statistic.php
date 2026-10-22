<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $fillable = [
        'data',
        'is_running'
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
