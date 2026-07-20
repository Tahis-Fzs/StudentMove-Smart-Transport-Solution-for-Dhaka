<x-app-layout>
    @push('styles')
    <style>
        .fb-page { max-width: 720px; margin: 2rem auto 3rem; padding: 0 1.25rem; }
        .fb-card {
            background: #fff;
            border: 1px solid rgba(18, 22, 28, 0.08);
            border-radius: 1rem;
            box-shadow: 0 18px 40px rgba(18, 22, 28, 0.08);
            padding: 1.75rem 1.5rem;
        }
        .fb-title {
            margin: 0;
            font-family: Syne, sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #12161c;
        }
        .fb-sub { margin: 0.35rem 0 1.35rem; color: #5b6572; }
        .fb-flash {
            display: flex; align-items: center; gap: 0.65rem;
            padding: 0.9rem 1rem; margin-bottom: 1.25rem;
            background: rgba(11, 110, 106, 0.1);
            border: 1px solid rgba(11, 110, 106, 0.25);
            border-radius: 0.65rem; color: #0b4f4c; font-weight: 600;
        }
        .fb-label { display: block; font-size: 0.85rem; font-weight: 600; color: #3d4654; margin-bottom: 0.4rem; }
        .fb-input, .fb-textarea, .fb-select {
            width: 100%; padding: 0.75rem 0.9rem; border-radius: 0.5rem;
            border: 1px solid #c9d2db; background: #fff; color: #1e2630;
            font: inherit; margin-bottom: 1rem;
        }
        .fb-input:focus, .fb-textarea:focus, .fb-select:focus {
            outline: none; border-color: #0b6e6a;
            box-shadow: 0 0 0 3px rgba(11, 110, 106, 0.15);
        }
        .fb-stars { display: flex; gap: 0.35rem; margin-bottom: 1.15rem; }
        .fb-stars label {
            cursor: pointer; font-size: 1.5rem; color: #d5dde5;
            transition: color 0.15s, transform 0.15s;
        }
        .fb-stars input { position: absolute; opacity: 0; pointer-events: none; }
        .fb-stars label.is-on, .fb-stars label:hover { color: #e0952c; }
        .fb-submit {
            background: #0b6e6a; color: #fff; border: none;
            padding: 0.8rem 1.25rem; border-radius: 0.45rem;
            font-weight: 700; cursor: pointer; font: inherit;
        }
        .fb-submit:hover { filter: brightness(1.06); }
        .fb-history { margin-top: 1.75rem; }
        .fb-history h2 {
            font-family: Syne, sans-serif; font-size: 1.15rem;
            margin: 0 0 0.85rem; color: #12161c;
        }
        .fb-item {
            border: 1px solid rgba(18, 22, 28, 0.1);
            border-radius: 0.75rem; padding: 1rem; margin-bottom: 0.75rem;
            background: #f7f8fa;
        }
        .fb-item h3 { margin: 0; color: #1e2630; font-size: 1rem; }
        .fb-item .fb-date { color: #7a8490; font-size: 0.8rem; }
        .fb-item p { margin: 0.5rem 0; color: #3d4654; line-height: 1.45; }
        .fb-item .fb-rating { color: #e0952c; letter-spacing: 0.04em; }
        .fb-admin {
            margin-top: 0.65rem; padding: 0.7rem 0.85rem;
            background: rgba(11, 110, 106, 0.08);
            border-radius: 0.5rem; border: 1px solid rgba(11, 110, 106, 0.18);
            color: #0b4f4c; font-size: 0.9rem;
        }
    </style>
    @endpush

    <div class="fb-page">
        <div class="fb-card">
            <h1 class="fb-title">Feedback</h1>
            <p class="fb-sub">Rate your ride and help us improve routes across Dhaka.</p>

            @if(session('success'))
                <div class="fb-flash">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('feedback.store') }}" method="POST" id="feedback-form">
                @csrf
                <label class="fb-label" for="subject">Subject</label>
                <input class="fb-input" type="text" name="subject" id="subject" required
                       placeholder="e.g. Morning Uttara → DSC ride" value="{{ old('subject') }}">

                <label class="fb-label" for="message">Your review</label>
                <textarea class="fb-textarea" name="message" id="message" rows="5" required
                          placeholder="What went well? Timing, comfort, fare…">{{ old('message') }}</textarea>

                <label class="fb-label">Rating</label>
                <input type="hidden" name="rating" id="rating" value="{{ old('rating', 5) }}">
                <div class="fb-stars" id="fb-stars" role="radiogroup" aria-label="Rating">
                    @for($i = 1; $i <= 5; $i++)
                        <label data-value="{{ $i }}" class="{{ $i <= (int) old('rating', 5) ? 'is-on' : '' }}" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}">★</label>
                    @endfor
                </div>

                <button type="submit" class="fb-submit">Submit feedback</button>
            </form>

            @if($feedbacks->count() > 0)
                <div class="fb-history">
                    <h2>Your feedback history</h2>
                    @foreach($feedbacks as $feedback)
                        <div class="fb-item">
                            <div style="display:flex; justify-content:space-between; gap:0.75rem;">
                                <h3>{{ $feedback->subject }}</h3>
                                <span class="fb-date">{{ $feedback->created_at->format('d M, Y') }}</span>
                            </div>
                            <p>{{ $feedback->message }}</p>
                            <div class="fb-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    {{ $i <= $feedback->rating ? '★' : '☆' }}
                                @endfor
                            </div>
                            @if($feedback->admin_response)
                                <div class="fb-admin">
                                    <strong>Team reply:</strong> {{ $feedback->admin_response }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const input = document.getElementById('rating');
            const stars = document.querySelectorAll('#fb-stars label');
            const paint = (n) => stars.forEach((s) => s.classList.toggle('is-on', +s.dataset.value <= n));
            stars.forEach((star) => {
                star.addEventListener('click', () => {
                    input.value = star.dataset.value;
                    paint(+star.dataset.value);
                });
                star.addEventListener('mouseenter', () => paint(+star.dataset.value));
            });
            document.getElementById('fb-stars').addEventListener('mouseleave', () => paint(+input.value));
        })();
    </script>
    @endpush
</x-app-layout>
