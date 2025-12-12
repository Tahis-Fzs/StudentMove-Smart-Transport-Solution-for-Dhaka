@extends('admin.layout')

@section('title', 'Add New Bus')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-bus-front"></i> Add New Bus Schedule</h1>
        </div>
        <div class="admin-section">
            <form method="POST" action="{{ route('admin.buses.store') }}" style="display:flex; flex-direction:column; gap:15px;">
                @csrf
                <div class="form-group">
                    <label>Bus Number</label>
                    <input name="bus_number" placeholder="Bus Number (e.g. B-101)" required style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
                </div>
                <div class="form-group">
                    <label>Route Name</label>
                    <input name="route_name" placeholder="Route (e.g. Uttara to Campus)" required style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
                </div>
                <div class="form-group">
                    <label>Departure Time</label>
                    <input name="departure_time" type="time" required style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
                </div>
                <div class="form-group">
                    <label>Ticket Price</label>
                    <input name="price" type="number" placeholder="Ticket Price" required style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
                </div>
                
                <button type="submit" class="action-btn" style="background:#28a745; color:white; padding:10px; border:none; cursor:pointer; border-radius:5px;">Save Bus</button>
                <a href="{{ route('admin.buses.index') }}" class="btn-back">← Back to Buses</a>
            </form>
        </div>
    </div>
@endsection