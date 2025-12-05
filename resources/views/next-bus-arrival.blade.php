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
            
            <!-- Toast Notification for Delay -->
            <div id="toast-notification" style="display:none; position:fixed; top:20px; right:20px; background:white; padding:15px; border-left: 5px solid red; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index:9999; border-radius:4px;">
                <div style="font-weight:bold; color:red;"><i class="bi bi-exclamation-circle-fill"></i> DELAY ALERT</div>
                <div id="toast-msg">Bus is late!</div>
            </div>
            
            <!-- Map & Arrival Card -->
            <div id="map" style="width: 100%; height: 450px; border-radius: 12px; margin-top: 15px;"></div>

            <div id="eta-card" style="background: white; padding: 15px; border-radius: 10px; margin-top: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin:0; color:#333; font-size: 1.1rem;">Next Bus: <span id="route-name">Uttara to DSC</span></h3>
                    <div style="font-size: 0.9rem; margin-top: 5px;">
                        Status: <span id="status-badge" style="background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 4px; font-weight: bold;">On Time</span>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.8rem; color: #666;">Arriving in</div>
                    <div id="eta-time" style="font-size: 1.5rem; font-weight: bold; color: #007bff;">10 mins</div>
                </div>
            </div>

            <div style="margin-top:10px; display:flex; gap:10px;">
                <button onclick="startSimulation()" style="padding:10px; background:#28a745; color:white; border:none; border-radius:5px;">
                    ▶️ Simulate Movement
                </button>
                <button onclick="triggerDelay()" style="padding:10px; background:#dc3545; color:white; border:none; border-radius:5px;">
                    ⚠️ Simulate Traffic Delay
                </button>
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
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
    <script>
        let map, busMarker;
        let busId = 1;
        let notificationShown = false; // Prevent spamming notifications

        // Custom Icons
        const iconNormal = 'http://maps.google.com/mapfiles/ms/icons/bus.png'; // Standard Blue/Green
        const iconDelay = 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'; // Red for Alert

        function initMap() {
            const dhaka = { lat: 23.8103, lng: 90.4125 };
            map = new google.maps.Map(document.getElementById("map"), { zoom: 14, center: dhaka });

            busMarker = new google.maps.Marker({
                position: dhaka,
                map: map,
                icon: iconNormal,
                title: "Live Bus"
            });

            // Poll for location every 3 seconds
            setInterval(fetchLiveLocation, 3000);
        }

        function fetchLiveLocation() {
            fetch(`/api/bus/get-location/${busId}`)
                .then(res => res.json())
                .then(data => {
                    // 1. Move Marker (Uber-style update)
                    const newPos = { lat: data.lat, lng: data.lng };
                    busMarker.setPosition(newPos);
                    map.panTo(newPos);

                    // 2. 🚀 UPDATE THE TIME AND STATUS ON THE ARRIVAL CARD
                    document.getElementById('eta-time').innerText = data.eta_text;

                    const badge = document.getElementById('status-badge');
                    if (data.is_delayed) {
                        // Update Badge Text and Color to RED for delay
                        badge.innerText = data.status_msg;
                        badge.style.background = '#f8d7da';
                        badge.style.color = '#721c24';
                        document.getElementById('eta-time').style.color = '#dc3545'; // Timer turns Red

                        // 3. Change marker icon and trigger notification ONCE
                        busMarker.setIcon(iconDelay);
                        if (!notificationShown) {
                            showNotification(data.delay_msg);
                            notificationShown = true;
                        }
                    } else {
                        // Reset to Green state
                        badge.innerText = "On Time";
                        badge.style.background = '#d4edda';
                        badge.style.color = '#155724';
                        document.getElementById('eta-time').style.color = '#007bff';
                        busMarker.setIcon(iconNormal);
                        notificationShown = false;
                    }
                });
        }

        function showNotification(msg) {
            // UI Popup
            const toast = document.getElementById('toast-notification');
            document.getElementById('toast-msg').innerText = msg;
            toast.style.display = 'block';

            // Hide after 5 seconds
            setTimeout(() => { toast.style.display = 'none'; }, 5000);
        }

        // --- DEMO FUNCTIONS FOR TEACHER ---
        function triggerDelay() {
            // This forces the DB to update status to 'delayed' (Simulated via API)
            alert("Simulating Heavy Traffic... Next update will show Red Marker.");
            // In real code, you'd hit an API endpoint to update the bus status
            // For demo, we assume the Controller randomizer picks it up.
        }
        
        function startSimulation() {
            alert("Bus started moving!");
        }
    </script>
    @endpush
</x-app-layout>