<x-app-layout>
    <style>
        .admin-users-page {
            width: 100% !important;
            max-width: 100% !important;
            padding: 30px;
            box-sizing: border-box;
            overflow-x: visible;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .admin-users-page h2 {
            color: #1a202c !important;
            margin-bottom: 10px;
            font-size: 2rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-users-page h2 i {
            color: #007bff;
            margin-right: 10px;
        }
        .users-table-wrapper {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12), 0 4px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .users-table-container {
            overflow-x: auto;
            width: 100%;
            -webkit-overflow-scrolling: touch;
            display: block;
            border-radius: 12px;
        }
        .users-table-container table {
            width: 100%;
            min-width: 800px;
            display: table;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        .users-table-container thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .users-table-container thead th {
            color: white !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 18px 15px !important;
            border: none;
        }
        .users-table-container tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #e5e7eb;
        }
        .users-table-container tbody tr:hover {
            background: #f8f9ff;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .users-table-container td,
        .users-table-container th {
            vertical-align: middle;
            min-width: 100px;
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            max-width: none !important;
        }
        .admin-users-page table td {
            padding: 18px 15px !important;
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            max-width: none !important;
            width: auto !important;
            color: #374151;
            font-size: 0.95rem;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .status-banned {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        .search-form-wrapper {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .search-input {
            padding: 12px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 400px;
        }
        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .search-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .action-btn-ban {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .action-btn-ban:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .action-btn-edit {
            color: #667eea;
            font-size: 1.3rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .action-btn-edit:hover {
            color: #764ba2;
            transform: scale(1.2);
        }
    </style>
    <div class="admin-users-page">
        <h2><i class="bi bi-people-fill"></i> User Management</h2>

        <div class="search-form-wrapper">
            <form method="GET" action="{{ route('admin.users.search') }}" style="display: flex; gap: 12px; align-items: center;">
                <input type="text" name="q" class="search-input" placeholder="Search by name, email or ID..." value="{{ $query ?? '' }}">
                <button type="submit" class="search-btn">
                    <i class="bi bi-search"></i> Search
                </button>
            </form>
        </div>

        @if(session('success'))
            <div style="background: #d4edda; padding: 10px; margin-bottom: 20px; color: #155724;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background: #f8d7da; padding: 10px; margin-bottom: 20px; color: #721c24;">
                {{ session('error') }}
            </div>
        @endif

        <div class="users-table-wrapper">
        <div class="users-table-container">
        <table>
            <thead style="background: #343a40; color: white;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Name</th>
                    <th style="padding: 15px; text-align: left;">Email</th>
                    <th style="padding: 15px; text-align: left;">Role</th>
                    <th style="padding: 15px; text-align: left;">Status</th>
                    <th style="padding: 15px; text-align: left;">Subscription</th>
                    <th style="padding: 15px; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px; word-wrap: break-word; word-break: break-word;">{{ $user->name }}</td>
                    <td style="padding: 15px; word-wrap: break-word; word-break: break-all;">{{ $user->email }}</td>
                    <td style="padding: 15px;">{{ $user->role ?? 'Student' }}</td>
                    <td>
                        @if($user->is_banned)
                            <span class="status-badge status-banned">Banned</span>
                        @else
                            <span class="status-badge status-active">Active</span>
                        @endif
                    </td>
                    <td style="padding: 15px; word-wrap: break-word;">
                        @php $sub = \App\Models\Subscription::where('user_id', $user->id)->where('status', 'active')->first(); @endphp
                        @if($sub)
                            <span style="color: green; display: block; margin-bottom: 5px;">{{ $sub->plan_name }}</span>
                            <form method="POST" action="{{ route('admin.users.cancel_sub', $user->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" style="font-size: 0.85rem; color: red; border: none; background: none; text-decoration: underline; cursor: pointer;">(Cancel)</button>
                            </form>
                        @else
                            <span style="color: #999;">None</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <form method="POST" action="{{ route('admin.users.ban', $user->id) }}" style="display:inline-block; margin: 0;">
                                @csrf
                                <button type="submit" class="action-btn-ban" style="background: {{ $user->is_banned ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' }}; color: white;">
                                    <i class="bi bi-{{ $user->is_banned ? 'check-circle' : 'x-circle' }}"></i> {{ $user->is_banned ? 'Unban' : 'Ban' }}
                                </button>
                            </form>
                            
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn-edit" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #888;">
                        <i class="bi bi-inbox"></i> No users found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        </div>
        
        <div style="margin-top: 20px;">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>