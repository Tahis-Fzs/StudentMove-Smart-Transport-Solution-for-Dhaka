<x-app-layout title="Chat · StudentMove">
    @push('styles')
    <link rel="stylesheet" href="/css/chat.css">
    @endpush

    <div class="chat-page">
        <header class="chat-head">
            <h1 class="chat-head__title">Chat</h1>
            <p class="chat-head__lede">
                @if($channel === \App\Models\ChatThread::TYPE_SUPPORT)
                    Message our support team — replies appear here in real time.
                @else
                    Ask about routes, fares, and schedules. Your conversation is saved.
                    @unless($aiConfigured)
                        <span class="chat-offline-badge">Offline tips mode</span>
                    @endunless
                @endif
            </p>
        </header>

        <nav class="chat-tabs" aria-label="Chat channels">
            <a href="{{ route('chat.index', ['channel' => 'assistant']) }}"
               class="chat-tab {{ $channel === 'assistant' ? 'is-active' : '' }}">
                <i class="bi bi-robot"></i> AI Assistant
            </a>
            <a href="{{ route('chat.index', ['channel' => 'support']) }}"
               class="chat-tab {{ $channel === 'support' ? 'is-active' : '' }}">
                <i class="bi bi-headset"></i> Support
            </a>
        </nav>

        <div class="chat-panel" id="chat-panel" data-channel="{{ $channel }}">
            @if($channel === 'assistant')
            <div class="chat-toolbar">
                <span>Multi-turn assistant · history saved per account</span>
                <button type="button" class="chat-clear" id="chat-clear-btn">Clear history</button>
            </div>
            @else
            <div class="chat-toolbar">
                <span>
                    Status:
                    <strong>{{ $supportThread->status === 'open' ? 'Open' : 'Closed' }}</strong>
                </span>
                <span>Poll every 5s for admin replies</span>
            </div>
            @endif

            <div class="chat-messages" id="chat-messages">
                @forelse($messages as $msg)
                    <div class="chat-bubble chat-bubble--{{ $msg->role }}" data-id="{{ $msg->id }}">
                        <div class="chat-bubble__meta">{{ $msg->senderLabel() }}</div>
                        {{ $msg->body }}
                    </div>
                @empty
                    <div class="chat-empty" id="chat-empty">
                        @if($channel === 'support')
                            Describe your issue — a support agent will reply here.
                        @else
                            Try: “Best route from Uttara to DSC after 5pm?” or “Which subscription saves money?”
                        @endif
                    </div>
                @endforelse
            </div>

            <div class="chat-status" id="chat-status">Sending…</div>

            <form class="chat-compose" id="chat-form">
                @csrf
                <textarea id="chat-input" name="message" rows="2"
                          placeholder="{{ $channel === 'support' ? 'Write to support…' : 'Ask the assistant…' }}"
                          required maxlength="2000"></textarea>
                <button type="submit" class="chat-send" id="chat-send-btn">Send</button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const panel = document.getElementById('chat-panel');
            const channel = panel.dataset.channel;
            const messagesEl = document.getElementById('chat-messages');
            const form = document.getElementById('chat-form');
            const input = document.getElementById('chat-input');
            const sendBtn = document.getElementById('chat-send-btn');
            const statusEl = document.getElementById('chat-status');
            const clearBtn = document.getElementById('chat-clear-btn');
            const csrf = '{{ csrf_token() }}';

            function lastMessageId() {
                const nodes = messagesEl.querySelectorAll('[data-id]');
                if (!nodes.length) return 0;
                return Math.max(...Array.from(nodes).map(n => +n.dataset.id));
            }

            function appendBubble(msg) {
                const empty = document.getElementById('chat-empty');
                if (empty) empty.remove();

                if (messagesEl.querySelector('[data-id="' + msg.id + '"]')) return;

                const div = document.createElement('div');
                div.className = 'chat-bubble chat-bubble--' + msg.role;
                div.dataset.id = msg.id;
                div.innerHTML = '<div class="chat-bubble__meta">' + escapeHtml(msg.label) + '</div>' +
                    escapeHtml(msg.body);
                messagesEl.appendChild(div);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }

            function escapeHtml(text) {
                const d = document.createElement('div');
                d.textContent = text;
                return d.innerHTML;
            }

            function setBusy(busy) {
                sendBtn.disabled = busy;
                statusEl.classList.toggle('is-visible', busy);
                statusEl.textContent = channel === 'assistant' ? 'Assistant is thinking…' : 'Sending…';
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const text = input.value.trim();
                if (!text) return;

                setBusy(true);
                try {
                    const res = await fetch('{{ route('chat.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ message: text, channel }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        alert(data.error || 'Could not send message.');
                        return;
                    }
                    (data.messages || []).forEach(appendBubble);
                    input.value = '';
                } catch (err) {
                    alert('Network error — please try again.');
                } finally {
                    setBusy(false);
                }
            });

            if (channel === 'support') {
                setInterval(async () => {
                    try {
                        const after = lastMessageId();
                        const res = await fetch('{{ route('chat.poll') }}?' + new URLSearchParams({
                            channel: 'support',
                            after_id: after,
                        }), { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        (data.messages || []).forEach(appendBubble);
                    } catch (e) { /* ignore poll errors */ }
                }, 5000);
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', async () => {
                    if (!confirm('Clear assistant chat history?')) return;
                    try {
                        await fetch('{{ route('chat.clear') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify({ channel: 'assistant' }),
                        });
                        messagesEl.innerHTML = '<div class="chat-empty" id="chat-empty">History cleared. Ask a new question.</div>';
                    } catch (e) {
                        alert('Could not clear history.');
                    }
                });
            }

            messagesEl.scrollTop = messagesEl.scrollHeight;
        })();
    </script>
    @endpush
</x-app-layout>
