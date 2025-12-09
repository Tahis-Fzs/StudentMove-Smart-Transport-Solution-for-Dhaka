<x-app-layout>
    <div style="max-width: 1000px; margin: 40px auto; padding: 20px;">
        <h2><i class="bi bi-chat-left-text-fill"></i> User Feedback Manager</h2>

        @if(session('success'))
            <div style="background: #d4edda; padding: 10px; margin: 15px 0; border-radius: 5px; color: #155724;">
                {{ session('success') }}
            </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 20px; margin-top: 20px;">
            @foreach($feedbacks as $feedback)
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <strong>{{ $feedback->subject }}</strong>
                    <span style="font-size: 0.85rem; color: #666;">From: {{ $feedback->user->name ?? 'Guest' }} | {{ $feedback->created_at->format('d M, Y') }}</span>
                </div>
                
                <p style="margin: 15px 0; color: #333;">{{ $feedback->message }}</p>

                @if($feedback->status == 'replied')
                    <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #28a745; margin-top: 15px;">
                        <strong>Admin Response:</strong>
                        <p style="margin: 5px 0;">{{ $feedback->admin_response }}</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('feedback.reply', $feedback->id) }}" style="margin-top: 15px;">
                        @csrf
                        <textarea name="admin_response" rows="2" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Type your reply here..." required></textarea>
                        <button type="submit" style="margin-top: 10px; background: #007bff; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer;">
                            <i class="bi bi-reply-fill"></i> Send Reply
                        </button>
                    </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>