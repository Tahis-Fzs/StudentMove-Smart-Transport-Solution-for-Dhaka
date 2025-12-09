HTML
<x-app-layout>
    <div style="padding: 40px; max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; border: 1px solid #ddd;">
        <h3 style="color: #dc3545;"><i class="bi bi-geo-alt-fill"></i> Emergency GPS Override</h3>
        <p style="color: #666; margin-bottom: 20px;">
            Manually set the location for Bus <strong>{{ $bus->bus_number }}</strong>.
            <br><small>Only use this if the driver's GPS is not responding.</small>
        </p>

        <form method="POST" action="{{ route('admin.buses.gps.update', $bus->id) }}">
            @csrf
            
            <div style="margin-bottom: 15px;">
                <label>Latitude</label>
                <input name="lat" type="text" value="{{ $bus->current_lat }}" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label>Longitude</label>
                <input name="lng" type="text" value="{{ $bus->current_lng }}" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
            
            <button type="submit" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%;">
                <i class="bi bi-check-circle"></i> Update Location
            </button>
        </form>
    </div></x-app-layout>