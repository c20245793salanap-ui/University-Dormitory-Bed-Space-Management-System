@extends('layouts.app')

@section('title', 'Overview')
@section('heading', 'Dormitory overview')

@section('content')
    <div class="welcome-row">
        <div>
            <p class="page-kicker">YOUR HOUSING AT A GLANCE</p>
            <h2>Welcome to your dormitory dashboard</h2>
            <p class="muted">Keep track of students, bed availability, and active leases in one place.</p>
        </div>
        <a class="button button-primary" href="{{ route('dormitory.contracts.index') }}">
            <span aria-hidden="true">＋</span> Manage contracts
        </a>
    </div>

    <div class="stats-grid">
        <article class="stat-card">
            <div class="stat-heading"><span>Buildings</span><span class="stat-icon stat-icon-blue" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M9 9h.01M15 9h.01M9 12h.01M15 12h.01"/></svg></span></div>
            <strong>{{ number_format($buildingCount) }}</strong>
            <small>Residence buildings</small>
        </article>
        <article class="stat-card">
            <div class="stat-heading"><span>Rooms</span><span class="stat-icon stat-icon-violet" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 3v18"/></svg></span></div>
            <strong>{{ number_format($roomCount) }}</strong>
            <small>Across all buildings</small>
        </article>
        <article class="stat-card">
            <div class="stat-heading"><span>Students</span><span class="stat-icon stat-icon-amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M16 4.5a4 4 0 0 1 0 7.5M18 15a5 5 0 0 1 3 4.5V21"/></svg></span></div>
            <strong>{{ number_format($studentCount) }}</strong>
            <small>Registered residents</small>
        </article>
        <article class="stat-card">
            <div class="stat-heading"><span>Available beds</span><span class="stat-icon stat-icon-green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6M3 16h18M5 10V7a2 2 0 0 1 2-2h3v5M3 20v-2M21 20v-2"/></svg></span></div>
            <strong>{{ number_format($availableBeds) }}</strong>
            <small>{{ number_format($occupiedBeds) }} of {{ number_format($totalBeds) }} beds occupied</small>
        </article>
    </div>

    <div class="dashboard-grid">
        <section class="panel occupancy-panel">
            <div class="panel-heading">
                <div>
                    <p class="page-kicker">CAPACITY</p>
                    <h2>Bed occupancy</h2>
                </div>
                <span class="occupancy-value">{{ $occupancyRate }}%</span>
            </div>
            <div class="progress-track" role="progressbar" aria-valuenow="{{ $occupancyRate }}" aria-valuemin="0" aria-valuemax="100" aria-label="Bed occupancy">
                <span class="progress-bar" style="width: {{ $occupancyRate }}%"></span>
            </div>
            <div class="occupancy-legend">
                <span><i class="legend-dot legend-occupied"></i> Occupied <strong>{{ number_format($occupiedBeds) }}</strong></span>
                <span><i class="legend-dot legend-vacant"></i> Vacant <strong>{{ number_format($availableBeds) }}</strong></span>
            </div>
            <div class="metric-strip">
                <div>
                    <span class="metric-caption">Active contracts</span>
                    <strong>{{ number_format($activeContracts) }}</strong>
                </div>
                <div>
                    <span class="metric-caption">Payments recorded</span>
                    <strong>{{ number_format((float) $totalPaid, 2) }}</strong>
                </div>
            </div>
        </section>

        <section class="panel recent-panel">
            <div class="panel-heading">
                <div>
                    <p class="page-kicker">LATEST ACTIVITY</p>
                    <h2>Recent contracts</h2>
                </div>
                <a class="text-link" href="{{ route('dormitory.contracts.index') }}">View all <span aria-hidden="true">→</span></a>
            </div>
            @if ($recentContracts->isEmpty())
                <div class="empty-state">
                    <span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3M8 3v5h5M8 3l5 5M12 13h8M12 17h8"/></svg></span>
                    <strong>No contracts yet</strong>
                    <span>New leases will appear here.</span>
                </div>
            @else
                <div class="recent-list">
                    @foreach ($recentContracts as $contract)
                        <div class="recent-item">
                            <span class="resident-avatar">{{ mb_substr($contract->student->student_name, 0, 1) }}</span>
                            <div class="recent-detail">
                                <strong>{{ $contract->student->student_name }}</strong>
                                <span>{{ $contract->bedSlot->dormRoom->dormBuilding->building_name }} · Room {{ $contract->bedSlot->dormRoom->room_number }} · Bed {{ $contract->bedSlot->bed_slot }}</span>
                            </div>
                            <span class="badge badge-{{ strtolower($contract->lease_status) }}">{{ $contract->lease_status }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="quick-actions">
        <div>
            <p class="page-kicker">GET STARTED</p>
            <h2>Manage your residence</h2>
            <p class="muted">Keep your normalized records connected and up to date.</p>
        </div>
        <div class="action-links">
            <a href="{{ route('dormitory.students.index') }}">Add a student <span aria-hidden="true">→</span></a>
            <a href="{{ route('dormitory.bed-slots.index') }}">Update bed availability <span aria-hidden="true">→</span></a>
            <a href="{{ route('dormitory.buildings.index') }}">Manage buildings <span aria-hidden="true">→</span></a>
        </div>
    </section>
@endsection
