@extends('admin.layout')

@section('title', 'Activity Logs')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-journal-text"></i> Admin Activity Logs</h1>
            <p style="color: #666;">Tracking the last 50 administrative actions.</p>
        </div>

        <div class="admin-section">
            <div class="users-table">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #343a40; color: white;">
                        <tr>
                            <th style="padding: 15px; text-align: left;">Time</th>
                            <th style="padding: 15px; text-align: left;">Admin</th>
                            <th style="padding: 15px; text-align: left;">Action</th>
                            <th style="padding: 15px; text-align: left;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px; font-size: 0.9rem;">
                                {{ $log->created_at->format('d M, Y h:i A') }}
                                <br><small style="color:#999">{{ $log->created_at->diffForHumans() }}</small>
                            </td>
                            <td style="padding: 15px; font-weight: bold;">
                                {{ $log->admin->name ?? 'System' }}
                            </td>
                            <td style="padding: 15px;">
                                <span style="background: #e2e3e5; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td style="padding: 15px; color: #555;">
                                {{ $log->description }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection