<x-app-layout>
    <div style="padding: 40px; max-width: 600px; margin: 0 auto; background: white; border-radius: 10px;">
        <h3>Add New Bus Schedule</h3>
        <form method="POST" action="{{ route('admin.buses.store') }}" style="display:flex; flex-direction:column; gap:15px; margin-top:20px;">
            @csrf
            <input name="bus_number" placeholder="Bus Number (e.g. B-101)" required style="padding:10px; border:1px solid #ddd;">
            <input name="route_name" placeholder="Route (e.g. Uttara to Campus)" required style="padding:10px; border:1px solid #ddd;">
            <input name="departure_time" type="time" required style="padding:10px; border:1px solid #ddd;">
            <input name="price" type="number" placeholder="Ticket Price" required style="padding:10px; border:1px solid #ddd;">
            
            <button type="submit" style="background:#28a745; color:white; padding:10px; border:none; cursor:pointer;">Save Bus</button>
        </form>
    </div></x-app-layout>