@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <style>
        .stat-card { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-speedometer2"></i> Dashboard Overview</h1>
        </div>

        <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <h5>Total Users</h5>
                            <h2>{{ $totalUsers }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <h5>Active Offers</h5>
                            <h2>{{ $activeOffers }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-warning text-dark">
                        <div class="card-body">
                            <h5>Total Offers</h5>
                            <h2>{{ $totalOffers }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <h5>Notifications</h5>
                            <h2>{{ $activeNotifications }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recently Registered Users</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>University</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->university ?? 'N/A' }}</td>
                                <td>{{ $user->created_at->format('d M, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
@endsection

