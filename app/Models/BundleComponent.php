<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleComponent extends Model
{
    protected $fillable = ['bundle_item_id','component_item_id','qty'];

    public function bundle()
    {
        return $this->belongsTo(Item::class, 'bundle_item_id');
    }

    public function component()
    {
        return $this->belongsTo(Item::class, 'component_item_id');
    }
}
