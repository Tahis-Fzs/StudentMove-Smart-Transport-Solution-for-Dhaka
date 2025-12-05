<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/notification.css') }}">
    <link rel="stylesheet" href="{{ asset('css/next-bus-arrival.css') }}">
    @endpush

    <!-- Day Tabs -->
    <div class="nba-day-tabs">
        <button onclick="switchDay('sat')">SAT</button>
        <button class="active" onclick="switchDay('sun')">SUN</button>
        <button onclick="switchDay('mon')">MON</button>
        <button onclick="switchDay('tue')">TUE</button>
        <button onclick="switchDay('wed')">WED</button>
        <button onclick="switchDay('thu')">THU</button>
    </div>
    <div class="nba-tabs-underline"></div>

    <!-- Schedule Info -->
    <div class="nba-info">Three Schedule Available</div>
    
    <!-- Schedules -->
    <div class="nba-schedules">
        <div class="nba-schedule-card">
            <div class="nba-card-title">7.00 AM, 12 May<br><span>from: Rajlakshmi to DSC</span></div>
            
            <div id="map" style="width: 100%; height: 400px; border-radius: 12px; margin-top: 20px;"></div>
            
            <button onclick="startSimulation()" style="margin-top:10px; padding:10px; background:red; color:white; border:none; border-radius:5px;">
                🔴 Teacher Demo: Simulate Bus Movement
            </button>

            <div id="delay-alert" class="alert alert-warning" style="display: none; margin-top: 10px; background: #fff3cd; color: #856404; padding: 10px; border-radius: 8px;">
                <i class="bi bi-exclamation-triangle-fill"></i> <span id="delay-msg"></span>
            </div>
        </div>
        <div class="nba-schedule-card">
            <div class="nba-card-title">8.30 AM, 12 May<br><span>from: Rajlakshmi</span></div>
            <img class="nba-map-img" src="{{ asset('images/rout image.png') }}" alt="map">
        </div>
        <div class="nba-schedule-card">
            <div class="nba-card-title">12.00 PM, 12 May<br><span>from: Rajlakshmi</span></div>
            <img class="nba-map-img" src="{{ asset('images/rout image.png') }}" alt="map">
        </div>
    </div>
    
    <!-- Download Button -->
    <button class="nba-download-btn" onclick="downloadPDF()">
        <i class="bi bi-file-earmark-pdf"></i> Download PDF
    </button>

    @push('scripts')
    <script>
        function switchDay(day) {
            // Remove active class from all buttons
            document.querySelectorAll('.nba-day-tabs button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            event.target.classList.add('active');
            
            // Here you would typically load different schedules for different days
            console.log('Switched to:', day);
        }

        function downloadPDF() {
            // Create a link element
            const link = document.createElement('a');
            link.href = '{{ asset("pdf/bus-schedule.pdf") }}';
            link.download = 'bus-schedule.pdf';
            
            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>

    <script>
        let map;
        let busMarker;
        let busId = 1; // Assuming we are tracking Bus ID #1

        function initMap() {
            // Initial location (e.g., Dhaka)
            const startPos = { lat: 23.8103, lng: 90.4125 };

            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: startPos,
            });

            busMarker = new google.maps.Marker({
                position: startPos,
                map: map,
                icon: 'http://maps.google.com/mapfiles/kml/shapes/bus.png', // Dynamic Bus Icon
                title: "Live Bus"
            });

            // 🚀 THE DYNAMIC PART: Run this check every 3 seconds
            setInterval(fetchLiveLocation, 3000);
        }

        function fetchLiveLocation() {
            // Call the backend API
            fetch(`/api/bus/get-location/${busId}`)
                .then(response => response.json())
                .then(data => {
                    const newPos = { lat: data.lat, lng: data.lng };
                    
                    // Move the marker smoothly
                    busMarker.setPosition(newPos);
                    map.panTo(newPos);
                    
                    console.log("Bus moved to:", newPos);
                })
                .catch(error => console.error('Error fetching GPS:', error));
        }

        // --- DEMO SIMULATION FUNCTION ---
        // (This fakes the Driver App so you can show the teacher movement without a real bus)
        function startSimulation() {
            let lat = 23.8103;
            let lng = 90.4125;
            
            setInterval(() => {
                // Move slightly North-East
                lat += 0.0001; 
                lng += 0.0001;
                
                // Send this "Fake" Driver update to the database
                fetch('/api/bus/update-location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        bus_id: busId,
                        lat: lat,
                        lng: lng
                    })
                });
            }, 3000); // Driver updates every 3 seconds
            alert("Simulation Started! The bus marker will now move automatically.");
        }
    </script>
    @endpush
</x-app-layout>