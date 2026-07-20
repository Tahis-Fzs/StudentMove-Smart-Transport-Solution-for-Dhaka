<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/bookings.css') }}">
    @endpush

    <div class="bk-page">
        <header class="bk-header">
            <h1 class="bk-title"><i class="bi bi-ticket-perforated"></i> Book a ride</h1>
            <p class="bk-sub">Reserve seats on today’s campus shuttles — pick a trip, seats, and you’re set.</p>
        </header>

        <form method="GET" action="{{ route('bookings.index') }}" class="bk-date-bar">
            <label for="travelDateFilter">Travel date</label>
            <input type="date" id="travelDateFilter" name="date" value="{{ $travelDate }}" min="{{ now()->toDateString() }}">
            <button type="submit">Show trips</button>
        </form>

        <section class="bk-section">
            <h2 class="bk-section-title">Available trips</h2>
            <div class="bk-trips">
                @foreach ($tripCards as $card)
                    @php
                        $bus = $card['bus'];
                        $routeParts = preg_split('/\s+to\s+/i', (string) ($bus->route_name ?? ''), 2) ?: [];
                        $originFallback = $bus->departure_location ?? ($routeParts[0] ?? '');
                        $destFallback = $bus->arrival_location ?? ($routeParts[1] ?? '');
                    @endphp
                    <article class="bk-trip {{ $card['seats_left'] < 1 ? 'is-full' : '' }}"
                             data-id="{{ $bus->id }}"
                             data-route="{{ $bus->route_name }}"
                             data-bus="{{ $bus->bus_number }}"
                             data-origin="{{ $originFallback }}"
                             data-destination="{{ $destFallback }}"
                             data-time="{{ $bus->departure_time }}"
                             data-fare="{{ $bus->price ?? 30 }}"
                             data-left="{{ $card['seats_left'] }}">
                        <div class="bk-trip__top">
                            <strong>{{ $bus->route_name }}</strong>
                            <span class="bk-pill">{{ $bus->departure_time ?: '—' }}</span>
                        </div>
                        <div class="bk-trip__meta">
                            <span><i class="bi bi-bus-front"></i> {{ $bus->bus_number ?: 'Shuttle' }}</span>
                            <span>৳{{ number_format((float) ($bus->price ?? 30), 0) }}/seat</span>
                            <span class="{{ $card['seats_left'] < 5 ? 'bk-low' : '' }}">
                                {{ $card['seats_left'] }} / {{ $card['seats_total'] }} seats left
                            </span>
                        </div>
                        <button type="button" class="bk-trip__btn" @disabled($card['seats_left'] < 1)
                                onclick="selectTrip(this.closest('.bk-trip'))">
                            {{ $card['seats_left'] < 1 ? 'Full' : 'Select' }}
                        </button>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="bk-section bk-form-card" id="bookingFormSection">
            <h2 class="bk-section-title">Request seats</h2>
            <form method="POST" action="{{ route('bookings.store') }}" class="bk-form" id="bookingForm">
                @csrf
                <input type="hidden" name="bus_schedule_id" id="bus_schedule_id" value="{{ old('bus_schedule_id') }}">
                <input type="hidden" name="fare" id="fare" value="{{ old('fare', 30) }}">

                <div class="bk-grid">
                    <div>
                        <label class="bk-label">Route *</label>
                        <input class="bk-input" type="text" name="route_name" id="route_name"
                               value="{{ old('route_name') }}" required placeholder="Select a trip above">
                        @error('route_name')<span class="bk-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="bk-label">Bus #</label>
                        <input class="bk-input" type="text" name="bus_number" id="bus_number" value="{{ old('bus_number') }}">
                    </div>
                    <div>
                        <label class="bk-label">From *</label>
                        <input class="bk-input" type="text" name="origin" id="origin" value="{{ old('origin') }}" required>
                        @error('origin')<span class="bk-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="bk-label">To *</label>
                        <input class="bk-input" type="text" name="destination" id="destination" value="{{ old('destination') }}" required>
                        @error('destination')<span class="bk-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="bk-label">Travel date *</label>
                        <input class="bk-input" type="date" name="travel_date" id="travel_date"
                               value="{{ old('travel_date', $travelDate) }}" min="{{ now()->toDateString() }}" required>
                        @error('travel_date')<span class="bk-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="bk-label">Departure</label>
                        <input class="bk-input" type="text" name="departure_time" id="departure_time" value="{{ old('departure_time') }}" placeholder="07:00">
                    </div>
                    <div>
                        <label class="bk-label">Seats *</label>
                        <select class="bk-input" name="seats" id="seats" required>
                            @for ($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}" @selected((int) old('seats', 1) === $i)>{{ $i }}</option>
                            @endfor
                        </select>
                        @error('seats')<span class="bk-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="bk-label">Seat preference</label>
                        <select class="bk-input" name="seat_preference">
                            <option value="any" @selected(old('seat_preference', 'any') === 'any')>Any</option>
                            <option value="window" @selected(old('seat_preference') === 'window')>Window</option>
                            <option value="aisle" @selected(old('seat_preference') === 'aisle')>Aisle</option>
                        </select>
                    </div>
                </div>

                <label class="bk-label">Notes</label>
                <textarea class="bk-input" name="notes" rows="2" maxlength="500" placeholder="Optional pickup note…">{{ old('notes') }}</textarea>

                <p class="bk-estimate" id="fareEstimate">Estimated fare: ৳30</p>

                <button type="submit" class="bk-submit"><i class="bi bi-check2-circle"></i> Confirm booking</button>
            </form>
        </section>

        <section class="bk-section">
            <h2 class="bk-section-title">My bookings</h2>
            @forelse ($bookings as $booking)
                <div class="bk-item status-{{ $booking->status }}">
                    <div class="bk-item__head">
                        <div>
                            <strong>{{ $booking->route_name }}</strong>
                            <div class="bk-item__code">{{ $booking->booking_code }}</div>
                        </div>
                        <span class="bk-status">{{ ucfirst($booking->status) }}</span>
                    </div>
                    <div class="bk-item__path">{{ $booking->pathLabel() }}</div>
                    <div class="bk-item__meta">
                        <span>{{ $booking->travel_date->format('M j, Y') }}</span>
                        @if ($booking->departure_time)<span>{{ $booking->departure_time }}</span>@endif
                        <span>{{ $booking->seats }} seat{{ $booking->seats === 1 ? '' : 's' }} · {{ $booking->seat_preference }}</span>
                        <span>৳{{ number_format((float) $booking->fare, 0) }}</span>
                    </div>
                    @if ($booking->isCancellable())
                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                              onsubmit="return confirm('Cancel this booking?');">
                            @csrf
                            <button type="submit" class="bk-cancel">Cancel</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="bk-empty">No bookings yet. Select a trip and confirm seats.</p>
            @endforelse
        </section>
    </div>

    @push('scripts')
    <script>
        function selectTrip(el) {
            if (!el || el.classList.contains('is-full')) return;
            document.querySelectorAll('.bk-trip').forEach(t => t.classList.remove('is-selected'));
            el.classList.add('is-selected');

            document.getElementById('bus_schedule_id').value = el.dataset.id || '';
            document.getElementById('route_name').value = el.dataset.route || '';
            document.getElementById('bus_number').value = el.dataset.bus || '';
            document.getElementById('origin').value = el.dataset.origin || '';
            document.getElementById('destination').value = el.dataset.destination || '';
            document.getElementById('departure_time').value = el.dataset.time || '';
            document.getElementById('fare').value = el.dataset.fare || 30;
            document.getElementById('travel_date').value = document.getElementById('travelDateFilter').value;
            updateFare();
            document.getElementById('bookingFormSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function updateFare() {
            const unit = parseFloat(document.getElementById('fare').value) || 30;
            const seats = parseInt(document.getElementById('seats').value, 10) || 1;
            document.getElementById('fareEstimate').textContent = 'Estimated fare: ৳' + Math.round(unit * seats);
        }

        document.getElementById('seats').addEventListener('change', updateFare);
        updateFare();
    </script>
    @endpush
</x-app-layout>
