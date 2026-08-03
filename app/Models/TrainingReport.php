<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingReport extends Model
{
    /** @use HasFactory<\Database\Factories\TrainingReportFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path'
    ];
}
