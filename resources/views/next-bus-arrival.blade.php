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
            <div id="map" style="width: 100%; height: 250px; border-radius: 12px; margin-top: 15px;"></div>

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
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
    <script>
        function initMap() {
            // Default location (e.g., Dhaka)
            const dhaka = { lat: 23.8103, lng: 90.4125 };
            
            // Create Map
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: dhaka,
            });

            // Add Bus Marker
            new google.maps.Marker({
                position: dhaka,
                map: map,
                icon: '{{ asset("images/bus-icon.png") }}', // Custom bus icon
                title: "Live Bus Location"
            });
        }

        // Simulate checking for delays (FR-15)
        // In real app, this would poll the 'checkDelays' API we made
        setTimeout(() => {
            document.getElementById('delay-alert').style.display = 'block';
            document.getElementById('delay-msg').innerText = "Route Delay: Heavy traffic detected on Airport Road (15 min delay).";
        }, 3000);
    </script>
    @endpush
</x-app-layout>