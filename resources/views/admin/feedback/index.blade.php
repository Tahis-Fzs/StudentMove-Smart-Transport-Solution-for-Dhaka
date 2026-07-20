@extends('admin.layout')

@section('title', 'Feedback')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-chat-left-text"></i> Student Feedback</h1>
            <p>Review ride feedback, reply to students, and archive resolved items (FR-34 / FR-35)</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.dashboard') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="admin-section" style="margin-bottom:1rem;">
            <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                @foreach([
                    'open' => 'Open (' . $counts['open'] . ')',
                    'pending' => 'Pending (' . $counts['pending'] . ')',
                    'replied' => 'Replied (' . $counts['replied'] . ')',
                    'archived' => 'Archived (' . $counts['archived'] . ')',
                ] as $key => $label)
                    <a href="{{ route('admin.feedback.index', ['filter' => $key]) }}"
                       class="action-btn"
                       style="{{ $filter === $key ? 'background:#0b4f4c;' : 'background:#6b7280;' }} padding:8px 14px; font-size:0.85rem;">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="admin-section">
            @if($feedbacks->count() > 0)
                <div style="display:flex; flex-direction:column; gap:1rem;">
                    @foreach($feedbacks as $feedback)
                        <article style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:1.25rem;">
                            <div style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; border-bottom:1px solid #f3f4f6; padding-bottom:0.75rem; margin-bottom:0.75rem;">
                                <div>
                                    <strong style="font-size:1.05rem;">{{ $feedback->subject }}</strong>
                                    <div style="font-size:0.82rem;color:#6b7280;margin-top:0.2rem;">
                                        {{ $feedback->user?->name ?? 'Unknown' }}
                                        · {{ $feedback->user?->email }}
                                        · {{ $feedback->created_at->format('M j, Y g:i A') }}
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                    <span style="font-size:0.85rem;color:#e0952c;letter-spacing:0.05em;">
                                        @for($i = 1; $i <= 5; $i++)
                                            {{ $i <= (int) $feedback->rating ? '★' : '☆' }}
                                        @endfor
                                    </span>
                                    <span style="padding:0.2rem 0.55rem;border-radius:999px;font-size:0.75rem;font-weight:700;background:#eef7f6;color:#0b4f4c;">
                                        {{ $feedback->statusLabel() }}
                                    </span>
                                </div>
                            </div>

                            <p style="margin:0 0 0.75rem;line-height:1.5;color:#374151;white-space:pre-wrap;">{{ $feedback->message }}</p>

                            @if($feedback->admin_response)
                                <div style="background:#f0f7f6;border:1px solid rgba(11,110,106,0.2);border-radius:8px;padding:0.85rem 1rem;margin-bottom:0.75rem;">
                                    <strong style="color:#0b4f4c;">Your reply</strong>
                                    <p style="margin:0.35rem 0 0;color:#1e2630;white-space:pre-wrap;">{{ $feedback->admin_response }}</p>
                                </div>
                            @endif

                            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:flex-start;">
                                @if($feedback->status !== \App\Models\Feedback::STATUS_ARCHIVED)
                                    @if($feedback->isPending())
                                        <form method="POST" action="{{ route('admin.feedback.reply', $feedback) }}" style="flex:1; min-width:260px;">
                                            @csrf
                                            <textarea name="admin_response" rows="2" required maxlength="1000"
                                                      placeholder="Write a reply to the student…"
                                                      style="width:100%;padding:0.65rem;border:1px solid #d1d5db;border-radius:6px;font:inherit;margin-bottom:0.5rem;">{{ old('admin_response') }}</textarea>
                                            @error('admin_response')
                                                <p style="color:#ef4444;font-size:0.82rem;margin:0 0 0.5rem;">{{ $message }}</p>
                                            @enderror
                                            <button type="submit" class="action-btn" style="padding:8px 14px;">
                                                <i class="bi bi-reply"></i> Send reply
                                            </button>
                                        </form>
                                    @elseif($feedback->status === \App\Models\Feedback::STATUS_REPLIED)
                                        <form method="POST" action="{{ route('admin.feedback.reply', $feedback) }}" style="flex:1; min-width:260px;">
                                            @csrf
                                            <textarea name="admin_response" rows="2" required maxlength="1000"
                                                      style="width:100%;padding:0.65rem;border:1px solid #d1d5db;border-radius:6px;font:inherit;margin-bottom:0.5rem;">{{ $feedback->admin_response }}</textarea>
                                            <button type="submit" class="action-btn" style="padding:8px 14px;background:#6b7280;">
                                                <i class="bi bi-pencil"></i> Update reply
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.feedback.archive', $feedback) }}">
                                        @csrf
                                        <button type="submit" class="action-btn" style="padding:8px 14px;background:#9ca3af;">
                                            <i class="bi bi-archive"></i> Archive
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.feedback.restore', $feedback) }}">
                                        @csrf
                                        <button type="submit" class="action-btn" style="padding:8px 14px;">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div style="margin-top:1.25rem;">{{ $feedbacks->links() }}</div>
            @else
                <p style="padding:1.5rem;color:#6b7280;">No feedback in this view yet.</p>
            @endif
        </div>
    </div>
@endsection
