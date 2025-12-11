<x-app-layout>
    <style>
        .bus-fleet-page {
            width: 100% !important;
            max-width: 100% !important;
            padding: 30px;
            box-sizing: border-box;
            overflow-x: visible;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .bus-fleet-page h2 {
            color: #1a202c !important;
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .bus-fleet-page h2 i {
            color: #667eea;
            margin-right: 10px;
        }
        .bus-header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .add-bus-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .add-bus-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .bus-table-wrapper {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12), 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .bus-table-container {
            overflow-x: auto;
            width: 100%;
            border-radius: 12px;
        }
        .bus-table-container table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        .bus-table-container thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .bus-table-container thead th {
            color: white !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 18px 15px !important;
            text-align: left;
        }
        .bus-table-container tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #e5e7eb;
        }
        .bus-table-container tbody tr:hover {
            background: #f8f9ff;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .bus-table-container tbody td {
            padding: 18px 15px !important;
            color: #374151;
            font-size: 0.95rem;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        .bus-status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-active-bus {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .status-inactive-bus {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        .action-btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.2);
        }
        .action-btn-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        }
        .success-alert {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            font-weight: 500;
        }
    </style>
    <div class="bus-fleet-page">
        <div class="bus-header-section">
            <h2><i class="bi bi-bus-front-fill"></i> Bus Fleet Management</h2>
            <a href="{{ route('admin.buses.create') }}" class="add-bus-btn">
                <i class="bi bi-plus-circle"></i> Add New Bus
            </a>
        </div>

        @if(session('success'))
            <div class="success-alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="bus-table-wrapper">
            <div class="bus-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Bus No</th>
                            <th>Route</th>
                            <th>Time</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($buses as $bus)
                        <tr>
                            <td><strong>{{ $bus->bus_number }}</strong></td>
                            <td>{{ $bus->route_name }}</td>
                            <td>{{ $bus->departure_time }}</td>
                            <td><strong>{{ $bus->price }} Tk</strong></td>
                            <td>
                                <span class="bus-status-badge {{ $bus->is_active ? 'status-active-bus' : 'status-inactive-bus' }}">
                                    {{ $bus->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.buses.destroy', $bus->id) }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn-delete" onclick="return confirm('Are you sure you want to delete this bus route?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                                @if($bus->current_lat && $bus->current_lng)
                                    <a href="{{ route('admin.buses.gps', $bus->id) }}" style="margin-left: 8px; color: #667eea; text-decoration: none; font-size: 1.1rem;" title="Edit GPS">
                                        <i class="bi bi-geo-alt"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #888;">
                                <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                No buses found. <a href="{{ route('admin.buses.create') }}" style="color: #667eea; text-decoration: none;">Add your first bus</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>