<x-app-layout>
    <div style="padding: 30px; max-width: 1200px; margin: 0 auto;">
        <h2><i class="bi bi-people-fill"></i> User Management</h2>

        <form method="GET" action="{{ route('admin.users.search') }}" style="margin: 20px 0; display: flex; gap: 10px;">
            <input type="text" name="q" placeholder="Search by name, email or ID..." style="padding: 10px; border: 1px solid #ddd; width: 300px;" value="{{ $query ?? '' }}">
            <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none;">Search</button>
        </form>

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

        <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <thead style="background: #343a40; color: white;">
                <tr>
                    <th style="padding: 15px;">Name</th>
                    <th style="padding: 15px;">Email</th>
                    <th style="padding: 15px;">Role</th>
                    <th style="padding: 15px;">Status</th>
                    <th style="padding: 15px;">Subscription</th>
                    <th style="padding: 15px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;">{{ $user->name }}</td>
                    <td style="padding: 15px;">{{ $user->email }}</td>
                    <td style="padding: 15px;">{{ $user->role ?? 'Student' }}</td>
                    <td style="padding: 15px;">
                        @if($user->is_banned)
                            <span style="background: #dc3545; color: white; padding: 2px 8px; border-radius: 4px;">Banned</span>
                        @else
                            <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 4px;">Active</span>
                        @endif
                    </td>
                    <td style="padding: 15px;">
                        @php $sub = \App\Models\Subscription::where('user_id', $user->id)->where('status', 'active')->first(); @endphp
                        @if($sub)
                            <span style="color: green;">{{ $sub->plan_name }}</span>
                            <form method="POST" action="{{ route('admin.users.cancel_sub', $user->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" style="font-size: 0.8rem; color: red; border: none; background: none; text-decoration: underline; cursor: pointer;">(Cancel)</button>
                            </form>
                        @else
                            <span style="color: #999;">None</span>
                        @endif
                    </td>
                    <td style="padding: 15px;">
                        <form method="POST" action="{{ route('admin.users.ban', $user->id) }}" style="display:inline;">
                            @csrf
                            <button type="submit" style="padding: 5px 10px; background: {{ $user->is_banned ? '#28a745' : '#dc3545' }}; color: white; border: none; border-radius: 3px; cursor: pointer;">
                                {{ $user->is_banned ? 'Unban' : 'Ban' }}
                            </button>
                        </form>
                        
                        <a href="{{ route('admin.users.edit', $user->id) }}" style="margin-left: 5px; color: #007bff;"><i class="bi bi-pencil"></i></a>
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
        
        <div style="margin-top: 20px;">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>