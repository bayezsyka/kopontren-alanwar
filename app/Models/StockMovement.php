<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'item_id','direction','qty','source_type','source_id',
        'happened_at','created_by','notes'
    ];

    protected $casts = [
        'happened_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
