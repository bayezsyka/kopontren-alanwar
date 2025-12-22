<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function storeSale(Request $request, StockService $stockService)
    {
        $data = $request->validate([
            'sold_at' => ['nullable','date'],
            'payment_method' => ['nullable','string','max:50'],
            'lines' => ['required','array','min:1'],
            'lines.*.item_id' => ['required','integer','exists:items,id'],
            'lines.*.qty' => ['required','integer','min:1'],
            'lines.*.unit_price' => ['nullable','integer','min:0'],
        ]);

        $user = $request->user();
        $tz = config('app.timezone', 'Asia/Jakarta');
        $soldAt = isset($data['sold_at'])
            ? Carbon::parse($data['sold_at'], $tz)
            : now($tz);

        try {
            $sale = DB::transaction(function () use ($data, $user, $soldAt, $stockService) {
                // prefetch items untuk harga default + validasi
                $items = Item::with('bundleComponents.component')
                    ->whereIn('id', array_map(fn($l) => $l['item_id'], $data['lines']))
                    ->get()
                    ->keyBy('id');

                $sale = Sale::create([
                    'sold_at' => $soldAt,
                    'created_by' => $user->id,
                    'payment_method' => $data['payment_method'] ?? null,
                    'total' => 0,
                ]);

                $total = 0;

                foreach ($data['lines'] as $line) {
                    $item = $items[$line['item_id']] ?? null;
                    if (!$item) {
                        throw new \RuntimeException("Item tidak ditemukan");
                    }
                    if (!$item->is_active) {
                        throw new \RuntimeException("Item nonaktif: {$item->name}");
                    }

                    $qty = (int)$line['qty'];
                    $unitPrice = isset($line['unit_price']) && $line['unit_price'] !== null
                        ? (int)$line['unit_price']
                        : (int)$item->price_sell;

                    $subtotal = $qty * $unitPrice;
                    $total += $subtotal;

                    SaleLine::create([
                        'sale_id' => $sale->id,
                        'item_id' => (int)$item->id,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);
                }

                // stok out (bundle di-expand jadi komponen)
                $outMap = $stockService->buildOutMapFromSaleLines($data['lines']);

                // sumber = sale_id (lebih gampang)
                $stockService->applyStockOut($outMap, $user->id, $soldAt, 'sale', $sale->id);

                $sale->total = $total;
                $sale->save();

                return $sale;
            });

            $sale->load('lines.item');
            return response()->json(['sale' => $sale], 201);

        } catch (\RuntimeException $e) {
            $msg = $e->getMessage();

            // kalau error stok kurang, kita kembalikan detail yang enak dibaca
            $decoded = json_decode($msg, true);
            if (is_array($decoded) && ($decoded['type'] ?? '') === 'INSUFFICIENT_STOCK') {
                return response()->json([
                    'message' => 'Stok tidak cukup',
                    'details' => $decoded['items'],
                ], 422);
            }

            return response()->json(['message' => $msg], 422);
        }
    }
}
