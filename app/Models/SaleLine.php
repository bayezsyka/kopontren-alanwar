<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleLine extends Model
{
    protected $fillable = ['sale_id','item_id','qty','unit_price','subtotal'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
