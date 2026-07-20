@extends('admin.layout')

@section('title', 'Support Thread')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-chat-left-text"></i> Support · {{ $thread->user?->name ?? 'Student' }}</h1>
            <p>{{ $thread->user?->email }} · {{ $thread->user?->university ?? 'No university set' }}</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.chat.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> All threads
            </a>
            @if($thread->status === 'open')
                <form method="POST" action="{{ route('admin.chat.close', $thread) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="action-btn" style="background:#6b7280;">Close thread</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.chat.reopen', $thread) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="action-btn">Reopen thread</button>
                </form>
            @endif
        </div>

        <div class="admin-section" style="max-width:760px;">
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:1.25rem;margin-bottom:1rem;max-height:420px;overflow-y:auto;">
                @forelse($thread->messages as $msg)
                    <div style="margin-bottom:1rem;padding:0.75rem 1rem;border-radius:8px;{{ $msg->role === 'user' ? 'background:#eef7f6;margin-left:2rem;' : 'background:#f3f4f6;margin-right:2rem;' }}">
                        <div style="font-size:0.75rem;font-weight:700;color:#5b6572;margin-bottom:0.35rem;">
                            {{ $msg->senderLabel() }} · {{ $msg->created_at->format('M j, g:i A') }}
                        </div>
                        <div style="white-space:pre-wrap;line-height:1.45;">{{ $msg->body }}</div>
                    </div>
                @empty
                    <p style="color:#9ca3af;">No messages in this thread.</p>
                @endforelse
            </div>

            @if($thread->status === 'open')
            <form method="POST" action="{{ route('admin.chat.reply', $thread) }}">
                @csrf
                <label for="message" style="display:block;font-weight:600;margin-bottom:0.4rem;">Reply to student</label>
                <textarea id="message" name="message" rows="4" required maxlength="2000"
                          style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:6px;font:inherit;margin-bottom:0.75rem;"></textarea>
                @error('message')
                    <p style="color:#ef4444;font-size:0.85rem;margin-bottom:0.5rem;">{{ $message }}</p>
                @enderror
                <button type="submit" class="action-btn"><i class="bi bi-send"></i> Send reply</button>
            </form>
            @else
                <p style="color:#6b7280;font-style:italic;">Thread is closed. Reopen to send more replies.</p>
            @endif
        </div>
    </div>
@endsection
