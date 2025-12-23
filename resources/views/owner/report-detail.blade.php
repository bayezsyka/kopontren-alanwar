@extends('layouts.app')

@section('title', 'Detail Laporan')
@section('page-title', 'Detail Laporan')

@php
    $showBack = true;
@endphp

@section('content')
<div class="p-4 space-y-4" x-data="reportDetailAppComponent('{{ $reportId }}')">
    <!-- Header Info -->
    <div class="card" x-show="report">
        <template x-if="report">
            <div>
                <h2 class="font-bold text-lg text-gray-900 mb-2" x-text="report.label"></h2>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Periode:</strong> <span x-text="report.start_date"></span> s/d <span x-text="report.end_date"></span></p>
                    <p><strong>Status:</strong> 
                        <span 
                            class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                            :class="report.status === 'final' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                            x-text="report.status === 'final' ? '✅ Final' : '📝 Draft'"
                        ></span>
                    </p>
                    <p x-show="report.generated_at"><strong>Dibuat:</strong> <span x-text="report.generated_at"></span></p>
                </div>
            </div>
        </template>
    </div>

    <!-- Summary -->
    <div class="card" x-show="report && report.summary">
        <h3 class="text-sm font-bold text-gray-900 mb-3">Ringkasan</h3>
        <template x-if="report && report.summary">
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-green-50 rounded-xl p-3 border border-green-100">
                    <p class="text-xs text-green-700 mb-1">Total Penjualan</p>
                    <p class="text-lg font-bold text-green-800" x-text="formatCurrency(report.summary.total_sales || 0)"></p>
                </div>
                <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                    <p class="text-xs text-blue-700 mb-1">Total Belanja</p>
                    <p class="text-lg font-bold text-blue-800" x-text="formatCurrency(report.summary.total_purchases || 0)"></p>
                </div>
                <div class="bg-purple-50 rounded-xl p-3 border border-purple-100">
                    <p class="text-xs text-purple-700 mb-1">Laba Kotor</p>
                    <p class="text-lg font-bold text-purple-800" x-text="formatCurrency(report.summary.gross_profit || 0)"></p>
                </div>
                <div class="bg-orange-50 rounded-xl p-3 border border-orange-100">
                    <p class="text-xs text-orange-700 mb-1">Margin</p>
                    <p class="text-lg font-bold text-orange-800" x-text="(report.summary.margin || 0).toFixed(1) + '%'"></p>
                </div>
            </div>
        </template>
    </div>

    <!-- Top Items -->
    <div class="card" x-show="report && report.top_items && report.top_items.length > 0">
        <h3 class="text-sm font-bold text-gray-900 mb-3">Top Barang Terlaris</h3>
        <div class="space-y-2">
            <template x-for="(item, index) in report.top_items" :key="item.item_id">
                <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-2">
                    <div class="w-6 h-6 bg-[var(--color-primary)] text-white rounded-full flex items-center justify-center text-xs font-bold" x-text="index + 1"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm" x-text="item.item_name"></p>
                        <p class="text-xs text-gray-500"><span x-text="item.total_qty"></span> terjual • <span x-text="formatCurrency(item.total_amount)"></span></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Actions -->
    <div class="card space-y-2">
        <button 
            @click="generatePDF"
            :disabled="generating"
            class="btn btn-primary w-full"
        >
            <svg x-show="!generating" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <svg x-show="generating" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-show="!generating">Generate PDF</span>
            <span x-show="generating">Generating...</span>
        </button>

        <button 
            x-show="report && report.has_pdf"
            @click="downloadPDF"
            class="btn btn-secondary w-full"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download PDF
        </button>
    </div>

    <div x-show="loading" class="card text-center py-8">
        <svg class="animate-spin w-8 h-8 mx-auto text-[var(--color-primary)]" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('reportDetailApp', (reportId) => ({
        reportId: reportId,
        report: null,
        loading: false,
        generating: false,

        init() {
            this.loadReport();
        },

        async loadReport() {
            this.loading = true;
            try {
                const response = await api.get(`/reports/weekly/${this.reportId}`);
                this.report = response.data;
            } catch (error) {
                showToast('Gagal memuat laporan: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        async generatePDF() {
            this.generating = true;
            try {
                await api.post(`/reports/weekly/${this.reportId}/generate`);
                showToast('✅ PDF berhasil di-generate', 'success');
                await this.loadReport(); // Reload to get updated status
            } catch (error) {
                showToast('❌ Gagal generate PDF: ' + error.message, 'error');
            } finally {
                this.generating = false;
            }
        },

        async downloadPDF() {
            try {
                const apiBaseUrl = getApiBaseUrl();
                const token = getAuthToken();
                const url = `${apiBaseUrl}/reports/weekly/${this.reportId}/download`;

                // Download directly
                const a = document.createElement('a');
                a.href = url;
                a.download = `laporan-${this.report.label}.pdf`;
                a.target = '_blank';
                
                // Add auth header via fetch
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (!response.ok) throw new Error('Download failed');

                const blob = await response.blob();
                const blobUrl = window.URL.createObjectURL(blob);
                a.href = blobUrl;
                a.click();
                window.URL.revokeObjectURL(blobUrl);

                showToast('✅ PDF berhasil diunduh', 'success');
            } catch (error) {
                showToast('❌ Gagal download PDF: ' + error.message, 'error');
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        }
    }));
});
</script>
@endpush
