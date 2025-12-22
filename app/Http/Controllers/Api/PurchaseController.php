<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function store(Request $request, StockService $stockService)
    {
        $data = $request->validate([
            'purchased_at' => ['nullable','date'],
            'notes' => ['nullable','string'],
            'lines' => ['required','array','min:1'],
            'lines.*.item_id' => ['required','integer','exists:items,id'],
            'lines.*.qty' => ['required','integer','min:1'],
            'lines.*.unit_cost' => ['nullable','integer','min:0'],
        ]);

        $user = $request->user();
        $tz = config('app.timezone', 'Asia/Jakarta');
        $purchasedAt = isset($data['purchased_at'])
            ? Carbon::parse($data['purchased_at'], $tz)
            : now($tz);

        try {
            $purchase = DB::transaction(function () use ($data, $user, $purchasedAt, $stockService) {
                $purchase = Purchase::create([
                    'purchased_at' => $purchasedAt,
                    'created_by' => $user->id,
                    'notes' => $data['notes'] ?? null,
                    'total' => 0,
                ]);

                $total = 0;

                foreach ($data['lines'] as $line) {
                    $qty = (int)$line['qty'];
                    $unitCost = isset($line['unit_cost']) ? (int)$line['unit_cost'] : 0;
                    $subtotal = $qty * $unitCost;
                    $total += $subtotal;

                    $pl = PurchaseLine::create([
                        'purchase_id' => $purchase->id,
                        'item_id' => (int)$line['item_id'],
                        'qty' => $qty,
                        'unit_cost' => $unitCost,
                        'subtotal' => $subtotal,
                    ]);

                    $stockService->applyStockIn((int)$line['item_id'], $qty, $user->id, $purchasedAt, 'purchase_line', $pl->id);
                }

                $purchase->total = $total;
                $purchase->save();

                return $purchase;
            });

            $purchase->load('lines.item');
            return response()->json(['purchase' => $purchase], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
