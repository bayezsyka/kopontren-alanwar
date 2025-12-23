<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public const TYPE_NORMAL = 'normal';
    public const TYPE_BUNDLE = 'bundle';

    protected $fillable = [
        'name','type','sku','barcode','price_sell','is_active',
        'stock_cached','low_stock_threshold','created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bundleComponents()
    {
        return $this->hasMany(BundleComponent::class, 'bundle_item_id');
    }

    public function componentOfBundles()
    {
        return $this->hasMany(BundleComponent::class, 'component_item_id');
    }

    // ✅ TAMBAH INI
    public function saleLines()
    {
        return $this->hasMany(SaleLine::class, 'item_id');
    }

    // ✅ DAN INI
    public function purchaseLines()
    {
        return $this->hasMany(PurchaseLine::class, 'item_id');
    }

    public function isBundle(): bool
    {
        return $this->type === self::TYPE_BUNDLE;
    }
}
