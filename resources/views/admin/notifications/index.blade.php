@extends('admin.layout')

@section('title', 'Manage Notifications')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-bell"></i> Manage Notifications</h1>
            <p>Create and manage user notifications</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.notifications.create') }}" class="action-btn">
                <i class="bi bi-plus-circle"></i> Create New Notification
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="admin-section">
            <div class="section-header">
                <h2>All Notifications ({{ $notifications->total() }})</h2>
            </div>
            <div class="users-table">
                @if($notifications->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                        <tr>
                            <td>{{ $notification->id }}</td>
                            <td><strong>{{ Str::limit($notification->message, 60) }}</strong></td>
                            <td>
                                <span class="badge badge-{{ $notification->type }}">
                                    {{ ucfirst($notification->type) }}
                                </span>
                            </td>
                            <td>
                                @if($notification->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge" style="background: #6c757d; color: white;">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $notification->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-buttons-inline">
                                    <a href="{{ route('admin.notifications.edit', $notification) }}" class="btn-sm btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this notification?');">
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
                    {{ $notifications->links() }}
                </div>
                @else
                <div class="no-data">
                    <i class="bi bi-inbox"></i>
                    <p>No notifications found. <a href="{{ route('admin.notifications.create') }}">Create your first notification</a></p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

