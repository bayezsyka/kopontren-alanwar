<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyReport extends Model
{
    protected $fillable = [
        'year','month','week_of_month','start_date','end_date',
        'status','generated_at','pdf_path'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'generated_at' => 'datetime',
    ];
}
