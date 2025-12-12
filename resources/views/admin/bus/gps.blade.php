@extends('admin.layout')

@section('title', 'GPS Override')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1 style="color: #dc3545;"><i class="bi bi-geo-alt-fill"></i> Emergency GPS Override</h1>
            <p style="color: #666;">
                Manually set the location for Bus <strong>{{ $bus->bus_number }}</strong>.
                <br><small>Only use this if the driver's GPS is not responding.</small>
            </p>
        </div>

        <div class="admin-section">
            <form method="POST" action="{{ route('admin.buses.gps.update', $bus->id) }}">
                @csrf
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Latitude</label>
                    <input name="lat" type="text" value="{{ $bus->current_lat }}" required 
                           style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Longitude</label>
                    <input name="lng" type="text" value="{{ $bus->current_lng }}" required 
                           style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                
                <button type="submit" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%;">
                    <i class="bi bi-check-circle"></i> Update Location
                </button>
                <a href="{{ route('admin.buses.index') }}" class="btn-back" style="display:block; text-align:center; margin-top:10px;">← Back to Buses</a>
            </form>
        </div>
    </div>
@endsection