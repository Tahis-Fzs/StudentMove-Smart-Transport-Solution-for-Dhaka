@extends('admin.layout')

@section('title', 'Activity Logs')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-journal-text"></i> Admin Activity Logs</h1>
            <p>Tracking the last 50 administrative actions</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.dashboard') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="admin-section">
            <div class="section-header">
                <h2>Recent Activity ({{ $logs->count() }})</h2>
            </div>
            <div class="users-table">
                @if($logs->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td style="font-size: 0.9rem;">
                                {{ $log->created_at->format('d M, Y h:i A') }}
                                <br><small style="color:#999">{{ $log->created_at->diffForHumans() }}</small>
                            </td>
                            <td style="font-weight: bold;">
                                {{ $log->admin->name ?? 'System' }}
                            </td>
                            <td>
                                <span style="background: #e2e3e5; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; display: inline-block;">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td style="color: #555; word-wrap: break-word;">
                                {{ $log->description ?? 'N/A' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="no-data">
                    <i class="bi bi-inbox"></i>
                    <p>No activity logs found.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .no-data i {
            font-size: 3rem;
            margin-bottom: 10px;
            display: block;
        }
        .users-table table {
            width: 100%;
        }
        .users-table td {
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
    </style>
@endsection

