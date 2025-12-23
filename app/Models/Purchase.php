<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['purchased_at','created_by','notes','total'];

    protected $casts = [
        'purchased_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(PurchaseLine::class);
    }
}