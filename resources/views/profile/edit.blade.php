<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/notification.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile-settings.css') }}">
    @endpush

    <div class="profile-container">
        <div class="profile-header">
            <h1 class="profile-title"><i class="bi bi-person-circle"></i> Profile Settings</h1>
            <p class="profile-subtitle">
                @if($completingProfile ?? false)
                    Finish your student profile to unlock bookings, chat, and your dashboard.
                @else
                    Update your personal information and preferences
                @endif
            </p>

            @if($completingProfile ?? false)
                <div class="alert alert-success" style="background:#ecfdf5;color:#047857;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #a7f3d0;">
                    <i class="bi bi-info-circle"></i>
                    <strong>Almost there.</strong> Add your student ID, Bangladesh mobile number, and university to complete your account.
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-success" style="background:#ecfdf5;color:#047857;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #a7f3d0;">
                    {{ session('info') }}
                </div>
            @endif
            
            @if(session('status') == 'profile-updated')
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="bi bi-check-circle"></i> Profile updated successfully!
                </div>
            @endif
        </div>

        <form id="profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('patch')
            
            <!-- Profile Picture Section -->
            <div class="avatar-upload">
                <img src="{{ Auth::user()->avatarUrl() }}"
                     alt="Profile Picture" class="profile-avatar" id="profile-avatar"
                     onerror="this.onerror=null;this.src='{{ Auth::user()->avatarFallbackUrl() }}';">
                <button type="button" class="upload-btn" onclick="document.getElementById('avatar-input').click()">
                    <i class="bi bi-camera"></i> Change Photo
                </button>
                <input type="file" id="avatar-input" name="profile_image" accept="image/jpeg,image/png,image/jpg,image/gif" style="display: none;" onchange="previewImage(this)">
                @error('profile_image')
                    <div class="error-message" style="margin-top: 8px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Personal Information -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="bi bi-person"></i> Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-input" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        @error('first_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-input" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        @error('last_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-input" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Mobile Number</label>
                    <input type="tel" class="form-input" name="phone" value="{{ old('phone', $user->phone) }}" required>
                    @error('phone')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" class="form-input" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}">
                    @error('date_of_birth')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Academic Information -->
            @php
                $uniNames = $universities->pluck('name')->all();
                $matchUni = $user->university;
                if ($matchUni && !in_array($matchUni, $uniNames, true)) {
                    $byShort = $universities->first(fn ($u) => strcasecmp((string) $u->short_name, $matchUni) === 0);
                    if ($byShort) {
                        $matchUni = $byShort->name;
                    }
                }
                $savedUni = old('university_select', in_array($matchUni, $uniNames, true) ? $matchUni : ($user->university ? '__other__' : ''));
                $savedFac = old('faculty_select', $faculties->contains($user->faculty) ? $user->faculty : ($user->faculty ? '__other__' : ''));
                $savedDept = old('department_select', $departments->contains($user->department) ? $user->department : ($user->department ? '__other__' : ''));
                $uniMeta = $universities->mapWithKeys(fn ($u) => [$u->name => $u->calendar_type])->all();
            @endphp
            <div class="form-section" id="academic-section"
                 data-calendars='@json($uniMeta)'
                 data-saved-system="{{ old('semester_system', $user->semester_system) }}"
                 data-saved-year="{{ old('year_of_study', $user->year_of_study) }}"
                 data-saved-semester="{{ old('semester', $user->semester) }}">
                <h3 class="form-section-title"><i class="bi bi-mortarboard"></i> Academic Information</h3>

                <div class="form-group">
                    <label class="form-label">University</label>
                    <select class="form-input" name="university_select" id="university_select">
                        <option value="">Select university</option>
                        @foreach($universities as $uni)
                            <option value="{{ $uni->name }}" @selected($savedUni === $uni->name)>
                                {{ $uni->short_name ? $uni->short_name.' — ' : '' }}{{ $uni->name }}
                            </option>
                        @endforeach
                        <option value="__other__" @selected($savedUni === '__other__')>Other (add manually)</option>
                    </select>
                    <input type="text" class="form-input academic-other" name="university_other" id="university_other"
                           placeholder="Type your university name"
                           value="{{ old('university_other', $savedUni === '__other__' ? $user->university : '') }}"
                           style="{{ $savedUni === '__other__' ? '' : 'display:none;margin-top:8px;' }}">
                    @error('university_other')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Faculty</label>
                        <select class="form-input" name="faculty_select" id="faculty_select">
                            <option value="">Select faculty</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty }}" @selected($savedFac === $faculty)>{{ $faculty }}</option>
                            @endforeach
                            <option value="__other__" @selected($savedFac === '__other__')>Other (add manually)</option>
                        </select>
                        <input type="text" class="form-input academic-other" name="faculty_other" id="faculty_other"
                               placeholder="Type your faculty name"
                               value="{{ old('faculty_other', $savedFac === '__other__' ? $user->faculty : '') }}"
                               style="{{ $savedFac === '__other__' ? '' : 'display:none;margin-top:8px;' }}">
                        @error('faculty_other')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <select class="form-input" name="department_select" id="department_select">
                            <option value="">Select department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department }}" @selected($savedDept === $department)>{{ $department }}</option>
                            @endforeach
                            <option value="__other__" @selected($savedDept === '__other__')>Other (add manually)</option>
                        </select>
                        <input type="text" class="form-input academic-other" name="department_other" id="department_other"
                               placeholder="Type your department name"
                               value="{{ old('department_other', $savedDept === '__other__' ? $user->department : '') }}"
                               style="{{ $savedDept === '__other__' ? '' : 'display:none;margin-top:8px;' }}">
                        @error('department_other')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Student ID</label>
                        <input type="text" class="form-input" name="student_id" value="{{ old('student_id', $user->student_id) }}">
                        @error('student_id')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" id="semester-system-wrap" style="display:none;">
                        <label class="form-label">Semester system</label>
                        <select class="form-input" name="semester_system" id="semester_system">
                            <option value="bi">Bi-mester (2 / year)</option>
                            <option value="tri">Tri-mester (3 / year)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Year of Study</label>
                        <select class="form-input" name="year_of_study" id="year_of_study"></select>
                        @error('year_of_study')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" id="semester-label">Semester</label>
                        <select class="form-input" name="semester" id="semester"></select>
                        @error('semester')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <p class="academic-hint" id="academic-hint">Pick a university to load bi-mester or tri-mester options.</p>
            </div>

            <!-- Address Information -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="bi bi-geo-alt"></i> Address Information</h3>
                
                <div class="form-group">
                    <label class="form-label">Current Address</label>
                    <input type="text" class="form-input" name="current_address" value="{{ old('current_address', $user->current_address) }}">
                    @error('current_address')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Home Address</label>
                    <input type="text" class="form-input" name="home_address" value="{{ old('home_address', $user->home_address) }}">
                    @error('home_address')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Preferences -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="bi bi-gear"></i> Preferences</h3>
                
                <div class="form-group">
                    <label class="form-label">Preferred Language</label>
                    <select class="form-input" name="preferred_language">
                        <option value="en" {{ old('preferred_language', $user->preferred_language) == 'en' ? 'selected' : '' }}>English</option>
                        <option value="bn" {{ old('preferred_language', $user->preferred_language) == 'bn' ? 'selected' : '' }}>বাংলা</option>
                    </select>
                    @error('preferred_language')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Notification Settings</label>
                    <div style="margin-top: 8px;">
                        <label style="display: flex; align-items: center; margin-bottom: 8px;">
                            <input type="checkbox" name="bus_delay_notifications" value="1" 
                                   {{ old('bus_delay_notifications', $user->bus_delay_notifications) ? 'checked' : '' }} 
                                   style="margin-right: 8px;"> Bus delay notifications
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 8px;">
                            <input type="checkbox" name="route_change_alerts" value="1" 
                                   {{ old('route_change_alerts', $user->route_change_alerts) ? 'checked' : '' }} 
                                   style="margin-right: 8px;"> Route change alerts
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 8px;">
                            <input type="checkbox" name="promotional_offers" value="1" 
                                   {{ old('promotional_offers', $user->promotional_offers) ? 'checked' : '' }} 
                                   style="margin-right: 8px;"> Promotional offers
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <button type="submit" class="save-btn">
                <i class="bi bi-check-circle"></i>
                @if($completingProfile ?? false)
                    Complete profile &amp; continue
                @else
                    Save Changes
                @endif
            </button>
            @unless($completingProfile ?? false)
            <button type="button" class="cancel-btn" onclick="window.location.href='{{ route('dashboard') }}'">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </button>
            @endunless
        </form>
    </div>

    @push('scripts')
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Check file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, or GIF).');
                    input.value = ''; // Clear the input
                    return;
                }
                
                // Preview the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-avatar').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        (function academicFields() {
            const section = document.getElementById('academic-section');
            if (!section) return;

            const calendars = JSON.parse(section.dataset.calendars || '{}');
            const uniSelect = document.getElementById('university_select');
            const uniOther = document.getElementById('university_other');
            const facSelect = document.getElementById('faculty_select');
            const facOther = document.getElementById('faculty_other');
            const deptSelect = document.getElementById('department_select');
            const deptOther = document.getElementById('department_other');
            const systemWrap = document.getElementById('semester-system-wrap');
            const systemSelect = document.getElementById('semester_system');
            const yearSelect = document.getElementById('year_of_study');
            const semesterSelect = document.getElementById('semester');
            const semesterLabel = document.getElementById('semester-label');
            const hint = document.getElementById('academic-hint');

            const yearLabels = { 1: '1st Year', 2: '2nd Year', 3: '3rd Year', 4: '4th Year' };
            const savedYear = section.dataset.savedYear || '';
            const savedSemester = section.dataset.savedSemester || '';
            const savedSystem = section.dataset.savedSystem || '';

            function toggleOther(selectEl, otherEl) {
                const isOther = selectEl.value === '__other__';
                otherEl.style.display = isOther ? 'block' : 'none';
                if (isOther) {
                    otherEl.style.marginTop = '8px';
                    otherEl.required = true;
                } else {
                    otherEl.required = false;
                }
            }

            function fillSelect(select, options, selected) {
                select.innerHTML = '';
                options.forEach(([value, label]) => {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = label;
                    if (String(value) === String(selected)) opt.selected = true;
                    select.appendChild(opt);
                });
            }

            function activeSystem() {
                const uni = uniSelect.value;
                if (!uni || uni === '__other__') {
                    return systemSelect.value || 'bi';
                }
                const type = calendars[uni] || 'bi';
                if (type === 'both') return systemSelect.value || 'bi';
                return type;
            }

            function refreshSemesterUI() {
                const uni = uniSelect.value;
                let type = (!uni || uni === '__other__') ? 'bi' : (calendars[uni] || 'bi');

                if (type === 'both' || !uni || uni === '__other__') {
                    systemWrap.style.display = '';
                    if (savedSystem) systemSelect.value = savedSystem;
                    type = systemSelect.value || 'bi';
                } else {
                    systemWrap.style.display = 'none';
                    systemSelect.value = type;
                }

                fillSelect(yearSelect, Object.entries(yearLabels), savedYear || '1');

                if (type === 'tri') {
                    semesterLabel.textContent = 'Tri-mester';
                    fillSelect(semesterSelect, [
                        ['1', 'Term 1'],
                        ['2', 'Term 2'],
                        ['3', 'Term 3'],
                    ], savedSemester || '1');
                    hint.textContent = 'This university uses a tri-mester calendar (3 terms per year).';
                } else {
                    semesterLabel.textContent = 'Bi-mester';
                    fillSelect(semesterSelect, [
                        ['1', 'Semester 1'],
                        ['2', 'Semester 2'],
                    ], savedSemester || '1');
                    hint.textContent = 'This university uses a bi-mester calendar (2 semesters per year).';
                }
            }

            uniSelect.addEventListener('change', () => {
                toggleOther(uniSelect, uniOther);
                refreshSemesterUI();
            });
            facSelect.addEventListener('change', () => toggleOther(facSelect, facOther));
            deptSelect.addEventListener('change', () => toggleOther(deptSelect, deptOther));
            systemSelect.addEventListener('change', refreshSemesterUI);

            toggleOther(uniSelect, uniOther);
            toggleOther(facSelect, facOther);
            toggleOther(deptSelect, deptOther);
            refreshSemesterUI();
        })();

        // Form validation
        document.getElementById('profile-form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('input[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                // Skip file inputs in validation check
                if (field.type === 'file') {
                    return;
                }
                
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '#e9ecef';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
            }
            
            // Allow form to submit normally (including file upload)
            return true;
        });
    </script>
    @endpush
</x-app-layout>