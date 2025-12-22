<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['sold_at','created_by','payment_method','total'];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(SaleLine::class);
    }
}
