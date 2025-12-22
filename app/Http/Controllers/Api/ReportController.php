<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\WeeklyReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function weeklyList(Request $request)
    {
        $data = $request->validate([
            'year' => ['required','integer','min:2000'],
            'month' => ['required','integer','min:1','max:12'],
        ]);

        $reports = WeeklyReport::where('year', $data['year'])
            ->where('month', $data['month'])
            ->orderBy('week_of_month')
            ->get()
            ->map(function (WeeklyReport $r) {
                return [
                    'id' => (int)$r->id,
                    'year' => (int)$r->year,
                    'month' => (int)$r->month,
                    'week_of_month' => (int)$r->week_of_month,
                    'start_date' => $r->start_date->toDateString(),
                    'end_date' => $r->end_date->toDateString(),
                    'status' => (string)$r->status,
                    'generated_at' => $r->generated_at?->toDateTimeString(),
                    'has_pdf' => !empty($r->pdf_path),
                    'label' => $this->labelId($r->year, $r->month, $r->week_of_month),
                ];
            });

        return response()->json(['reports' => $reports]);
    }

    public function weeklyDetail(Request $request, WeeklyReport $report)
    {
        $tz = config('app.timezone', 'Asia/Jakarta');

        $start = Carbon::parse($report->start_date->toDateString(), $tz)->startOfDay();
        $end = Carbon::parse($report->end_date->toDateString(), $tz)->endOfDay();

        $now = now($tz);
        $effectiveEnd = $now->lt($end) ? $now : $end;
        $isPartial = $now->lt($end);

        $salesTotal = (int) Sale::whereBetween('sold_at', [$start, $effectiveEnd])->sum('total');
        $purchaseTotal = (int) Purchase::whereBetween('purchased_at', [$start, $effectiveEnd])->sum('total');
        $salesCount = (int) Sale::whereBetween('sold_at', [$start, $effectiveEnd])->count();

        $topItems = DB::table('sale_lines')
            ->join('sales', 'sale_lines.sale_id', '=', 'sales.id')
            ->join('items', 'sale_lines.item_id', '=', 'items.id')
            ->whereBetween('sales.sold_at', [$start, $effectiveEnd])
            ->selectRaw('items.id, items.name, items.type, SUM(sale_lines.qty) as qty, SUM(sale_lines.subtotal) as revenue')
            ->groupBy('items.id', 'items.name', 'items.type')
            ->orderByDesc('revenue')
            ->limit(15)
            ->get();

        return response()->json([
            'report' => [
                'id' => (int)$report->id,
                'label' => $this->labelId($report->year, $report->month, $report->week_of_month),
                'start_date' => $report->start_date->toDateString(),
                'end_date' => $report->end_date->toDateString(),
                'is_partial' => $isPartial,
                'effective_end' => $effectiveEnd->toDateTimeString(),
                'sales_total' => $salesTotal,
                'purchase_total' => $purchaseTotal,
                'gross_profit_simple' => $salesTotal - $purchaseTotal,
                'sales_count' => $salesCount,
                'top_items' => $topItems,
                'has_pdf' => !empty($report->pdf_path),
            ]
        ]);
    }

    public function generateWeeklyPdf(Request $request, WeeklyReport $report)
    {
        $tz = config('app.timezone', 'Asia/Jakarta');

        $start = Carbon::parse($report->start_date->toDateString(), $tz)->startOfDay();
        $end = Carbon::parse($report->end_date->toDateString(), $tz)->endOfDay();

        $now = now($tz);
        $effectiveEnd = $now->lt($end) ? $now : $end;
        $isPartial = $now->lt($end);

        $sales = Sale::with('lines.item')
            ->whereBetween('sold_at', [$start, $effectiveEnd])
            ->orderBy('sold_at')
            ->get();

        $purchases = Purchase::with('lines.item')
            ->whereBetween('purchased_at', [$start, $effectiveEnd])
            ->orderBy('purchased_at')
            ->get();

        $salesTotal = (int)$sales->sum('total');
        $purchaseTotal = (int)$purchases->sum('total');

        $payload = [
            'title' => $this->labelId($report->year, $report->month, $report->week_of_month),
            'start_date' => $report->start_date->toDateString(),
            'end_date' => $report->end_date->toDateString(),
            'is_partial' => $isPartial,
            'effective_end' => $effectiveEnd->toDateTimeString(),
            'sales_total' => $salesTotal,
            'purchase_total' => $purchaseTotal,
            'gross_profit_simple' => $salesTotal - $purchaseTotal,
            'sales' => $sales,
            'purchases' => $purchases,
        ];

        $pdf = Pdf::loadView('reports.weekly', $payload)->setPaper('a4', 'portrait');

        $filename = $this->safeFilename($report);
        $path = "reports/weekly/{$report->year}/" . str_pad($report->month, 2, '0', STR_PAD_LEFT) . "/{$filename}";

        Storage::put($path, $pdf->output());

        $report->pdf_path = $path;
        $report->generated_at = now($tz);
        $report->save();

        return response()->json([
            'message' => 'PDF generated',
            'pdf_path' => $path,
        ]);
    }

    public function downloadWeeklyPdf(Request $request, WeeklyReport $report)
    {
        if (!$report->pdf_path || !Storage::exists($report->pdf_path)) {
            return response()->json(['message' => 'PDF belum dibuat'], 404);
        }

        $filename = $this->safeFilename($report);
        return Storage::download($report->pdf_path, $filename);
    }

    private function safeFilename(WeeklyReport $r): string
    {
        $label = $this->labelId($r->year, $r->month, $r->week_of_month);
        $label = preg_replace('/[^A-Za-z0-9_\-\s]/', '', $label);
        $label = trim(preg_replace('/\s+/', ' ', $label));
        $label = str_replace(' ', '_', $label);

        return "{$label}.pdf";
    }

    private function labelId(int $year, int $month, int $week): string
    {
        $bulan = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
        ];

        $m = $bulan[$month] ?? (string)$month;
        return "{$m} {$year} - Minggu {$week}";
    }
}
