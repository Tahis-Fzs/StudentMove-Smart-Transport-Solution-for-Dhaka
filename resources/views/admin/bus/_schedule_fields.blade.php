@php
    $selectedDays = old('run_days', $bus->run_days ?? \App\Models\BusSchedule::DAY_KEYS);
    $tagsValue = old('university_tags', isset($bus) ? implode(', ', $bus->universityTags()) : '');
@endphp

<div class="form-group">
    <label>Departure location</label>
    <input name="departure_location" value="{{ old('departure_location', $bus->departure_location ?? '') }}" placeholder="e.g. Uttara" style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
</div>
<div class="form-group">
    <label>Arrival location</label>
    <input name="arrival_location" value="{{ old('arrival_location', $bus->arrival_location ?? '') }}" placeholder="e.g. DSC" style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
</div>

<div class="form-group">
    <label>Run days</label>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        @foreach(\App\Models\BusSchedule::DAY_KEYS as $day)
            <label style="display:flex; align-items:center; gap:6px; font-weight:500;">
                <input type="checkbox" name="run_days[]" value="{{ $day }}" {{ in_array($day, (array) $selectedDays, true) ? 'checked' : '' }}>
                {{ strtoupper($day) }}
            </label>
        @endforeach
    </div>
</div>

<div class="form-group">
    <label>Whiteboard / schedule note</label>
    <textarea name="schedule_note" rows="3" maxlength="1000" placeholder="Driver note shown on the live map card" style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">{{ old('schedule_note', $bus->schedule_note ?? '') }}</textarea>
</div>

<div class="form-group">
    <label>University tags</label>
    <input name="university_tags" value="{{ $tagsValue }}" placeholder="DIU, DSC, NSU (comma-separated)" style="padding:10px; border:1px solid #ddd; width:100%; border-radius:5px;">
</div>

@if(isset($bus))
<div class="form-group">
    <label style="display:flex; align-items:center; gap:8px;">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $bus->is_active ?? true) ? 'checked' : '' }}>
        Active route
    </label>
</div>
@endif
