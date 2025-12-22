<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BundleComponent;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('search');
        $type = $request->query('type'); // normal|bundle|null
        $quick = $request->query('quick'); // 1|null

        $items = Item::query()
            ->when($q, fn($qq) => $qq->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%")
                  ->orWhere('barcode', 'like', "%{$q}%");
            }))
            ->when($type, fn($qq) => $qq->where('type', $type))
            ->when($quick, fn($qq) => $qq->where('is_quick', true))
            ->orderBy('is_quick', 'desc')
            ->orderBy('quick_order')
            ->orderBy('name')
            ->limit(100)
            ->get();

        $items->load('bundleComponents.component');

        $result = $items->map(fn($item) => $this->itemPayload($item));

        return response()->json(['items' => $result]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'type' => ['required','in:normal,bundle'],
            'sku' => ['nullable','string','max:100'],
            'barcode' => ['nullable','string','max:100'],
            'price_sell' => ['required','integer','min:0'],
            'is_active' => ['nullable','boolean'],
            'low_stock_threshold' => ['nullable','integer','min:0'],
            'is_quick' => ['nullable','boolean'],
            'quick_order' => ['nullable','integer','min:0'],
        ]);

        $item = Item::create([
            ...$data,
            'created_by' => $request->user()->id,
            'is_active' => $data['is_active'] ?? true,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 0,
            'is_quick' => $data['is_quick'] ?? false,
            'quick_order' => $data['quick_order'] ?? 0,
            'stock_cached' => 0,
        ]);

        return response()->json(['item' => $this->itemPayload($item->fresh('bundleComponents.component'))], 201);
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'sku' => ['sometimes','nullable','string','max:100'],
            'barcode' => ['sometimes','nullable','string','max:100'],
            'price_sell' => ['sometimes','integer','min:0'],
            'is_active' => ['sometimes','boolean'],
            'low_stock_threshold' => ['sometimes','integer','min:0'],
            'is_quick' => ['sometimes','boolean'],
            'quick_order' => ['sometimes','integer','min:0'],
        ]);

        $item->update($data);

        return response()->json(['item' => $this->itemPayload($item->fresh('bundleComponents.component'))]);
    }

    public function setBundleComponents(Request $request, Item $item)
    {
        if (!$item->isBundle()) {
            return response()->json(['message' => 'Item ini bukan bundle'], 422);
        }

        $data = $request->validate([
            'components' => ['required','array','min:1'],
            'components.*.component_item_id' => ['required','integer','exists:items,id'],
            'components.*.qty' => ['required','integer','min:1'],
        ]);

        DB::transaction(function () use ($item, $data) {
            BundleComponent::where('bundle_item_id', $item->id)->delete();

            foreach ($data['components'] as $c) {
                BundleComponent::create([
                    'bundle_item_id' => $item->id,
                    'component_item_id' => $c['component_item_id'],
                    'qty' => $c['qty'],
                ]);
            }
        });

        return response()->json(['item' => $this->itemPayload($item->fresh('bundleComponents.component'))]);
    }

    private function itemPayload(Item $item): array
    {
        // hitung stok bundle = min(component_stock / qty)
        $stock = (int)$item->stock_cached;

        $components = [];
        if ($item->isBundle()) {
            $stock = 0;
            if ($item->bundleComponents->count() > 0) {
                $stock = $item->bundleComponents->map(function ($bc) {
                    $componentStock = (int)($bc->component->stock_cached ?? 0);
                    return intdiv($componentStock, (int)$bc->qty);
                })->min() ?? 0;
            }

            $components = $item->bundleComponents->map(fn($bc) => [
                'component_item_id' => (int)$bc->component_item_id,
                'component_name' => (string)($bc->component->name ?? ''),
                'qty' => (int)$bc->qty,
                'component_stock' => (int)($bc->component->stock_cached ?? 0),
            ])->values()->all();
        }

        return [
            'id' => (int)$item->id,
            'name' => (string)$item->name,
            'type' => (string)$item->type,
            'sku' => $item->sku,
            'barcode' => $item->barcode,
            'price_sell' => (int)$item->price_sell,
            'is_active' => (bool)$item->is_active,
            'stock' => (int)$stock,
            'stock_cached' => (int)$item->stock_cached,
            'low_stock_threshold' => (int)$item->low_stock_threshold,
            'is_quick' => (bool)$item->is_quick,
            'quick_order' => (int)$item->quick_order,
            'bundle_components' => $components,
        ];
    }
}
