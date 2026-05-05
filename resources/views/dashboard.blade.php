<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - Country Yoghurt Inventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">
        @include('partials._sidebar')
      </aside>

      <main class="main-content">

        {{-- ── Page header ────────────────────────────── --}}
        <header class="dash-header">
          <div>
            <h2 class="dash-title">Dashboard</h2>
            <p class="dash-sub">{{ now()->format('l, d F Y') }} &middot; {{ ucfirst($user->role) }}</p>
          </div>
        </header>

        {{-- ── Admin overview ──────────────────────────── --}}
        @if ($user->role === 'admin' && $adminStats)

          {{-- Date filter bar --}}
          @php
            $rangeLabels = [
              'all'                  => 'All Time',
              'today'                => 'Today',
              'yesterday'            => 'Yesterday',
              'last_7'               => 'Last 7 Days',
              'last_30'              => 'Last 30 Days',
              'this_month'           => 'This Month',
              'last_month'           => 'Last Month',
              'this_month_last_year' => 'This Month Last Year',
              'this_year'            => 'This Year',
              'last_year'            => 'Last Year',
              'current_fy'           => 'Current Financial Year',
              'last_fy'              => 'Last Financial Year',
              'custom'               => 'Custom Range',
            ];
          @endphp
          <form method="GET" action="{{ route('dashboard') }}" id="dashFilterForm" class="dash-filter-bar">
            <div class="dash-filter-left">
              <label class="dash-filter-label">
                <i class="bi bi-funnel"></i> Filter
              </label>
              <select name="range" class="filter-select" id="dashRangeSelect"
                      onchange="dashRangeChanged(this.value)">
                @foreach ($rangeLabels as $key => $label)
                  <option value="{{ $key }}" {{ $range === $key ? 'selected' : '' }}>
                    {{ $label }}
                  </option>
                @endforeach
              </select>
              <span id="dashCustomInputs" class="dash-custom-range"
                    style="{{ $range === 'custom' ? '' : 'display:none;' }}">
                <input type="date" name="from" class="filter-select"
                       value="{{ $fromInput ?? '' }}" />
                <span class="dash-filter-to">to</span>
                <input type="date" name="to" class="filter-select"
                       value="{{ $toInput ?? '' }}" />
                <button type="submit" class="ghost-btn">Apply</button>
              </span>
            </div>
            @if ($dateStart && $dateEnd)
              <span class="dash-filter-period">
                <i class="bi bi-calendar3"></i>
                {{ $dateStart->format('d M Y') }} &ndash; {{ $dateEnd->format('d M Y') }}
              </span>
            @elseif ($range === 'all')
              <span class="dash-filter-period">
                <i class="bi bi-infinity"></i> No date filter applied
              </span>
            @endif
          </form>

          {{-- Row 1: Users --}}
          <section class="kpi-grid" style="margin-bottom: 12px;">            
            <a href="{{ route('orders.index') }}" class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-bag-check"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['totalOrders'] }}</h4>
              <small class="stat-label">Total Orders
                @if($adminStats['pendingOrders'] > 0)
                  &nbsp;<span class="badge-warn">{{ $adminStats['pendingOrders'] }} pending</span>
                @endif
              </small>
            </a>

            <a href="{{ route('orders.index') }}" class="stat-card success">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-bag-check-fill"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['approvedOrders'] }}</h4>
              <small class="stat-label">Approved Orders</small>
            </a>

            <a href="{{ route('payments.index') }}" class="stat-card info">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-cash-stack"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['totalPayments'] }}</h4>
              <small class="stat-label">Total Payments
                @if($adminStats['pendingPayments'] > 0)
                  &nbsp;<span class="badge-warn">{{ $adminStats['pendingPayments'] }} pending</span>
                @endif
              </small>
            </a>

            <a href="{{ route('transactions.index') }}" class="stat-card success">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-currency-exchange"></i></span>
              </div>
              <h4 class="stat-value">&#8358;{{ number_format($adminStats['totalRevenue'], 2) }}</h4>
              <small class="stat-label">Total Revenue</small>
            </a>

            <a href="{{ route('admin.debts.index') }}" class="stat-card danger">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-exclamation-circle"></i></span>
              </div>
              <h4 class="stat-value">&#8358;{{ number_format($adminStats['totalDebt'], 2) }}</h4>
              <small class="stat-label">Total Debt (Unpaid)</small>
            </a>

            <a href="{{ route('admin.staff.index') }}" class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-person-badge"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['staffCount'] }}</h4>
              <small class="stat-label">Total Staff</small>
            </a>

            <a href="{{ route('admin.customers.index') }}" class="stat-card info">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-shop"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['customerCount'] }}</h4>
              <small class="stat-label">Total Customers</small>
            </a>

            <a href="{{ route('admin.staff.index') }}" class="stat-card warn">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-geo-alt"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['stateCount'] }}</h4>
              <small class="stat-label">Active States</small>
            </a>

            <a href="{{ route('admin.inventory.index') }}" class="stat-card info">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-box-seam"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['totalProducts'] }}</h4>
              <small class="stat-label">Products
                @if($adminStats['lowStock'] > 0 || $adminStats['outOfStock'] > 0)
                  &nbsp;<span class="badge-warn">{{ $adminStats['lowStock'] + $adminStats['outOfStock'] }} alert{{ ($adminStats['lowStock'] + $adminStats['outOfStock']) > 1 ? 's' : '' }}</span>
                @endif
              </small>
            </a>
            
            <a href="{{ route('deliveries.index') }}" class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-truck"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['totalDeliveries'] }}</h4>
              <small class="stat-label">Total Deliveries
                @if($adminStats['pendingDeliveries'] > 0)
                  &nbsp;<span class="badge-warn">{{ $adminStats['pendingDeliveries'] }} pending</span>
                @endif
              </small>
            </a>

            <a href="{{ route('deliveries.index') }}" class="stat-card success">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-truck-front-fill"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['completedDeliveries'] }}</h4>
              <small class="stat-label">Completed Deliveries</small>
            </a>

            <a href="{{ route('admin.inventory.index') }}" class="stat-card warn">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-exclamation-triangle"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['lowStock'] }}</h4>
              <small class="stat-label">Low Stock Items</small>
            </a>

            <a href="{{ route('admin.inventory.index') }}" class="stat-card danger">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-x-circle"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['outOfStock'] }}</h4>
              <small class="stat-label">Out of Stock</small>
            </a>

            <a href="{{ route('admin.sms.index') }}" class="stat-card sms-balance-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-wallet2"></i></span>
              </div>
              <h4 class="stat-value sms-balance-stat-value">
                {{ $smsBalance ? $smsBalance['formatted'] : '—' }}
              </h4>
              <small class="stat-label">SMS Wallet Balance</small>
            </a>
          </section>

          <section class="middle-grid">

            <article class="card table-card">
              <div class="card-head">
                <div>
                  <h3>Recent Staff</h3>
                  <span>Latest 5 added</span>
                </div>
                <a href="{{ route('admin.staff.index') }}" class="ghost-btn">View All</a>
              </div>
              <div class="table-scroll">
                <table class="dash-table">
                  <thead>
                    <tr><th>Name</th><th>State</th><th>Joined</th></tr>
                  </thead>
                  <tbody>
                    @forelse($recentStaff as $row)
                      <tr class="clickable-row" onclick="window.location='{{ route('users.edit', $row->id) }}'" style="cursor:pointer;">
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->state ?: '-' }}</td>
                        <td>{{ optional($row->created_at)->format('d M Y') }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="3" class="table-empty">No staff records yet.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </article>

            <article class="card table-card">
              <div class="card-head">
                <div>
                  <h3>Recent Customers</h3>
                  <span>Latest 5 added</span>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="ghost-btn">View All</a>
              </div>
              <div class="table-scroll">
                <table class="dash-table">
                  <thead>
                    <tr><th>Name</th><th>Shop</th><th>State</th><th>Joined</th></tr>
                  </thead>
                  <tbody>
                    @forelse($recentCustomers as $row)
                      <tr class="clickable-row" onclick="window.location='{{ route('customers.show', $row->id) }}'" style="cursor:pointer;">
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->shop_name ?: '-' }}</td>
                        <td>{{ $row->state ?: '-' }}</td>
                        <td>{{ optional($row->created_at)->format('d M Y') }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="3" class="table-empty">No customers yet.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </article>

          </section>

        @endif

        {{-- ── Staff analytics ──────────────────────────── --}}
        @if ($user->role === 'staff' && $staffStats)

          {{-- Row 1: Orders in state --}}
          <section class="kpi-grid" style="margin-bottom: 12px;">
            <a href="{{ route('orders.index') }}" class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-bag-check"></i></span>
              </div>
              <h4 class="stat-value">{{ $staffStats['stateOrders'] }}</h4>
              <small class="stat-label">Orders in {{ $user->state ?? 'State' }}
                @if($staffStats['pendingOrders'] > 0)
                  &nbsp;<span class="badge-warn">{{ $staffStats['pendingOrders'] }} pending</span>
                @endif
              </small>
            </a>

            <a href="{{ route('payments.index') }}" class="stat-card info">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-cash-stack"></i></span>
              </div>
              <h4 class="stat-value">{{ $staffStats['statePayments'] }}</h4>
              <small class="stat-label">Payments in State
                @if($staffStats['statePendingPayments'] > 0)
                  &nbsp;<span class="badge-warn">{{ $staffStats['statePendingPayments'] }} pending</span>
                @endif
              </small>
            </a>

            <a href="{{ route('payments.index') }}" class="stat-card success">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-currency-exchange"></i></span>
              </div>
              <h4 class="stat-value">&#8358;{{ number_format($staffStats['stateRevenue'], 2) }}</h4>
              <small class="stat-label">Revenue from State</small>
            </a>

            <a href="{{ route('orders.index') }}" class="stat-card danger">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-exclamation-circle"></i></span>
              </div>
              <h4 class="stat-value">&#8358;{{ number_format($staffStats['stateDebt'], 2) }}</h4>
              <small class="stat-label">Debt ({{ $user->state ?? 'State' }})</small>
            </a>

            <a href="{{ route('deliveries.index') }}" class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-truck"></i></span>
              </div>
              <h4 class="stat-value">{{ $staffStats['myDeliveries'] }}</h4>
              <small class="stat-label">My Deliveries
                @if($staffStats['myPendingDeliveries'] > 0)
                  &nbsp;<span class="badge-warn">{{ $staffStats['myPendingDeliveries'] }} pending</span>
                @endif
                @if($staffStats['myActiveDeliveries'] > 0)
                  &nbsp;<span class="badge-info">{{ $staffStats['myActiveDeliveries'] }} active</span>
                @endif
              </small>
            </a>
          </section>

        @endif

        {{-- ── Customer analytics ───────────────────────── --}}
        @if ($user->role === 'customer' && $customerStats)

          <section class="kpi-grid" style="margin-bottom: 12px;">
            <a href="{{ route('orders.index') }}" class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-bag"></i></span>
              </div>
              <h4 class="stat-value">{{ $customerStats['totalOrders'] }}</h4>
              <small class="stat-label">My Orders
                @if($customerStats['pendingOrders'] > 0)
                  &nbsp;<span class="badge-warn">{{ $customerStats['pendingOrders'] }} pending</span>
                @endif
              </small>
            </a>

            <a href="{{ route('orders.index') }}" class="stat-card success">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-bag-check-fill"></i></span>
              </div>
              <h4 class="stat-value">{{ $customerStats['approvedOrders'] }}</h4>
              <small class="stat-label">Approved Orders</small>
            </a>

            <a href="{{ route('payments.index') }}" class="stat-card info">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-cash-stack"></i></span>
              </div>
              <h4 class="stat-value">&#8358;{{ number_format($customerStats['totalPaid'], 2) }}</h4>
              <small class="stat-label">Total Paid
                @if($customerStats['pendingPayments'] > 0)
                  &nbsp;<span class="badge-warn">{{ $customerStats['pendingPayments'] }} pending</span>
                @endif
              </small>
            </a>

            <a href="{{ route('deliveries.index') }}" class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-truck"></i></span>
              </div>
              <h4 class="stat-value">{{ $customerStats['totalDeliveries'] }}</h4>
              <small class="stat-label">My Deliveries
                @if($customerStats['pendingDeliveries'] > 0)
                  &nbsp;<span class="badge-warn">{{ $customerStats['pendingDeliveries'] }} pending</span>
                @endif
              </small>
            </a>

            <a href="{{ route('payments.create') }}" class="stat-card danger">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-exclamation-circle"></i></span>
              </div>
              <h4 class="stat-value">&#8358;{{ number_format($customerStats['myDebt'], 2) }}</h4>
              <small class="stat-label">Outstanding Balance</small>
            </a>
          </section>

        @endif

        {{-- ── People in state (staff / customer view) ─── --}}
        @if (in_array($user->role, ['staff', 'customer'], true))
          <section class="card state-card">
            <div class="card-head">
              <div>
                <h3>People in {{ $user->state ?: 'your state' }}</h3>
                <span>Staff &amp; customers in your region</span>
              </div>
            </div>
            @if ($stateContacts->isEmpty())
              <p class="state-empty">No other contacts found in your state yet.</p>
            @else
              <ul class="state-list">
                @foreach ($stateContacts as $contact)
                  <li>
                    <div>
                      <p>{{ $contact->name }}</p>
                      <small>{{ ucfirst($contact->role) }} · {{ $contact->lga }}, {{ $contact->state }}</small>
                    </div>
                    <span>{{ $contact->phone ?: '-' }}</span>
                  </li>
                @endforeach
              </ul>
            @endif
          </section>
        @endif

        {{-- ── Quick links (customer only) ────────────── --}}
        @if ($user->role === 'customer')
          <section class="kpi-grid" style="margin-top: 8px;">
            <a href="{{ route('orders.index') }}" class="stat-card stat-card-link">
              <div class="stat-top"><span class="mini-icon"><i class="bi bi-bag"></i></span></div>
              <h4 class="stat-value" style="font-size:1.1rem;">My Orders</h4>
            </a>
            <a href="{{ route('orders.create') }}" class="stat-card stat-card-link">
              <div class="stat-top"><span class="mini-icon"><i class="bi bi-plus-circle"></i></span></div>
              <h4 class="stat-value" style="font-size:1.1rem;">Place Order</h4>
            </a>
            <a href="{{ route('payments.index') }}" class="stat-card stat-card-link">
              <div class="stat-top"><span class="mini-icon"><i class="bi bi-cash"></i></span></div>
              <h4 class="stat-value" style="font-size:1.1rem;">Payments</h4>
            </a>
            <a href="{{ route('deliveries.index') }}" class="stat-card stat-card-link">
              <div class="stat-top"><span class="mini-icon"><i class="bi bi-truck"></i></span></div>
              <h4 class="stat-value" style="font-size:1.1rem;">Deliveries</h4>
            </a>
          </section>
        @endif

      </main>
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <script>
      (function() {
        var sidebar  = document.getElementById('sidebar');
        var backdrop = document.getElementById('sidebarBackdrop');
        var toggle   = document.getElementById('sidebarToggle');
        var close    = document.getElementById('sidebarClose');
        function openSidebar()  { sidebar.classList.add('is-open'); backdrop.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
        function closeSidebar() { sidebar.classList.remove('is-open'); backdrop.classList.remove('is-open'); document.body.style.overflow = ''; }
        if (toggle)   toggle.addEventListener('click', openSidebar);
        if (close)    close.addEventListener('click', closeSidebar);
        if (backdrop) backdrop.addEventListener('click', closeSidebar);
      })();

      function dashRangeChanged(val) {
        var ci = document.getElementById('dashCustomInputs');
        if (val === 'custom') {
          ci.style.display = '';
        } else {
          ci.style.display = 'none';
          document.getElementById('dashFilterForm').submit();
        }
      }
    </script>
  </body>
</html>
