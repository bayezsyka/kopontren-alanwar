@extends('layouts.main')

@section('title', 'Reports -Kopontren Kasir')

@section('content')
<div class="container mx-auto px-4 py-4 max-w-4xl">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">Laporan Mingguan</h2>

    <!-- Period Selector -->
    <div class="card mb-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Tahun</label>
                <select id="yearSelect" class="input input-sm"></select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Bulan</label>
                <select id="monthSelect" class="input input-sm">
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
    <div id="reportsList" class="space-y-3">
        <div class="text-center py-8">
            <div class="spinner mx-auto mb-3"></div>
            <p class="text-gray-500">Loading...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let reports = [];

// Init year selector
function initYearSelector() {
    const yearSelect = document.getElementById('yearSelect');
    const currentYear = new Date().getFullYear();
    
    for (let year = currentYear; year >= currentYear - 3; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    }
}

// Set current month
function setCurrentMonth() {
    const monthSelect = document.getElementById('monthSelect');
    monthSelect.value = new Date().getMonth() + 1;
}

async function loadReports() {
    const year = document.getElementById('yearSelect').value;
    const month = document.getElementById('monthSelect').value;

    try {
        const response = await window.api.get(`/reports/weekly?year=${year}&month=${month}`);
        reports = response.data || response;
        renderReports();
    } catch (error) {
        console.error('Load reports error:', error);
        showToast('Gagal memuat laporan', 'error');
    }
}

function renderReports() {
    const container = document.getElementById('reportsList');

    if (reports.length === 0) {
        container.innerHTML = '<div class="card text-center text-gray-400 py-8">Tidak ada laporan</div>';
        return;
    }

    container.innerHTML = reports.map(report => `
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="font-bold text-lg text-gray-900">${report.label}</h3>
                    <p class="text-sm text-gray-500">
                        ${formatDate(report.start_date)} - ${formatDate(report.end_date)}
                    </p>
                </div>
                <span class="badge ${report.status === 'final' ? 'badge-success' : 'badge-warning'}">
                    ${report.status === 'final' ? 'Final' : 'Draft'}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                <div>
                    <p class="text-gray-600">Penjualan</p>
                    <p class="font-bold text-green-600">${formatRupiah(report.sales_total || 0)}</p>
                </div>
                <div>
                    <p class="text-gray-600">Pembelian</p>
                    <p class="font-bold text-red-600">${formatRupiah(report.purchase_total || 0)}</p>
                </div>
            </div>

            <div class="flex gap-2">
                ${report.has_pdf ? `
                    <button onclick="downloadPDF(${report.id})" class="btn btn-success btn-sm flex-1">
                        <i class="fas fa-download mr-2"></i>Download PDF
                    </button>
                ` : `
                    <button onclick="generatePDF(${report.id})" class="btn btn-primary btn-sm flex-1">
                        <i class="fas fa-file-pdf mr-2"></i>Generate PDF
                    </button>
                `}
            </div>
        </div>
    `).join('');
}

async function generatePDF(reportId) {
    showLoading();
    
    try {
        await window.api.post(`/reports/weekly/${reportId}/generate`);
        hideLoading();
        showToast('PDF berhasil digenerate', 'success');
        loadReports();
    } catch (error) {
        hideLoading();
        console.error('Generate PDF error:', error);
        showToast(error.message || 'Gagal generate PDF', 'error');
    }
}

async function downloadPDF(reportId) {
    try {
        // Open in new tab
        window.open(`/api/reports/weekly/${reportId}/download`, '_blank');
        showToast('Download dimulai...', 'info');
    } catch (error) {
        console.error('Download error:', error);
        showToast('Gagal download PDF', 'error');
    }
}

document.getElementById('yearSelect').addEventListener('change', loadReports);
document.getElementById('monthSelect').addEventListener('change', loadReports);

// Init
initYearSelector();
setCurrentMonth();
loadReports();
</script>
@endpush
