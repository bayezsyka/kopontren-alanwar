<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseLine extends Model
{
    protected $fillable = ['purchase_id','item_id','qty','unit_cost','subtotal'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
