@extends('admin.layout')

@section('title', 'Edit Offer')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-pencil"></i> Edit Offer</h1>
            <p>Update offer information</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.offers.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Offers
            </a>
        </div>

        <div class="admin-section">
            <form method="POST" action="{{ route('admin.offers.update', $offer) }}" class="admin-form">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Title *</label>
                        <input type="text" name="title" value="{{ old('title', $offer->title) }}" required placeholder="Offer title...">
                        @error('title')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Offer description...">{{ old('description', $offer->description) }}</textarea>
                        @error('description')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Discount Percentage (%)</label>
                        <input type="number" name="discount_percentage" value="{{ old('discount_percentage', $offer->discount_percentage) }}" min="0" max="100" step="0.01">
                        <small>Enter 0 for no discount</small>
                        @error('discount_percentage')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Valid From *</label>
                        <input type="date" name="valid_from" value="{{ old('valid_from', $offer->valid_from->format('Y-m-d')) }}" required>
                        @error('valid_from')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Valid Until *</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until', $offer->valid_until->format('Y-m-d')) }}" required>
                        @error('valid_until')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $offer->sort_order) }}" min="0">
                        <small>Lower numbers appear first</small>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $offer->is_active) ? 'checked' : '' }}>
                            Active (Show on website)
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle"></i> Update Offer
                    </button>
                    <a href="{{ route('admin.offers.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .admin-form { max-width: 800px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input[type="text"], .form-group input[type="number"], .form-group input[type="date"], .form-group textarea, .form-group select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;
        }
        .form-group small { display: block; margin-top: 5px; color: #6c757d; font-size: 0.875rem; }
        .error { color: #dc3545; font-size: 0.875rem; margin-top: 5px; display: block; }
        .form-actions { margin-top: 30px; display: flex; gap: 10px; }
        .btn-primary { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; }
        .btn-secondary:hover { background: #5a6268; color: white; }
    </style>
@endsection

