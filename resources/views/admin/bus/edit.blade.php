@extends('admin.layout')

@section('title', 'Edit Bus')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-bus-front"></i> Edit Bus Schedule</h1>
            <p>{{ $bus->bus_number }} · {{ $bus->route_name }}</p>
        </div>
        <div class="admin-section">
            <form method="POST" action="{{ route('admin.buses.update', $bus) }}" style="display:flex; flex-direction:column; gap:15px;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Bus Number</label>
                    <input name="bus_number" value="{{ old('bus_number', $bus->bus_number) }}" required style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
                </div>
                <div class="form-group">
                    <label>Route Name</label>
                    <input name="route_name" value="{{ old('route_name', $bus->route_name) }}" required style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
                </div>
                <div class="form-group">
                    <label>Departure Time</label>
                    <input name="departure_time" type="time" value="{{ old('departure_time', $bus->departure_time) }}" required style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
                </div>
                <div class="form-group">
                    <label>Ticket Price</label>
                    <input name="price" type="number" value="{{ old('price', $bus->price) }}" required style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
                </div>

                @include('admin.bus._schedule_fields', ['bus' => $bus])

                <button type="submit" class="action-btn" style="background:#0b6e6a; color:white; padding:10px; border:none; cursor:pointer; border-radius:5px;">Update Bus</button>
                <a href="{{ route('admin.buses.index') }}" class="btn-back">← Back to Buses</a>
            </form>
        </div>
    </div>
@endsection
