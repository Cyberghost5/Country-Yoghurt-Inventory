{{--
  Shared sidebar partial.
  Requires: $user  (Illuminate\Foundation\Auth\User)
  Active links are detected automatically from the current route.
--}}
@php
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

$_u = auth()->user();

// Orders pending
$_pendingOrders = match($_u->role) {
    'admin'    => Order::where('status', 'pending')->count(),
    'staff'    => Order::whereIn('user_id',
                      User::where('role', 'customer')->where('state', $_u->state)->pluck('id')
                  )->where('status', 'pending')->count(),
    'customer' => Order::where('user_id', $_u->id)->where('status', 'pending')->count(),
    default    => 0,
};

// Payments pending
$_pendingPayments = match($_u->role) {
    'admin'    => Payment::where('status', 'pending')->count(),
    'staff'    => Payment::whereIn('user_id',
                      User::where('role', 'customer')->where('state', $_u->state)->pluck('id')
                  )->where('status', 'pending')->count(),
    'customer' => Payment::where('user_id', $_u->id)->where('status', 'pending')->count(),
    default    => 0,
};

// Deliveries pending (admin/staff only)
$_pendingDeliveries = match($_u->role) {
    'admin', 'super_admin' => Delivery::where('status', 'pending')->count(),
    'staff'    => Delivery::where('staff_id', $_u->id)->where('status', 'pending')->count(),
    'customer' => Delivery::whereHas('allocations', fn ($q) => $q->where('customer_id', $_u->id))->where('status', 'dispatched')->count(),
    default    => 0,
};
@endphp
<button class="sidebar-close" id="sidebarClose" aria-label="Close navigation">
  <i class="bi bi-x-lg"></i>
</button>

<div class="brand-block">
  <img src="{{ asset('assets/img/logo.png') }}" alt="Country Yoghurt logo"
       style="height: 48px; width: 48px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);" />
  <div>
    <h1>Country Yoghurt</h1>
    <p>Inventory System</p>
  </div>
</div>

{{-- -- Main Menu ----------------------------------- --}}
<p class="menu-label">Main Menu</p>
<nav class="nav-links">
  <a href="{{ route('dashboard') }}"
     class="nav-link nav-link-anchor {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="bi bi-grid-1x2 nav-icon"></i>Dashboard
  </a>

  @if ($user->isAdmin())
    <a href="{{ route('admin.inventory.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
      <i class="bi bi-box-seam nav-icon"></i>Products
    </a>
  @endif

  <a href="{{ route('orders.index') }}"
     class="nav-link nav-link-anchor {{ request()->routeIs('orders.*') ? 'active' : '' }}">
    <i class="bi bi-bag nav-icon"></i>Orders
    @if ($_pendingOrders > 0)
      <span class="notif-nav-badge">{{ $_pendingOrders > 99 ? '99+' : $_pendingOrders }}</span>
    @endif
  </a>

  @if ($user->isAdminOrStaff())
    <a href="{{ route('deliveries.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('deliveries.*') ? 'active' : '' }}">
      <i class="bi bi-truck nav-icon"></i>Deliveries
      @if ($_pendingDeliveries > 0)
        <span class="notif-nav-badge">{{ $_pendingDeliveries > 99 ? '99+' : $_pendingDeliveries }}</span>
      @endif
    </a>
  @endif

  @if ($user->role === 'customer')
    <a href="{{ route('deliveries.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('deliveries.*') ? 'active' : '' }}">
      <i class="bi bi-truck nav-icon"></i>Deliveries
      @if ($_pendingDeliveries > 0)
        <span class="notif-nav-badge">{{ $_pendingDeliveries > 99 ? '99+' : $_pendingDeliveries }}</span>
      @endif
    </a>
    <a href="{{ route('bank_accounts.show') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('bank_accounts.*') ? 'active' : '' }}">
      <i class="bi bi-bank nav-icon"></i>Payment Accounts
    </a>
  @endif

  @if ($user->role === 'staff')
    <a href="{{ route('bank_accounts.show') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('bank_accounts.*') ? 'active' : '' }}">
      <i class="bi bi-bank nav-icon"></i>Payment Accounts
    </a>
  @endif

  <a href="{{ route('payments.index') }}"
     class="nav-link nav-link-anchor {{ request()->routeIs('payments.*') ? 'active' : '' }}">
    <i class="bi bi-credit-card nav-icon"></i>Payments
    @if ($_pendingPayments > 0)
      <span class="notif-nav-badge">{{ $_pendingPayments > 99 ? '99+' : $_pendingPayments }}</span>
    @endif
  </a>

  <a href="{{ route('transactions.index') }}"
     class="nav-link nav-link-anchor {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
    <i class="bi bi-clock-history nav-icon"></i>Transactions
  </a>

  @if ($user->isAdmin())
    <a href="{{ route('admin.reports.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
      <i class="bi bi-bar-chart-line nav-icon"></i>Reports
    </a>
    <a href="{{ route('admin.debts.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.debts.*') ? 'active' : '' }}">
      <i class="bi bi-exclamation-circle nav-icon"></i>Debts
    </a>
    <a href="{{ route('admin.sms.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.sms.*') ? 'active' : '' }}">
      <i class="bi bi-chat-dots nav-icon"></i>SMS Broadcast
    </a>
    <a href="{{ route('admin.bank_accounts.index') }}"
       class="nav-link nav-link-anchor {{ request()->routeIs('admin.bank_accounts.*') ? 'active' : '' }}">
      <i class="bi bi-bank nav-icon"></i>Bank Accounts
    </a>
  @endif

  @php $notifCount = auth()->user()?->unreadNotifications()->count() ?? 0; @endphp
  <a href="{{ route('notifications.index') }}"
     class="nav-link nav-link-anchor {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
    <i class="bi bi-bell nav-icon"></i>Notifications
    @if ($notifCount > 0)
      <span class="notif-nav-badge">{{ $notifCount > 99 ? '99+' : $notifCount }}</span>
    @endif
  </a>
