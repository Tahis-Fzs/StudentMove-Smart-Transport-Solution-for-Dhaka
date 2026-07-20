<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusSchedule;
use App\Services\BusLiveStream;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusController extends Controller
{
    public function index()
    {
        $buses = BusSchedule::orderBy('created_at', 'desc')->get();

        return view('admin.bus.index', compact('buses'));
    }

    public function create()
    {
        return view('admin.bus.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        BusSchedule::create(array_merge($data, ['status' => 'on_time']));

        return redirect()->route('admin.buses.index')->with('success', 'Bus Route Created Successfully!');
    }

    public function edit(BusSchedule $bus): View
    {
        return view('admin.bus.edit', compact('bus'));
    }

    public function update(Request $request, BusSchedule $bus): RedirectResponse
    {
        $bus->update($this->validated($request));

        return redirect()->route('admin.buses.index')->with('success', 'Bus route updated.');
    }

    public function destroy($id): RedirectResponse
    {
        BusSchedule::find($id)?->delete();

        return back()->with('success', 'Bus Route Deleted!');
    }

    public function editGps($id): View
    {
        $bus = BusSchedule::findOrFail($id);

        return view('admin.bus.gps', compact('bus'));
    }

    public function updateGps(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $bus = BusSchedule::findOrFail($id);

        $bus->update([
            'current_lat' => $request->lat,
            'current_lng' => $request->lng,
            'status' => 'manual_override',
            'location_updated_at' => now(),
        ]);

        BusLiveStream::publishGps((int) $bus->id, [
            'lat' => (float) $request->lat,
            'lng' => (float) $request->lng,
            'heading' => $bus->heading !== null && $bus->heading !== '' ? (float) $bus->heading : null,
            'speed_kmh' => $bus->speed_kmh !== null ? (float) $bus->speed_kmh : null,
            'location_updated_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('admin.buses.index')->with('success', 'Bus Location Manually Overridden!');
    }

    protected function validated(Request $request): array
    {
        $request->validate([
            'bus_number' => ['required', 'string', 'max:80'],
            'route_name' => ['required', 'string', 'max:255'],
            'departure_time' => ['required'],
            'departure_location' => ['nullable', 'string', 'max:255'],
            'arrival_location' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'schedule_note' => ['nullable', 'string', 'max:1000'],
            'university_tags' => ['nullable', 'string', 'max:255'],
            'run_days' => ['nullable', 'array'],
            'run_days.*' => ['in:sat,sun,mon,tue,wed,thu'],
        ]);

        return [
            'bus_number' => $request->bus_number,
            'route_name' => $request->route_name,
            'departure_time' => $request->departure_time,
            'departure_location' => $request->departure_location ?: null,
            'arrival_location' => $request->arrival_location ?: null,
            'price' => $request->price,
            'run_days' => BusSchedule::normalizeRunDays($request->input('run_days')),
            'schedule_note' => $request->schedule_note ?: null,
            'university_tags' => BusSchedule::parseUniversityTags($request->university_tags),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
