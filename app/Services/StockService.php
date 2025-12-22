<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Kunci semua item normal yang terlibat (normal/bundle) supaya aman dari race.
     * Return: array [itemNormalId => qtyOut]
     */
    public function buildOutMapFromSaleLines(array $lines): array
    {
        // lines: [{item_id, qty, unit_price?}]
        $outMap = []; // normal_item_id => total_qty_out

        $items = Item::with('bundleComponents.component')
            ->whereIn('id', array_map(fn($l) => $l['item_id'], $lines))
            ->get()
            ->keyBy('id');

        foreach ($lines as $l) {
            $item = $items[$l['item_id']] ?? null;
            if (!$item) continue;

            $qty = (int)$l['qty'];

            if ($item->isBundle()) {
                foreach ($item->bundleComponents as $bc) {
                    $cid = (int)$bc->component_item_id;
                    $outMap[$cid] = ($outMap[$cid] ?? 0) + ($qty * (int)$bc->qty);
                }
            } else {
                $iid = (int)$item->id;
                $outMap[$iid] = ($outMap[$iid] ?? 0) + $qty;
            }
        }

        return $outMap;
    }

    /**
     * Lock items normal yang ada di outMap dengan FOR UPDATE.
     * Return items keyedBy id.
     */
    public function lockNormalItemsForUpdate(array $outMap)
    {
        $ids = array_keys($outMap);
        return Item::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');
    }

    /**
     * Validasi stok cukup (nggak boleh minus).
     */
    public function assertStockSufficient($lockedItems, array $outMap): void
    {
        $insufficient = [];

        foreach ($outMap as $itemId => $qtyOut) {
            $it = $lockedItems[$itemId] ?? null;
            if (!$it) continue;

            $stock = (int)$it->stock_cached;
            if ($stock - (int)$qtyOut < 0) {
                $insufficient[] = [
                    'item_id' => (int)$it->id,
                    'name' => $it->name,
                    'stock' => $stock,
                    'need' => (int)$qtyOut,
                ];
            }
        }

        if (!empty($insufficient)) {
            throw new \RuntimeException(json_encode([
                'type' => 'INSUFFICIENT_STOCK',
                'items' => $insufficient,
            ]));
        }
    }

    /**
     * Apply stock OUT untuk sale (normal items saja, karena bundle sudah di-expand jadi komponen).
     */
    public function applyStockOut(array $outMap, int $userId, \DateTimeInterface $happenedAt, string $sourceType, int $sourceId): void
    {
        $locked = $this->lockNormalItemsForUpdate($outMap);

        $this->assertStockSufficient($locked, $outMap);

        foreach ($outMap as $itemId => $qtyOut) {
            /** @var Item $it */
            $it = $locked[$itemId];
            $it->stock_cached = (int)$it->stock_cached - (int)$qtyOut;
            $it->save();

            StockMovement::create([
                'item_id' => $it->id,
                'direction' => 'out',
                'qty' => (int)$qtyOut,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'happened_at' => $happenedAt,
                'created_by' => $userId,
            ]);
        }
    }

    public function applyStockIn(int $itemId, int $qtyIn, int $userId, \DateTimeInterface $happenedAt, string $sourceType, int $sourceId): void
    {
        /** @var Item $it */
        $it = Item::whereKey($itemId)->lockForUpdate()->firstOrFail();
        if ($it->isBundle()) {
            throw new \RuntimeException("Tidak boleh stok in untuk item bundle: {$it->name}");
        }

        $it->stock_cached = (int)$it->stock_cached + (int)$qtyIn;
        $it->save();

        StockMovement::create([
            'item_id' => $it->id,
            'direction' => 'in',
            'qty' => (int)$qtyIn,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'happened_at' => $happenedAt,
            'created_by' => $userId,
        ]);
    }

    public function adjustStock(int $itemId, int $newStock, int $userId, \DateTimeInterface $happenedAt, ?string $notes = null): void
    {
        /** @var Item $it */
        $it = Item::whereKey($itemId)->lockForUpdate()->firstOrFail();
        if ($it->isBundle()) {
            throw new \RuntimeException("Tidak boleh adjust stok untuk item bundle: {$it->name}");
        }

        $current = (int)$it->stock_cached;
        $delta = $newStock - $current;

        $it->stock_cached = $newStock;
        $it->save();

        StockMovement::create([
            'item_id' => $it->id,
            'direction' => 'adjust',
            'qty' => $delta,
            'source_type' => 'adjustment',
            'source_id' => null,
            'happened_at' => $happenedAt,
            'created_by' => $userId,
            'notes' => $notes,
        ]);
    }
}
