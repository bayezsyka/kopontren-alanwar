<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $data = $request->validate([
            'from' => ['required','date'],
            'to' => ['required','date'],
        ]);

        $tz = config('app.timezone', 'Asia/Jakarta');
        $from = Carbon::parse($data['from'], $tz)->startOfDay();
        $to = Carbon::parse($data['to'], $tz)->endOfDay();

        $salesTotal = (int) Sale::whereBetween('sold_at', [$from, $to])->sum('total');
        $salesCount = (int) Sale::whereBetween('sold_at', [$from, $to])->count();

        $purchaseTotal = (int) Purchase::whereBetween('purchased_at', [$from, $to])->sum('total');
        $purchaseCount = (int) Purchase::whereBetween('purchased_at', [$from, $to])->count();

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'sales_total' => $salesTotal,
            'sales_count' => $salesCount,
            'purchase_total' => $purchaseTotal,
            'purchase_count' => $purchaseCount,
            'gross_profit_simple' => $salesTotal - $purchaseTotal,
        ]);
    }

    public function series(Request $request)
    {
        $data = $request->validate([
            'from' => ['required','date'],
            'to' => ['required','date'],
        ]);

        $tz = config('app.timezone', 'Asia/Jakarta');
        $from = Carbon::parse($data['from'], $tz)->startOfDay();
        $to = Carbon::parse($data['to'], $tz)->endOfDay();

        $sales = DB::table('sales')
            ->selectRaw('DATE(sold_at) as d, SUM(total) as total')
            ->whereBetween('sold_at', [$from, $to])
            ->groupByRaw('DATE(sold_at)')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $purchases = DB::table('purchases')
            ->selectRaw('DATE(purchased_at) as d, SUM(total) as total')
            ->whereBetween('purchased_at', [$from, $to])
            ->groupByRaw('DATE(purchased_at)')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $days = [];
        $cur = $from->copy();
        while ($cur->lte($to)) {
            $d = $cur->toDateString();
            $days[] = [
                'date' => $d,
                'sales_total' => (int)($sales[$d]->total ?? 0),
                'purchase_total' => (int)($purchases[$d]->total ?? 0),
            ];
            $cur->addDay();
        }

        return response()->json(['series' => $days]);
    }

    public function topItems(Request $request)
    {
        $data = $request->validate([
            'from' => ['required','date'],
            'to' => ['required','date'],
            'limit' => ['nullable','integer','min:1','max:50'],
        ]);

        $tz = config('app.timezone', 'Asia/Jakarta');
        $from = Carbon::parse($data['from'], $tz)->startOfDay();
        $to = Carbon::parse($data['to'], $tz)->endOfDay();
        $limit = (int)($data['limit'] ?? 10);

        $rows = DB::table('sale_lines')
            ->join('sales', 'sale_lines.sale_id', '=', 'sales.id')
            ->join('items', 'sale_lines.item_id', '=', 'items.id')
            ->whereBetween('sales.sold_at', [$from, $to])
            ->selectRaw('items.id, items.name, items.type, SUM(sale_lines.qty) as qty, SUM(sale_lines.subtotal) as revenue')
            ->groupBy('items.id', 'items.name', 'items.type')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return response()->json(['items' => $rows]);
    }
}
