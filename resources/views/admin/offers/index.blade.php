@extends('admin.layout')

@section('title', 'Manage Offers')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-tag"></i> Manage Offers</h1>
            <p>Create and manage promotional offers</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.offers.create') }}" class="action-btn">
                <i class="bi bi-plus-circle"></i> Create New Offer
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="admin-section">
            <div class="section-header">
                <h2>All Offers ({{ $offers->total() }})</h2>
            </div>
            <div class="users-table">
                @if($offers->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Discount</th>
                            <th>Valid From</th>
                            <th>Valid Until</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($offers as $offer)
                        <tr>
                            <td>{{ $offer->id }}</td>
                            <td><strong>{{ $offer->title }}</strong>
                                @if($offer->description)
                                    <br><small class="text-muted">{{ Str::limit($offer->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($offer->discount_percentage > 0)
                                    <span class="badge bg-success">{{ $offer->discount_percentage }}% OFF</span>
                                @else
                                    <span class="badge bg-secondary">No Discount</span>
                                @endif
                            </td>
                            <td>{{ $offer->valid_from->format('M d, Y') }}</td>
                            <td>{{ $offer->valid_until->format('M d, Y') }}</td>
                            <td>
                                @if($offer->is_active && $offer->valid_from <= now() && $offer->valid_until >= now())
                                    <span class="badge bg-success">Active</span>
                                @elseif(!$offer->is_active)
                                    <span class="badge bg-secondary">Inactive</span>
                                @else
                                    <span class="badge bg-warning">Expired</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons-inline">
                                    <a href="{{ route('admin.offers.edit', $offer) }}" class="btn-sm btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this offer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm btn-delete" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="pagination-wrapper">
                    {{ $offers->links() }}
                </div>
                @else
                <div class="no-data">
                    <i class="bi bi-inbox"></i>
                    <p>No offers found. <a href="{{ route('admin.offers.create') }}">Create your first offer</a></p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .action-buttons-inline { display: flex; gap: 5px; }
        .btn-sm { padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-edit { background: #007bff; color: white; }
        .btn-edit:hover { background: #0056b3; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; color: white; }
        .inline-form { display: inline; margin: 0; }
        .pagination-wrapper { margin-top: 20px; }
        .no-data { text-align: center; padding: 40px; color: #6c757d; }
        .no-data i { font-size: 3rem; margin-bottom: 10px; display: block; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.875rem; }
    </style>
@endsection

