@extends('layouts.main')

@section('content')
<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-history"></i>Riwayat Kunjungan</h1>
        <p>Lihat riwayat kunjungan dan status antrian Anda</p>
    </div>

    {{-- ✅ Alert Error/Success --}}
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- ✅ Filter Container yang diperbaiki --}}
    <div class="sort-container">
        <div class="custom-dropdown">
            <div class="dropdown-header" onclick="toggleFilterDropdown()">
                <div class="dropdown-selected">
                    <i class="fas fa-filter dropdown-icon"></i>
                    <span id="selected-text">
                        @if(request('poli'))
                            {{ request('poli') }}
                        @else
                            Semua Poli
                        @endif
                    </span>
                </div>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </div>
            <div class="dropdown-menu" id="filter-dropdown-menu">
                <div class="dropdown-item {{ !request('poli') ? 'active' : '' }}" onclick="selectOption('all', 'Semua Poli')">
                    <i class="fas fa-list-ul item-icon"></i>
                    <span>Semua Poli</span>
                    @if(!request('poli'))
                        <i class="fas fa-check check-icon"></i>
                    @endif
                </div>
                @if(isset($availableServices))
                    @foreach($availableServices as $service)
                        <div class="dropdown-item {{ request('poli') == $service ? 'active' : '' }}" onclick="selectOption('{{ $service }}', '{{ $service }}')">
                            <i class="fas fa-stethoscope item-icon"></i>
                            <span>{{ $service }}</span>
                            @if(request('poli') == $service)
                                <i class="fas fa-check check-icon"></i>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- ✅ Content Container --}}
    <div class="history-grid"> 
        <div class="card mobile-card">
            <div class="card-header mobile-header">
                <h5 class="mobile-title">
                    <i class="fas fa-history me-2"></i>Riwayat Kunjungan
                </h5>
                <span class="mobile-count badge bg-light text-dark">
                    {{ $riwayatAntrian->total() ?? 0 }}
                </span>
            </div>
            <div class="card-body p-0">
                @if($riwayatAntrian && $riwayatAntrian->count() > 0)
                
                {{-- ✅ Desktop Table --}}
                <div class="desktop-view">
                    <div class="table-container">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>No Antrian</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>Gender</th>
                                    <th>HP</th>
                                    <th>Poli</th>
                                    <th>Tgl Antrian</th>
                                    <th>Status</th>
                                    <th>Dokter</th>
                                    <th>Tgl Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($riwayatAntrian as $key => $antrian)
                                <tr>
                                    <td>{{ $riwayatAntrian->firstItem() + $key }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $antrian->number }}</span>
                                    </td>
                                    <td>{{ $antrian->name ?? Auth::user()->name }}</td>
                                    <td>{{ $antrian->address ?? Auth::user()->address ?? '-' }}</td>
                                    <td>{{ $antrian->gender ?? Auth::user()->gender ?? '-' }}</td>
                                    <td>{{ $antrian->phone ?? Auth::user()->phone ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $antrian->poli ?? $antrian->service->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>{{ $antrian->formatted_tanggal }}</td>
                                    <td>
                                        <span class="badge bg-{{ $antrian->status_badge }}">
                                            {{ $antrian->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $antrian->doctor_name ?? '-' }}</td>
                                    <td>
                                        {{ $antrian->created_at->format('d/m/Y') }}<br>
                                        <small>{{ $antrian->created_at->format('H:i') }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ✅ Mobile Cards --}}
                <div class="mobile-view">
                    <div class="mobile-cards">
                        @foreach ($riwayatAntrian as $antrian)
                        <div class="mobile-card-item">
                            <div class="mobile-card-header">
                                <span class="badge bg-info">{{ $antrian->number }}</span>
                                <span class="badge bg-{{ $antrian->status_badge }}">
                                    {{ $antrian->status_label }}
                                </span>
                            </div>
                            <div class="mobile-card-body">
                                <h6 class="patient-name">{{ $antrian->name ?? Auth::user()->name }}</h6>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>{{ $antrian->address ?? Auth::user()->address ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-venus-mars"></i>
                                        <span>{{ $antrian->gender ?? Auth::user()->gender ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $antrian->phone ?? Auth::user()->phone ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-hospital"></i>
                                        <span class="badge bg-primary">
                                            {{ $antrian->poli ?? $antrian->service->name ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>{{ $antrian->formatted_tanggal }}</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-user-md"></i>
                                        <span>{{ $antrian->doctor_name ?? '-' }}</span>
                                    </div>
                                    @if($antrian->medicalRecord)
                                        <div class="info-item">
                                            <i class="fas fa-notes-medical"></i>
                                            <span>{{ Str::limit($antrian->medicalRecord->chief_complaint ?? '-', 50) }}</span>
                                        </div>
                                        @if($antrian->medicalRecord->diagnosis)
                                            <div class="info-item">
                                                <i class="fas fa-stethoscope"></i>
                                                <span><strong>Diagnosis:</strong> {{ Str::limit($antrian->medicalRecord->diagnosis, 50) }}</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- ✅ Pagination --}}
                <div class="pagination-container">
                    <small class="text-muted">
                        {{ $riwayatAntrian->firstItem() ?? 0 }}-{{ $riwayatAntrian->lastItem() ?? 0 }} 
                        dari {{ $riwayatAntrian->total() ?? 0 }}
                    </small>
                    <div class="pagination-links">
                        {{ $riwayatAntrian->appends(request()->query())->links() }}
                    </div>
                </div>

                @else
                {{-- ✅ Empty State --}}
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h5>Belum Ada Riwayat</h5>
                    <p>Riwayat kunjungan akan muncul setelah Anda mengambil antrian.</p>
                    <a href="{{ route('antrian.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Ambil Antrian
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="backdrop-overlay" id="backdrop-overlay"></div>

{{-- ✅ CSS Styles (sama seperti sebelumnya) --}}
<style>
/* Base */
.main-content {
    padding: 30px 30px 0 30px;
    background: #f8f9fa;
    min-height: 100vh;
    z-index: 1000;
}

/* Alert Styles */
.alert {
    border: none;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

/* Page Header */
.page-header {
    background: white;
    padding: 25px 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08); 
    border-radius: 15px; 
}

.page-header h1 {
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0 0 5px 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header h1 i {
    font-size: 1.6rem;
    color: #6c757d;
}

.page-header p {
    font-size: 1rem;
    margin: 0;
    color: #6c757d;
    font-weight: 400;
}

/* Filter Container */
.sort-container {
    padding: 0;
    background: transparent;
    border-bottom: none;
    position: relative;
    z-index: 1500;
    margin-bottom: 25px;
}

.custom-dropdown {
    position: relative;
    z-index: 1501;
    margin: 0 0px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    padding: 0;
}

.dropdown-header {
    background: transparent;
    border: none;
    border-radius: 15px;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    z-index: 1502;
}

.dropdown-header:hover {
    border-color: #667eea;
    background: white;
}

.dropdown-header.active {
    border-color: #667eea;
    background: white;
    border-radius: 15px 15px 0 0;
}

.dropdown-selected {
    display: flex;
    align-items: center;
    flex: 1;
}

.dropdown-icon {
    color: #667eea;
    margin-right: 12px;
    font-size: 1.1rem;
}

.dropdown-selected span {
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.dropdown-arrow {
    color: #6c757d;
    font-size: 0.9rem;
    transition: transform 0.3s;
}

.dropdown-header.active .dropdown-arrow {
    transform: rotate(180deg);
    color: #667eea;
}

/* Dropdown Menu */
.sort-container .dropdown-menu {
    position: absolute;
    top: calc(100% - 0px);
    left: 0;
    right: 0;
    background: white;
    border: none;
    border-top: none;
    border-radius: 0 0 15px 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    z-index: 1503;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}

.sort-container .dropdown-menu.show {
    opacity: 1;
    visibility: visible;
}

.sort-container .dropdown-item {
    padding: 15px 20px;
    display: flex;
    align-items: center;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f4;
    background: white;
}

.sort-container .dropdown-item:last-child {
    border-bottom: none;
}

.sort-container .dropdown-item:hover {
    background: #f8f9fa;
}

.sort-container .dropdown-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.item-icon {
    margin-right: 12px;
    font-size: 1rem;
    width: 20px;
    text-align: center;
    color: #6c757d;
}

.sort-container .dropdown-item.active .item-icon {
    color: white;
}

.sort-container .dropdown-item span {
    flex: 1;
    font-size: 0.95rem;
    font-weight: 500;
}