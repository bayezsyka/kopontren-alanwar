<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function low(Request $request)
    {
        $items = Item::where('type', 'normal')
            ->where('is_active', true)
            ->whereColumn('stock_cached', '<=', 'low_stock_threshold')
            ->orderBy('stock_cached')
            ->limit(200)
            ->get();

        return response()->json(['items' => $items]);
    }

    // Owner-only biasanya, tapi kalau mau kasir juga boleh adjust, hapus middleware owner di route.
    public function adjust(Request $request, StockService $stockService)
    {
        $data = $request->validate([
            'item_id' => ['required','integer','exists:items,id'],
            'new_stock' => ['required','integer','min:0'],
            'notes' => ['nullable','string'],
            'happened_at' => ['nullable','date'],
        ]);

        $tz = config('app.timezone', 'Asia/Jakarta');
        $at = isset($data['happened_at'])
            ? Carbon::parse($data['happened_at'], $tz)
            : now($tz);

        try {
            $stockService->adjustStock(
                (int)$data['item_id'],
                (int)$data['new_stock'],
                $request->user()->id,
                $at,
                $data['notes'] ?? null
            );

            return response()->json(['message' => 'Stock adjusted']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
