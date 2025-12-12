<div class="sidebar p-3" style="width: 250px;">
    <h4 class="mb-4 text-center">Admin Panel</h4>
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
        <i class="bi bi-people"></i> Users
    </a>
    <a href="{{ route('admin.offers.index') }}" class="{{ request()->routeIs('admin.offers*') ? 'active' : '' }}">
        <i class="bi bi-tag"></i> Offers
    </a>
    <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
        <i class="bi bi-bell"></i> Notifications
    </a>
    <a href="{{ route('admin.buses.index') }}" class="{{ request()->routeIs('admin.buses*') || request()->routeIs('admin.bus*') ? 'active' : '' }}">
        <i class="bi bi-bus-front"></i> Buses
    </a>
    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
        <i class="bi bi-graph-up"></i> Reports
    </a>
    <a href="{{ route('admin.logs') }}" class="{{ request()->routeIs('admin.logs') ? 'active' : '' }}">
        <i class="bi bi-file-text"></i> Activity Logs
    </a>
    
    <form method="POST" action="{{ route('admin.logout') }}" class="mt-5">
        @csrf
        <button type="submit" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
    </form>
</div>
