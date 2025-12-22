<?php

namespace App\Console\Commands;

use App\Models\WeeklyReport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EnsureWeeklyReports extends Command
{
    protected $signature = 'reports:ensure-weekly {year?} {month?}';
    protected $description = 'Buat slot laporan mingguan (Senin-Minggu) otomatis per bulan';

    public function handle(): int
    {
        $tz = config('app.timezone', 'Asia/Jakarta');

        $year = (int) ($this->argument('year') ?? now($tz)->year);
        $month = (int) ($this->argument('month') ?? now($tz)->month);

        $firstDay = Carbon::create($year, $month, 1, 0, 0, 0, $tz);
        $lastDay  = $firstDay->copy()->endOfMonth();

        $weekStart = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
        $weekNo = 1;

        while ($weekStart->lte($lastDay)) {
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            WeeklyReport::updateOrCreate(
                ['year' => $year, 'month' => $month, 'week_of_month' => $weekNo],
                [
                    'start_date' => $weekStart->toDateString(),
                    'end_date' => $weekEnd->toDateString(),
                    'status' => 'draft',
                ]
            );

            $weekStart->addWeek();
            $weekNo++;
        }

        $this->info("Weekly reports ensured for {$year}-{$month}");
        return self::SUCCESS;
    }
}
