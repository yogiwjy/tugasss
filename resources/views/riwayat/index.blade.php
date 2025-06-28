@extends('layouts.main')

@section('title', 'Riwayat Kunjungan')

@section('content')
<main class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-history"></i> Riwayat Kunjungan</h1>
        <p>Lihat riwayat kunjungan dan status antrian Anda</p>
    </div>

    {{-- Alert Messages --}}
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div class="alert-content">
                {{ session('error') }}
            </div>
            <button type="button" class="alert-close">&times;</button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div class="alert-content">
                {{ session('success') }}
            </div>
            <button type="button" class="alert-close">&times;</button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="filter-card">
        <div class="filter-header">
            <div class="filter-title">
                <i class="fas fa-filter"></i>
                Filter Riwayat
            </div>
            <div class="filter-stats">
                Total: {{ $riwayatAntrian->total() ?? 0 }} Kunjungan
            </div>
        </div>
        
        <form method="GET" action="{{ route('riwayat.index') }}" class="filter-form">
            <div class="filter-options">
                <button type="submit" name="poli" value="" class="filter-chip {{ !request('poli') ? 'active' : '' }}">
                    <i class="fas fa-list"></i>
                    Semua Poli
                </button>
                @if(isset($availableServices))
                    @foreach($availableServices as $service)
                        <button type="submit" name="poli" value="{{ $service }}" class="filter-chip {{ request('poli') == $service ? 'active' : '' }}">
                            <i class="fas fa-stethoscope"></i>
                            {{ $service }}
                        </button>
                    @endforeach
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="content-card">
        <div class="card-header">
            <div class="header-left">
                <i class="fas fa-table"></i>
                <h5>Data Riwayat Kunjungan</h5>
            </div>
            <div class="header-right">
                @if($riwayatAntrian && $riwayatAntrian->count() > 0)
                    <span class="record-count">{{ $riwayatAntrian->firstItem() }}-{{ $riwayatAntrian->lastItem() }} dari {{ $riwayatAntrian->total() }}</span>
                @endif
            </div>
        </div>

        <div class="table-container">
            @if($riwayatAntrian && $riwayatAntrian->count() > 0)
                <!-- Desktop Table -->
                <div class="desktop-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>No Antrian</th>
                                <th>Informasi Pasien</th>
                                <th>Layanan</th>
                                <th>Tanggal & Waktu</th>
                                <th>Status</th>
                                <th>Dokter</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riwayatAntrian as $key => $antrian)
                            <tr class="table-row">
                                <td class="text-center">
                                    <span class="row-number">{{ $riwayatAntrian->firstItem() + $key }}</span>
                                </td>
                                <td>
                                    <span class="queue-number">{{ $antrian->number }}</span>
                                </td>
                                <td>
                                    <div class="patient-info">
                                        <div class="patient-name">{{ $antrian->name ?? Auth::user()->name }}</div>
                                        <div class="patient-details">
                                            <span><i class="fas fa-phone"></i> {{ $antrian->phone ?? Auth::user()->phone ?? '-' }}</span>
                                            <span><i class="fas fa-venus-mars"></i> {{ $antrian->gender ?? Auth::user()->gender ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="service-info">
                                        <span class="service-name">{{ $antrian->poli ?? $antrian->service->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="datetime-info">
                                        <div class="date">{{ $antrian->formatted_tanggal }}</div>
                                        <div class="time">{{ $antrian->created_at->format('H:i') }} WIB</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $antrian->status_badge }}">
                                        @switch($antrian->status)
                                            @case('waiting')
                                                <i class="fas fa-clock"></i> Menunggu
                                                @break
                                            @case('serving')
                                                <i class="fas fa-play"></i> Dilayani
                                                @break
                                            @case('finished')
                                                <i class="fas fa-check"></i> Selesai
                                                @break
                                            @case('canceled')
                                                <i class="fas fa-times"></i> Dibatalkan
                                                @break
                                            @default
                                                {{ $antrian->status_label }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <div class="doctor-info">
                                        @if($antrian->doctor_name)
                                            <strong>{{ $antrian->doctor_name }}</strong>
                                            @if(config('app.debug'))
                                                <small class="text-muted d-block">
                                                    @if($antrian->doctor_id && $antrian->doctorSchedule)
                                                        <span class="badge badge-success">Dari Antrian</span><br>
                                                        {{-- ID: {{ $antrian->doctor_id }} --}}
                                                    @endif
                                                    @if($antrian->medicalRecord && $antrian->medicalRecord->doctor)
                                                        <span class="badge badge-info">Dari Rekam Medis</span>
                                                    @endif
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">Belum ditentukan</span>
                                            @if(config('app.debug'))
                                                <small class="text-danger d-block">
                                                    Debug: doctor_id={{ $antrian->doctor_id ?? 'null' }}
                                                </small>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="mobile-cards">
                    @foreach ($riwayatAntrian as $antrian)
                    <div class="mobile-card-item">
                        <div class="mobile-card-header">
                            <span class="queue-number">{{ $antrian->number }}</span>
                            <span class="status-badge status-{{ $antrian->status_badge }}">
                                @switch($antrian->status)
                                    @case('waiting')
                                        <i class="fas fa-clock"></i> Menunggu
                                        @break
                                    @case('serving')
                                        <i class="fas fa-play"></i> Dilayani
                                        @break
                                    @case('finished')
                                        <i class="fas fa-check"></i> Selesai
                                        @break
                                    @case('canceled')
                                        <i class="fas fa-times"></i> Dibatalkan
                                        @break
                                    @default
                                        {{ $antrian->status_label }}
                                @endswitch
                            </span>
                        </div>
                        <div class="mobile-card-body">
                            <h6 class="patient-name">{{ $antrian->name ?? Auth::user()->name }}</h6>
                            <div class="info-grid">
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ $antrian->phone ?? Auth::user()->phone ?? '-' }}</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-venus-mars"></i>
                                    <span>{{ $antrian->gender ?? Auth::user()->gender ?? '-' }}</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-hospital"></i>
                                    <span>{{ $antrian->poli ?? $antrian->service->name ?? '-' }}</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $antrian->formatted_tanggal }}</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-user-md"></i>
                                    <span>
                                        @if($antrian->doctor_name)
                                            {{ $antrian->doctor_name }}
                                            @if(config('app.debug'))
                                                @if($antrian->doctor_id)
                                                    <small class="text-success">(Antrian)</small>
                                                @elseif($antrian->medicalRecord && $antrian->medicalRecord->doctor)
                                                    <small class="text-info">(Rekam Medis)</small>
                                                @endif
                                            @endif
                                        @else
                                            Belum ditentukan
                                        @endif
                                    </span>
                                </div>
                                @if($antrian->medicalRecord)
                                    <div class="info-item medical-info">
                                        <i class="fas fa-notes-medical"></i>
                                        <span>{{ Str::limit($antrian->medicalRecord->chief_complaint ?? 'Tidak ada keluhan', 50) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    {{ $riwayatAntrian->appends(request()->query())->links() }}
                </div>

            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>Belum Ada Riwayat Kunjungan</h3>
                    <p>Riwayat kunjungan akan muncul setelah Anda mengambil antrian dan melakukan kunjungan ke klinik.</p>
                    <a href="{{ route('antrian.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ambil Antrian Sekarang
                    </a>
                </div>
            @endif
        </div>
    </div>
</main>

<style>
/* Base Styles */
.main-content {
    padding: 30px;
    background: #f8f9fa;
    min-height: 100vh;
}

/* Page Header */
.page-header {
    background: white;
    padding: 25px 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.page-header h1 {
    font-size: 1.8rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header h1 i {
    color: #3498db;
    font-size: 1.6rem;
}

.page-header p {
    color: #7f8c8d;
    margin: 0;
    font-size: 1rem;
}

/* Alert Styles */
.alert {
    background: white;
    border: none;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
}

.alert-success {
    border-left: 5px solid #27ae60;
    color: #2e7d32;
}

.alert-danger {
    border-left: 5px solid #e74c3c;
    color: #d32f2f;
}

.alert-content {
    flex: 1;
}

.alert-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #7f8c8d;
}

/* Filter Card */
.filter-card {
    background: white;
    border-radius: 15px;
    padding: 25px 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.filter-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-title i {
    color: #3498db;
}

.filter-stats {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.filter-form {
    margin: 0;
}

.filter-options {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.filter-chip {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    padding: 12px 20px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.filter-chip:hover {
    background: #e9ecef;
    border-color: #3498db;
    color: #495057;
    text-decoration: none;
}

.filter-chip.active {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    border-color: #3498db;
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
}

.filter-chip i {
    font-size: 0.9rem;
}

/* Content Card */
.content-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px 30px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-left i {
    color: #3498db;
    font-size: 1.2rem;
}

.header-left h5 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.record-count {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Table Styles */
.table-container {
    position: relative;
}

.desktop-table {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.data-table thead th {
    background: #2c3e50;
    color: white;
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    border: none;
    white-space: nowrap;
}

.data-table tbody tr {
    border-bottom: 1px solid #f1f3f4;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

.data-table tbody td {
    padding: 16px 12px;
    vertical-align: middle;
    border: none;
}

.row-number {
    background: #e9ecef;
    color: #495057;
    padding: 6px 12px;
    border-radius: 15px;
    font-weight: 600;
    font-size: 14px;
}

.queue-number {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
    display: inline-block;
}

.patient-info {
    max-width: 200px;
}

.patient-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 6px;
    font-size: 15px;
}

.patient-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.patient-details span {
    color: #6c757d;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.patient-details i {
    width: 12px;
    font-size: 11px;
}

.service-info .service-name {
    background: #e8f5e8;
    color: #2e7d32;
    padding: 6px 12px;
    border-radius: 15px;
    font-weight: 500;
    font-size: 13px;
    display: inline-block;
}

.datetime-info {
    min-width: 120px;
}

.datetime-info .date {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
    font-size: 14px;
}

.datetime-info .time {
    color: #6c757d;
    font-size: 13px;
}

.status-badge {
    padding: 8px 14px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.status-waiting {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffc107;
}

.status-serving {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #17a2b8;
}

.status-finished {
    background: #d4edda;
    color: #155724;
    border: 1px solid #28a745;
}

.status-canceled {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #dc3545;
}

.doctor-info {
    color: #495057;
    font-weight: 500;
    font-size: 14px;
    max-width: 150px;
}

/* Mobile Cards */
.mobile-cards {
    display: none;
    padding: 20px;
    gap: 20px;
    flex-direction: column;
}

.mobile-card-item {
    background: #f8f9fa;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.mobile-card-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mobile-card-body {
    padding: 20px;
}

.mobile-card-body .patient-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item i {
    width: 16px;
    color: #3498db;
    font-size: 14px;
}

.info-item span {
    color: #495057;
    font-size: 14px;
}

.info-item.medical-info {
    background: #f0f8ff;
    padding: 10px;
    border-radius: 8px;
    border-bottom: none;
}

/* Pagination */
.pagination-container {
    padding: 20px 30px;
    border-top: 1px solid #f1f3f4;
    background: #f8f9fa;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 30px;
    color: #6c757d;
}

.empty-icon {
    font-size: 4rem;
    color: #3498db;
    margin-bottom: 20px;
    opacity: 0.7;
}

.empty-state h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #2c3e50;
}

.empty-state p {
    font-size: 1rem;
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .main-content {
        padding: 20px 15px;
    }

    .page-header {
        padding: 20px;
        margin-bottom: 20px;
    }

    .page-header h1 {
        font-size: 1.5rem;
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }

    .filter-card {
        padding: 20px;
        margin-bottom: 20px;
    }

    .filter-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }

    .filter-options {
        width: 100%;
    }

    .filter-chip {
        flex: 1;
        justify-content: center;
        min-width: 100px;
        font-size: 13px;
        padding: 10px 15px;
    }

    .card-header {
        padding: 15px 20px;
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .desktop-table {
        display: none;
    }

    .mobile-cards {
        display: flex;
    }

    .pagination-container {
        padding: 15px 20px;
    }

    .empty-state {
        padding: 40px 20px;
    }

    .empty-icon {
        font-size: 3rem;
    }

    .empty-state h3 {
        font-size: 1.3rem;
    }
}

@media (max-width: 480px) {
    .filter-chip {
        font-size: 12px;
        padding: 8px 12px;
    }

    .mobile-card-header {
        padding: 12px 15px;
    }

    .mobile-card-body {
        padding: 15px;
    }

    .info-item {
        padding: 6px 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Close alert functionality
    document.querySelectorAll('.alert-close').forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });

    // Auto hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Responsive table handling
    function handleResponsiveTable() {
        const table = document.querySelector('.desktop-table');
        const mobileCards = document.querySelector('.mobile-cards');
        
        if (window.innerWidth <= 768) {
            if (table) table.style.display = 'none';
            if (mobileCards) mobileCards.style.display = 'flex';
        } else {
            if (table) table.style.display = 'block';
            if (mobileCards) mobileCards.style.display = 'none';
        }
    }

    // Initial responsive check
    handleResponsiveTable();
    
    // Handle window resize
    window.addEventListener('resize', handleResponsiveTable);
});
</script>
@endsection