</nav>

{{-- -- People --------------------------------------- --}}
@if ($user->isAdminOrStaff())
  <p class="menu-label" style="margin-top: 18px;">People</p>
  <nav class="nav-links">

    @if ($user->isAdmin())
      @php $adminMenuActive = request()->routeIs('admin.admins.index') || request()->routeIs('users.create.admin'); @endphp
      <button type="button"
              class="nav-link nav-dropdown-toggle {{ $adminMenuActive ? 'active' : '' }}"
              data-target="dd-admins"
              aria-expanded="{{ $adminMenuActive ? 'true' : 'false' }}">
        <i class="bi bi-shield nav-icon"></i>
        Admins
        <i class="bi bi-chevron-down nav-chevron"></i>
      </button>
      <div class="nav-dropdown {{ $adminMenuActive ? 'nav-dropdown-open' : '' }}" id="dd-admins">
        <div>
          <a href="{{ route('admin.admins.index') }}"
             class="nav-link nav-link-anchor nav-sub {{ request()->routeIs('admin.admins.index') ? 'active' : '' }}">
            <i class="bi bi-shield nav-icon"></i>View Admins
          </a>
          @if ($user->role === 'super_admin')
          <a href="{{ route('users.create.admin') }}"
             class="nav-link nav-link-anchor nav-sub {{ request()->routeIs('users.create.admin') ? 'active' : '' }}">
            <i class="bi bi-shield-plus nav-icon"></i>Add Admin
          </a>
          <a href="{{ route('users.create.super_admin') }}"
             class="nav-link nav-link-anchor nav-sub {{ request()->routeIs('users.create.super_admin') ? 'active' : '' }}">
            <i class="bi bi-shield-lock nav-icon"></i>Add Super Admin
          </a>
          @endif
        </div>
      </div>

      @php $staffActive = request()->routeIs('admin.staff.index') || request()->routeIs('users.create.staff'); @endphp
      <button type="button"
              class="nav-link nav-dropdown-toggle {{ $staffActive ? 'active' : '' }}"
              data-target="dd-staff"
              aria-expanded="{{ $staffActive ? 'true' : 'false' }}">
        <i class="bi bi-people nav-icon"></i>
        Staff
        <i class="bi bi-chevron-down nav-chevron"></i>
      </button>
      <div class="nav-dropdown {{ $staffActive ? 'nav-dropdown-open' : '' }}" id="dd-staff">
        <div>
          <a href="{{ route('admin.staff.index') }}"
             class="nav-link nav-link-anchor nav-sub {{ request()->routeIs('admin.staff.index') ? 'active' : '' }}">
            <i class="bi bi-people nav-icon"></i>View Staff
          </a>
          @if ($user->role === 'super_admin')
          <a href="{{ route('users.create.staff') }}"
             class="nav-link nav-link-anchor nav-sub {{ request()->routeIs('users.create.staff') ? 'active' : '' }}">
            <i class="bi bi-person-plus nav-icon"></i>Add Staff
          </a>
          @endif
        </div>
      </div>
    @endif

    @php
      $custActive = request()->routeIs('admin.customers.index')
                 || request()->routeIs('staff.customers.index')
                 || request()->routeIs('customers.show')
                 || request()->routeIs('users.create.customer');
    @endphp
    <button type="button"
            class="nav-link nav-dropdown-toggle {{ $custActive ? 'active' : '' }}"
            data-target="dd-customers"
            aria-expanded="{{ $custActive ? 'true' : 'false' }}">
      <i class="bi bi-shop nav-icon"></i>
      Customers
      <i class="bi bi-chevron-down nav-chevron"></i>
    </button>
    <div class="nav-dropdown {{ $custActive ? 'nav-dropdown-open' : '' }}" id="dd-customers">
      <div>
        @if ($user->isAdmin())
          <a href="{{ route('admin.customers.index') }}"
             class="nav-link nav-link-anchor nav-sub {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">
            <i class="bi bi-shop nav-icon"></i>View Customers
          </a>
        @elseif ($user->role === 'staff')
          <a href="{{ route('staff.customers.index') }}"
             class="nav-link nav-link-anchor nav-sub {{ request()->routeIs('staff.customers.index') || request()->routeIs('customers.show') ? 'active' : '' }}">
            <i class="bi bi-shop nav-icon"></i>View Customers
          </a>
        @endif
        <a href="{{ route('users.create.customer') }}"
           class="nav-link nav-link-anchor nav-sub {{ request()->routeIs('users.create.customer') ? 'active' : '' }}">
          <i class="bi bi-person-plus nav-icon"></i>Add Customer
        </a>
      </div>
    </div>

  </nav>
