<x-app-layout>
    <div style="padding: 30px; max-width: 1200px; margin: 0 auto;">
        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <h2><i class="bi bi-bus-front-fill"></i> Bus Fleet Management</h2>
            <a href="{{ route('admin.buses.create') }}" style="background:#007bff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">
                + Add New Bus
            </a>
        </div>

        @if(session('success'))
            <div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:15px;">{{ session('success') }}</div>
        @endif

        <table style="width:100%; background:white; border-collapse:collapse; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
            <tr style="background:#f8f9fa; text-align:left;">
                <th style="padding:15px;">Bus No</th>
                <th style="padding:15px;">Route</th>
                <th style="padding:15px;">Time</th>
                <th style="padding:15px;">Price</th>
                <th style="padding:15px;">Status</th>
                <th style="padding:15px;">Actions</th>
            </tr>
            @foreach($buses as $bus)
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:15px;">{{ $bus->bus_number }}</td>
                <td style="padding:15px;">{{ $bus->route_name }}</td>
                <td style="padding:15px;">{{ $bus->departure_time }}</td>
                <td style="padding:15px;">{{ $bus->price }} Tk</td>
                <td style="padding:15px;">
                    <span style="padding:5px 10px; border-radius:15px; background:{{ $bus->is_active ? '#d4edda' : '#f8d7da' }}">
                        {{ $bus->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td style="padding:15px;">
                    <form method="POST" action="{{ route('admin.buses.destroy', $bus->id) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button style="background:none; border:none; color:red; cursor:pointer;" onclick="return confirm('Delete this bus?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </table>
    </div></x-app-layout>