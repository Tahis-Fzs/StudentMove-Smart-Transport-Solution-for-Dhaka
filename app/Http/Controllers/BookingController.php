<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BusSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\InboxService;

class BookingController extends Controller
{
    public function __construct(protected InboxService $inbox)
    {
    }
    public function index(Request $request): View
    {
        $schedules = BusSchedule::query()
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->orderBy('departure_time')
            ->get();

        if ($schedules->isEmpty()) {
            $schedules = collect([
                (object) [
                    'id' => null,
                    'route_name' => 'Uttara to DSC',
                    'bus_number' => 'Demo-1',
                    'departure_time' => '07:00',
                    'departure_location' => 'Uttara',
                    'arrival_location' => 'DSC',
                    'price' => 30,
                    'seats_total' => 40,
                ],
                (object) [
                    'id' => null,
                    'route_name' => 'Rajlakshmi to Mirpur',
                    'bus_number' => 'Demo-2',
                    'departure_time' => '09:00',
                    'departure_location' => 'Rajlakshmi',
                    'arrival_location' => 'Mirpur',
                    'price' => 30,
                    'seats_total' => 40,
                ],
                (object) [
                    'id' => null,
                    'route_name' => 'Rajlakshmi to Gulshan',
                    'bus_number' => 'Demo-3',
                    'departure_time' => '12:00',
                    'departure_location' => 'Rajlakshmi',
                    'arrival_location' => 'Gulshan',
                    'price' => 35,
                    'seats_total' => 40,
                ],
            ]);
        }

        $travelDate = $request->input('date', now()->toDateString());
        $tripCards = $schedules->map(function ($bus) use ($travelDate) {
            $total = (int) ($bus->seats_total ?? 40);
            $taken = Booking::seatsTaken($bus->id ?? null, $travelDate);
            $left = max(0, $total - $taken);

            return [
                'bus' => $bus,
                'seats_left' => $left,
                'seats_total' => $total,
            ];
        });

        $bookings = Auth::user()
            ->bookings()
            ->latest()
            ->limit(30)
            ->get();

        return view('bookings.index', [
            'tripCards' => $tripCards,
            'bookings' => $bookings,
            'travelDate' => $travelDate,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'bus_schedule_id' => $request->filled('bus_schedule_id') ? $request->input('bus_schedule_id') : null,
        ]);

        $data = $request->validate([
            'bus_schedule_id' => ['nullable', 'integer', 'exists:bus_schedules,id'],
            'route_name' => ['required', 'string', 'max:255'],
            'bus_number' => ['nullable', 'string', 'max:80'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'travel_date' => ['required', 'date', 'after_or_equal:today'],
            'departure_time' => ['nullable', 'string', 'max:20'],
            'seats' => ['required', 'integer', 'min:1', 'max:4'],
            'seat_preference' => ['nullable', 'in:any,window,aisle'],
            'notes' => ['nullable', 'string', 'max:500'],
            'fare' => ['nullable', 'numeric', 'min:0', 'max:5000'],
        ]);

        $bus = null;
        if (!empty($data['bus_schedule_id'])) {
            $bus = BusSchedule::find($data['bus_schedule_id']);
        }

        $seats = (int) $data['seats'];
        $travelDate = $data['travel_date'];

        if ($bus) {
            $total = (int) ($bus->seats_total ?? 40);
            $taken = Booking::seatsTaken($bus->id, $travelDate);
            if ($taken + $seats > $total) {
                return back()
                    ->withErrors(['seats' => 'Not enough seats left on this trip (' . max(0, $total - $taken) . ' remaining).'])
                    ->withInput();
            }
        }

        $unitFare = $bus && $bus->price !== null
            ? (float) $bus->price
            : (float) ($data['fare'] ?? 30);

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'bus_schedule_id' => $bus?->id,
            'booking_code' => Booking::generateCode(),
            'route_name' => $bus?->route_name ?? $data['route_name'],
            'bus_number' => $bus?->bus_number ?? ($data['bus_number'] ?? null),
            'origin' => $bus?->departure_location ?? $data['origin'],
            'destination' => $bus?->arrival_location ?? $data['destination'],
            'travel_date' => $travelDate,
            'departure_time' => $bus?->departure_time ?? ($data['departure_time'] ?? null),
            'seats' => $seats,
            'seat_preference' => $data['seat_preference'] ?? 'any',
            'fare' => round($unitFare * $seats, 2),
            'status' => Booking::STATUS_CONFIRMED,
            'notes' => $data['notes'] ?? null,
        ]);

        try {
            $this->inbox->pushBooking(
                Auth::user(),
                'Booking confirmed · ' . $booking->booking_code,
                $booking->pathLabel() . ' on ' . $booking->travel_date->format('M j, Y')
                    . ($booking->departure_time ? ' at ' . $booking->departure_time : '')
                    . ' · ' . $booking->seats . ' seat(s).',
                ['booking_id' => $booking->id, 'code' => $booking->booking_code]
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Ride booked! Code ' . $booking->booking_code . ' · ' . $booking->seats . ' seat(s) confirmed.');
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        if (!$booking->isCancellable()) {
            return back()->with('error', 'This booking can no longer be cancelled.');
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        try {
            $this->inbox->pushBooking(
                Auth::user(),
                'Booking cancelled · ' . $booking->booking_code,
                $booking->pathLabel() . ' on ' . $booking->travel_date->format('M j, Y') . ' was cancelled.',
                ['booking_id' => $booking->id, 'code' => $booking->booking_code, 'cancelled' => true]
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Booking ' . $booking->booking_code . ' cancelled.');
    }
}
