@extends('admin.layout')

@section('title', 'Edit Notification')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-pencil"></i> Edit Notification</h1>
            <p>Update notification information</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.notifications.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Notifications
            </a>
        </div>

        <div class="admin-section">
            <form method="POST" action="{{ route('admin.notifications.update', $notification) }}" class="admin-form">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Message *</label>
                        <textarea name="message" rows="3" required>{{ old('message', $notification->message) }}</textarea>
                        @error('message')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Icon</label>
                        <input type="text" name="icon" value="{{ old('icon', $notification->icon) }}" placeholder="bi-bell">
                        @error('icon')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Icon Color</label>
                        <select name="icon_color">
                            <option value="blue" {{ old('icon_color', $notification->icon_color) == 'blue' ? 'selected' : '' }}>Blue</option>
                            <option value="green" {{ old('icon_color', $notification->icon_color) == 'green' ? 'selected' : '' }}>Green</option>
                            <option value="red" {{ old('icon_color', $notification->icon_color) == 'red' ? 'selected' : '' }}>Red</option>
                            <option value="orange" {{ old('icon_color', $notification->icon_color) == 'orange' ? 'selected' : '' }}>Orange</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Type</label>
                        <select name="type">
                            <option value="info" {{ old('type', $notification->type) == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="success" {{ old('type', $notification->type) == 'success' ? 'selected' : '' }}>Success</option>
                            <option value="warning" {{ old('type', $notification->type) == 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="error" {{ old('type', $notification->type) == 'error' ? 'selected' : '' }}>Error</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $notification->sort_order) }}" min="0">
                    </div>

                    <div class="form-group full-width">
                        <label>Link to Offer (Optional)</label>
                        <select name="offer_id">
                            <option value="">-- No Offer Link --</option>
                            @foreach($offers as $offer)
                                <option value="{{ $offer->id }}" {{ old('offer_id', $notification->offer_id) == $offer->id ? 'selected' : '' }}>
                                    {{ $offer->title }} @if($offer->discount_percentage > 0) - {{ $offer->discount_percentage }}% OFF @endif
                                </option>
                            @endforeach
                        </select>
                        <small>If selected, offer details will be displayed with the notification</small>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $notification->is_active) ? 'checked' : '' }}>
                            Active (Show on website)
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle"></i> Update Notification
                    </button>
                    <a href="{{ route('admin.notifications.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