@endif

{{-- -- Footer ---------------------------------------- --}}
<div class="sidebar-footer">
  <div class="user-avatar">
    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strrchr($user->name, ' ') ?: $user->name, 1, 1)) }}
  </div>
  <div class="user-meta">
    <p>{{ $user->name }}</p>
    <small>{{ ucfirst($user->role) }}</small>
  </div>
  <form method="POST" action="{{ route('logout') }}" class="logout-form">
    @csrf
    <button type="submit" class="logout-btn" title="Sign out">
      <i class="bi bi-box-arrow-right"></i>
    </button>
  </form>
</div>
<p style="text-align:center; font-size:0.7rem; color:var(--text-soft, #a09880); letter-spacing:0.04em; margin:10px 0 4px; opacity:0.7;">
  Powered by <strong style="font-weight:600; color:var(--text-soft, #a09880);"><a href="https://zeetechfoundation.org" target="_blank" rel="noopener noreferrer" style="color:inherit; text-decoration:none;">Zee Tech Ventures</a></strong>
</p>

<script>
  document.querySelectorAll('.nav-dropdown-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var targetId = this.getAttribute('data-target');
      var panel    = document.getElementById(targetId);
      var isOpen   = panel.classList.contains('nav-dropdown-open');
      panel.classList.toggle('nav-dropdown-open', !isOpen);
      this.setAttribute('aria-expanded', String(!isOpen));
    });
  });
</script>
