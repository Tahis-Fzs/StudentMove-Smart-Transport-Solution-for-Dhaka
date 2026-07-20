@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <style>
        .stat-card {
            border: 1px solid rgba(18,22,28,0.08);
            border-radius: 0.75rem;
            box-shadow: 0 8px 24px rgba(18,22,28,0.06);
            transition: transform 0.2s cubic-bezier(0.16,1,0.3,1);
            overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .card-body { padding: 1.25rem 1.35rem; }
        .stat-card h5 {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 0.45rem;
            opacity: 0.85;
        }
        .stat-card h2 {
            font-family: Syne, sans-serif;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0;
            font-size: 2rem;
        }
        .stat-route { background: #0b6e6a; color: #fff; }
        .stat-signal { background: #e0952c; color: #12161c; }
        .stat-graphite { background: #1e2630; color: #fff; }
        .stat-mist { background: #fff; color: #12161c; }
        .stat-mist h5 { color: #5b6572; }
    </style>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Operations overview for StudentMove</p>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card stat-route">
                    <div class="card-body">
                        <h5>Total users</h5>
                        <h2>{{ $totalUsers }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card stat-signal">
                    <div class="card-body">
                        <h5>Active offers</h5>
                        <h2>{{ $activeOffers }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card stat-graphite">
                    <div class="card-body">
                        <h5>Total offers</h5>
                        <h2>{{ $totalOffers }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card stat-mist">
                    <div class="card-body">
                        <h5>Notifications</h5>
                        <h2>{{ $activeNotifications }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-section">
            <div class="section-header">
                <h2>Recently registered</h2>
            </div>
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>University</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->university ?? 'N/A' }}</td>
                            <td>{{ $user->created_at->format('d M, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">No users yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
