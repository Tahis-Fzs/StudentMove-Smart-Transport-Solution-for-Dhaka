@extends('admin.layout')

@section('title', 'User Details')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-person"></i> User Details</h1>
            <p>View user information</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.users') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-edit">
                <i class="bi bi-pencil"></i> Edit User
            </a>
        </div>

        <div class="admin-section">
            <div class="user-detail-card">
                <div class="detail-header">
                    <h2>{{ $user->name }}</h2>
                    <span class="user-id">ID: {{ $user->id }}</span>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Email</label>
                        <p>{{ $user->email }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Phone</label>
                        <p>{{ $user->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="detail-item">
                        <label>University</label>
                        <p>{{ $user->university ?? 'N/A' }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Student ID</label>
                        <p>{{ $user->student_id ?? 'N/A' }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Department</label>
                        <p>{{ $user->department ?? 'N/A' }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Year of Study</label>
                        <p>{{ $user->year_of_study ?? 'N/A' }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Date of Birth</label>
                        <p>{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Current Address</label>
                        <p>{{ $user->current_address ?? 'N/A' }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Home Address</label>
                        <p>{{ $user->home_address ?? 'N/A' }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Preferred Language</label>
                        <p>{{ $user->preferred_language ?? 'N/A' }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Registered</label>
                        <p>{{ $user->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="detail-item">
                        <label>Last Updated</label>
                        <p>{{ $user->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection