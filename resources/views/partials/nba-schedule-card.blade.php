@props(['card', 'scheduleId'])

<div class="nba-schedule-card" data-run-days='@json($card->run_days)'>
    <div class="nba-card-title" id="card-title-{{ $scheduleId }}">
        {{ $card->display_time }}, {{ $card->display_date }}<br>
        <span>{{ $card->path_label }}</span>
    </div>

    @if(!empty($card->university_tags))
        <div class="nba-uni-tags" id="card-tags-{{ $scheduleId }}">
            @foreach($card->university_tags as $tag)
                <span class="nba-uni-tag">{{ $tag }}</span>
            @endforeach
        </div>
    @endif

    @if(!empty($card->schedule_note))
        <div class="nba-whiteboard" id="card-note-{{ $scheduleId }}">
            <i class="bi bi-megaphone"></i> {{ $card->schedule_note }}
        </div>
    @endif

    <div id="map-{{ $scheduleId }}" class="schedule-map" style="width: 100%; height: 450px; border-radius: 12px; margin-top: 15px; background:#1e293b; border: 1px solid rgba(255,255,255,0.1); position: relative;">
        <div id="map-loading-{{ $scheduleId }}" class="map-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #94a3b8; font-size: 14px; z-index: 1000;">Loading map...</div>
    </div>

    <div id="eta-card-{{ $scheduleId }}" class="eta-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04)); padding: 15px; border-radius: 10px; margin-top: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.12);">
        <div>
            <h3 style="margin:0; color:#e5e7eb; font-size: 1.1rem;">Next Bus: <span id="route-name-{{ $scheduleId }}">{{ $card->route_name }}</span></h3>
            <div style="font-size: 0.9rem; margin-top: 5px;">
                Status: <span id="status-badge-{{ $scheduleId }}" style="background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 4px; font-weight: bold;">On Time</span>
            </div>
            <div id="gps-badge-{{ $scheduleId }}" class="gps-live-badge gps-live-badge--idle">GPS: waiting for driver</div>
            <div id="motion-badge-{{ $scheduleId }}" class="gps-motion-badge">Heading — · Speed —</div>
            <div id="bus-link-{{ $scheduleId }}" class="gps-bus-link">Linked driver bus: …</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.8rem; color: #cbd5e1;">Arriving in</div>
            <div id="eta-time-{{ $scheduleId }}" style="font-size: 1.5rem; font-weight: bold; color: #14a39c;">10 mins</div>
        </div>
    </div>

    <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">
        <button type="button" onclick="startSimulation({{ $scheduleId }})" style="padding:10px; background:#0b6e6a; color:white; border:none; border-radius:5px; cursor:pointer;">
            ▶️ Simulate Movement
        </button>
        <button type="button" onclick="triggerDelay({{ $scheduleId }})" style="padding:10px; background:#dc3545; color:white; border:none; border-radius:5px; cursor:pointer;">
            ⚠️ Simulate Traffic Delay
        </button>
    </div>
</div>
