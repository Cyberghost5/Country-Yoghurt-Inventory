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

          <section class="kpi-grid" style="margin-bottom: 16px;">
            <article class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-person-badge"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['staffCount'] }}</h4>
              <small class="stat-label">Total Staff</small>
            </article>

            <article class="stat-card info">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-shop"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['customerCount'] }}</h4>
              <small class="stat-label">Total Customers</small>
            </article>

            <article class="stat-card warn">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-geo-alt"></i></span>
              </div>
              <h4 class="stat-value">{{ $adminStats['stateCount'] }}</h4>
              <small class="stat-label">Active States</small>
            </article>

            <article class="stat-card">
              <div class="stat-top">
                <span class="mini-icon"><i class="bi bi-bag"></i></span>
              </div>
              <h4 class="stat-value">
                <a href="{{ route('orders.index') }}" class="stat-link">View</a>
              </h4>
              <small class="stat-label">Orders</small>
            </article>
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
                      <tr>
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
                    <tr><th>Name</th><th>Shop</th><th>Joined</th></tr>
                  </thead>
                  <tbody>
                    @forelse($recentCustomers as $row)
                      <tr>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->shop_name ?: '-' }}</td>
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
          <section class="kpi-grid" style="margin-top: 16px;">
            <a href="{{ route('orders.index') }}" class="stat-card stat-card-link">
              <div class="stat-top"><span class="mini-icon"><i class="bi bi-bag"></i></span></div>
              <h4 class="stat-value" style="font-size:1.1rem;">My Orders</h4>
            </a>
            <a href="{{ route('orders.create') }}" class="stat-card stat-card-link">
              <div class="stat-top"><span class="mini-icon"><i class="bi bi-plus-circle"></i></span></div>
              <h4 class="stat-value" style="font-size:1.1rem;">Place Order</h4>
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
    </script>
  </body>
</html>
