@extends('admin.layout')

@section('title', 'Edit User')

@section('content')
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="bi bi-pencil"></i> Edit User</h1>
            <p>Update user information</p>
        </div>

        <div class="admin-toolbar">
            <a href="{{ route('admin.users.show', $user) }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to User
            </a>
        </div>

        <div class="admin-section">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-form">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        @error('first_name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        @error('last_name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>University</label>
                        <input type="text" name="university" value="{{ old('university', $user->university) }}">
                        @error('university')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}">
                        @error('student_id')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department" value="{{ old('department', $user->department) }}">
                        @error('department')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Year of Study</label>
                        <input type="text" name="year_of_study" value="{{ old('year_of_study', $user->year_of_study) }}">
                        @error('year_of_study')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}">
                        @error('date_of_birth')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Preferred Language</label>
                        <select name="preferred_language">
                            <option value="">Select Language</option>
                            <option value="en" {{ old('preferred_language', $user->preferred_language) == 'en' ? 'selected' : '' }}>English</option>
                            <option value="bn" {{ old('preferred_language', $user->preferred_language) == 'bn' ? 'selected' : '' }}>Bengali</option>
                        </select>
                        @error('preferred_language')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label>Current Address</label>
                        <textarea name="current_address" rows="3">{{ old('current_address', $user->current_address) }}</textarea>
                        @error('current_address')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label>Home Address</label>
                        <textarea name="home_address" rows="3">{{ old('home_address', $user->home_address) }}</textarea>
                        @error('home_address')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle"></i> Update User
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection