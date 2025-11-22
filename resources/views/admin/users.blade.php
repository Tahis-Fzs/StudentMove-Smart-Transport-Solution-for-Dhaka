@extends('admin.layout')

@section('title', 'User Management')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-people"></i> User Management</h1>
            <p>Manage all registered users</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        <!-- Search and Actions -->
        <div class="admin-toolbar">
            <form method="GET" action="{{ route('admin.users.search') }}" class="search-form">
                <input type="text" name="q" placeholder="Search by name, email, phone..." value="{{ $query ?? '' }}" class="search-input">
                <button type="submit" class="search-btn"><i class="bi bi-search"></i></button>
            </form>
            <a href="{{ route('admin.dashboard') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Users Table -->
        <div class="admin-section">
            <div class="section-header">
                <h2>All Users ({{ $users->total() }})</h2>
            </div>
            <div class="users-table">
                @if($users->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>University</th>
                            <th>Student ID</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>{{ $user->university ?? 'N/A' }}</td>
                            <td>{{ $user->student_id ?? 'N/A' }}</td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-buttons-inline">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn-sm btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-sm btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this user?');">
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
                
                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $users->links() }}
                </div>
                @else
                <div class="no-data">
                    <i class="bi bi-inbox"></i>
                    <p>No users found.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection