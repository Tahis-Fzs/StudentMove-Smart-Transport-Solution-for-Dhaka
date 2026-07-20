@extends('admin.layout')

@section('title', 'Support Chat')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-chat-dots"></i> Support Chat</h1>
            <p>Student support threads — reply to messages from the web chat</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.dashboard') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="admin-section">
            <div class="section-header">
                <h2>Threads ({{ $threads->total() }})</h2>
            </div>
            <div class="users-table">
                @if($threads->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Last activity</th>
                            <th>Unread</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($threads as $thread)
                        <tr>
                            <td>
                                <strong>{{ $thread->user?->name ?? 'Unknown' }}</strong><br>
                                <span style="font-size:0.82rem;color:#5b6572;">{{ $thread->user?->email }}</span>
                            </td>
                            <td>
                                <span style="font-weight:600;color:{{ $thread->status === 'open' ? '#0b6e6a' : '#9ca3af' }};">
                                    {{ ucfirst($thread->status) }}
                                </span>
                            </td>
                            <td style="font-size:0.85rem;">
                                {{ $thread->last_message_at?->diffForHumans() ?? '—' }}
                            </td>
                            <td>
                                @if($thread->hasUnreadForAdmin())
                                    <span style="background:#ef4444;color:#fff;padding:2px 8px;border-radius:999px;font-size:0.75rem;font-weight:700;">New</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.chat.show', $thread) }}" class="action-btn" style="padding:6px 12px;font-size:0.85rem;">
                                    <i class="bi bi-reply"></i> Open
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top:1rem;">{{ $threads->links() }}</div>
                @else
                <p style="padding:1.5rem;color:#5b6572;">No support messages yet. Students can write from Chat → Support.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
