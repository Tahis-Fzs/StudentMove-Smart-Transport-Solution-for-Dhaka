@extends('admin.layout')

@section('title', 'Create Announcement')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-plus-circle"></i> Create Announcement</h1>
            <p>Publish alerts with audience targeting and schedule windows</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.notifications.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Notifications
            </a>
        </div>

        <div class="admin-section">
            <form method="POST" action="{{ route('admin.notifications.store') }}" class="admin-form" id="announcementForm">
                @csrf

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Title (optional)</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. DIU morning shuttle delay">
                    </div>

                    <div class="form-group full-width">
                        <label>Message *</label>
                        <textarea name="message" rows="3" required placeholder="Announcement message...">{{ old('message') }}</textarea>
                        @error('message')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Audience *</label>
                        <select name="audience" id="audienceSelect">
                            <option value="all" @selected(old('audience', 'all') === 'all')>Everyone</option>
                            <option value="university" @selected(old('audience') === 'university')>University</option>
                            <option value="department" @selected(old('audience') === 'department')>Department</option>
                            <option value="route" @selected(old('audience') === 'route')>Route / stop</option>
                        </select>
                    </div>

                    <div class="form-group" id="targetValueGroup">
                        <label>Target value *</label>
                        <input type="text" name="target_value" id="targetValueInput" list="targetSuggestions"
                               value="{{ old('target_value') }}" placeholder="e.g. DIU, CSE, Uttara">
                        <datalist id="targetSuggestions"></datalist>
                        <small id="targetHint">Shown only to matching students.</small>
                        @error('target_value')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Publish at</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at') }}">
                        <small>Leave blank to publish immediately</small>
                    </div>

                    <div class="form-group">
                        <label>Expires at</label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}">
                        <small>Leave blank for no expiry</small>
                        @error('expires_at')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Icon (Bootstrap Icon class)</label>
                        <input type="text" name="icon" value="{{ old('icon', 'bi-bell') }}" placeholder="bi-bell">
                        <small>Examples: bi-bell, bi-bus-front, bi-check-circle, bi-gift</small>
                        @error('icon')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Icon Color</label>
                        <select name="icon_color">
                            <option value="blue" {{ old('icon_color', 'blue') == 'blue' ? 'selected' : '' }}>Blue</option>
                            <option value="green" {{ old('icon_color') == 'green' ? 'selected' : '' }}>Green</option>
                            <option value="red" {{ old('icon_color') == 'red' ? 'selected' : '' }}>Red</option>
                            <option value="orange" {{ old('icon_color') == 'orange' ? 'selected' : '' }}>Orange</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Type</label>
                        <select name="type">
                            <option value="info" {{ old('type', 'info') == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success</option>
                            <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="error" {{ old('type') == 'error' ? 'selected' : '' }}>Error</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                        <small>Lower numbers appear first</small>
                    </div>

                    <div class="form-group full-width">
                        <label>Link to Offer (Optional)</label>
                        <select name="offer_id">
                            <option value="">-- No Offer Link --</option>
                            @foreach($offers as $offer)
                                <option value="{{ $offer->id }}" {{ old('offer_id') == $offer->id ? 'selected' : '' }}>
                                    {{ $offer->title }} @if($offer->discount_percentage > 0) - {{ $offer->discount_percentage }}% OFF @endif
                                </option>
                            @endforeach
                        </select>
                        <small>If selected, offer details will be displayed with the notification</small>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            Active (Show on website)
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle"></i> Create Announcement
                    </button>
                    <a href="{{ route('admin.notifications.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @include('admin.notifications._targeting_script')
@endsection
