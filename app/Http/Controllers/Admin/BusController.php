class BusController extends Controller{
    // List all buses
    public function index()
    {
        $buses = BusSchedule::orderBy('created_at', 'desc')->get();
        return view('admin.buses.index', compact('buses'));
    }

    // Show form to create new bus
    public function create()
    {
        return view('admin.buses.create');
    }

    // Store new bus
    public function store(Request $request)
    {
        $request->validate([
            'bus_number' => 'required|string',
            'route_name' => 'required|string',
            'departure_time' => 'required',
            'price' => 'required|numeric',
        ]);

        BusSchedule::create([
            'bus_number' => $request->bus_number,
            'route_name' => $request->route_name,
            'departure_time' => $request->departure_time,
            'departure_location' => $request->departure_location ?? 'Campus',
            'arrival_location' => $request->arrival_location ?? 'City',
            'price' => $request->price,
            'is_active' => true,
            'status' => 'on_time'
        ]);

        return redirect()->route('admin.buses.index')->with('success', 'Bus Route Created Successfully!');
    }

    // Delete bus
    public function destroy($id)
    {
        BusSchedule::find($id)->delete();
        return back()->with('success', 'Bus Route Deleted!');
    }

    // Show form to edit the GPS coordinates of a bus
    public function editGps($id)
    {
        $bus = BusSchedule::findOrFail($id);
        return view('admin.buses.gps', compact('bus'));
    }

    // 🚀 FR-38: Update GPS Manually
    public function updateGps(Request $request, $id)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $bus = BusSchedule::findOrFail($id);
        
        $bus->update([
            'current_lat' => $request->lat,
            'current_lng' => $request->lng,
            'status' => 'manual_override' // Mark status so users know
        ]);

        return redirect()->route('admin.buses.index')->with('success', 'Bus Location Manually Overridden!');
    }
}