HTML
<x-app-layout>
    <div style="padding: 30px; max-width: 1000px; margin: 0 auto;">
        <h2><i class="bi bi-journal-text"></i> Admin Activity Logs</h2>
        <p style="color: #666;">Tracking the last 50 administrative actions.</p>

        <table style="width: 100%; background: white; border-collapse: collapse; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-top: 20px;">
            <thead style="background: #343a40; color: white;">
                <tr>
                    <th style="padding: 15px;">Time</th>
                    <th style="padding: 15px;">Admin</th>
                    <th style="padding: 15px;">Action</th>
                    <th style="padding: 15px;">Details</th>
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
    </div></x-app-layout>