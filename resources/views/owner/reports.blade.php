@extends('layouts.app')

@section('title', 'Laporan Mingguan')
@section('page-title', 'Laporan Mingguan')

@section('content')
<div class="p-4 space-y-4" x-data="reportsAppComponent">
    <!-- Month/Year Selector -->
    <div class="card">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tahun</label>
                <input type="number" x-model.number="year" @change="loadReports" min="2020" class="input-field">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Bulan</label>
                <select x-model.number="month" @change="loadReports" class="input-field">
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <div class="space-y-3">
        <template x-for="report in reports" :key="report.id">
            <div class="card hover:shadow-md transition-shadow">
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <h3 class="font-bold text-sm text-gray-900 mb-1" x-text="report.label"></h3>
                        <p class="text-xs text-gray-500 mb-2">
                            <span x-text="formatDate(report.start_date)"></span> s/d <span x-text="formatDate(report.end_date)"></span>
                        </p>
                        <div class="flex items-center gap-2">
                            <span 
                                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="report.status === 'final' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                x-text="report.status === 'final' ? '✅ Final' : '📝 Draft'"
                            ></span>
                            <span 
                                x-show="report.has_pdf"
                                class="inline-block px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold"
                            >📄 PDF Tersedia</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <a :href="`/owner/reports/${report.id}`" class="btn btn-primary text-xs px-3 py-2">
                            Detail →
                        </a>
                        <button 
                            x-show="report.has_pdf"
                            @click="downloadPdf(report.id)"
                            class="btn btn-secondary text-xs px-3 py-2"
                        >
                            📥 Unduh
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="reports.length === 0 && !loading" class="card text-center py-8 text-gray-400">
            <p class="text-sm font-medium">Belum ada laporan mingguan</p>
            <p class="text-xs mt-1">Laporan akan otomatis dibuat setiap akhir minggu</p>
        </div>
    </div>

    <div x-show="loading" class="card text-center py-8">
        <svg class="animate-spin w-8 h-8 mx-auto text-[var(--color-primary)]" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>
@endsection
