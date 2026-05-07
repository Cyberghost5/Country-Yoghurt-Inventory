<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deliveries - Country Yoghurt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">
        @include('partials._sidebar')
      </aside>
      <main class="main-content">
        <header class="topbar">
          <div class="title-block">
            <h2>Deliveries</h2>
            <p>{{ $user->role === 'admin' ? 'All delivery runs.' : 'Deliveries you have scheduled.' }}</p>
          </div>
          @if (in_array($user->role, ['admin', 'staff'], true))
            <div class="top-actions">
              <a href="{{ route('deliveries.create') }}" class="primary-btn">
                <i class="bi bi-plus-lg"></i> New Delivery Run
              </a>
            </div>
          @endif
        </header>

        @if (session('status'))
          <div class="lp-success" style="margin-bottom: 14px;">
            <i class="bi bi-check-circle"></i> {{ session('status') }}
          </div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom: 14px;">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
          </div>
        @endif

        <div class="ord-tabs" style="margin-bottom: 16px;">
          @php $active = request('status', ''); @endphp
          <a href="{{ route('deliveries.index') }}" class="ord-tab {{ $active === '' ? 'active' : '' }}">
            All <span class="ord-tab-count">{{ $counts['all'] }}</span>
          </a>
          <a href="{{ route('deliveries.index', ['status' => 'pending']) }}"
             class="ord-tab {{ $active === 'pending' ? 'active' : '' }}">
            Pending <span class="ord-tab-count">{{ $counts['pending'] }}</span>
          </a>
          <a href="{{ route('deliveries.index', ['status' => 'dispatched']) }}"
             class="ord-tab {{ $active === 'dispatched' ? 'active' : '' }}">
            Dispatched <span class="ord-tab-count approved">{{ $counts['dispatched'] }}</span>
          </a>
          <a href="{{ route('deliveries.index', ['status' => 'completed']) }}"
             class="ord-tab {{ $active === 'completed' ? 'active' : '' }}">
            Completed <span class="ord-tab-count delivered">{{ $counts['completed'] }}</span>
          </a>
        </div>

        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>Delivery #</th>
                  @if ($user->role === 'admin')<th>Staff</th>@endif
                  <th>Date</th>
                  <th>Customers</th>
                  <th>Total Value (&#8358;)</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse ($deliveries as $delivery)
                  <tr>
                    <td><span class="ord-number">{{ $delivery->delivery_number }}</span></td>
                    @if ($user->role === 'admin')
                      <td>{{ $delivery->staff->name ?? '-' }}</td>
                    @endif
                    <td>{{ $delivery->scheduled_at ? $delivery->scheduled_at->format('d M Y') : $delivery->created_at->format('d M Y') }}</td>
                    <td>{{ $delivery->allocations->count() }}</td>
                    <td class="ord-amount">{{ number_format($delivery->totalAmount(), 2) }}</td>
                    <td>
                      <span class="status-badge {{ $delivery->status_css }}">{{ $delivery->status_label }}</span>
                    </td>
                    <td>
                      <a href="{{ route('deliveries.show', $delivery) }}" class="ghost-btn" style="padding:4px 10px; font-size:0.8rem;">
                        <i class="bi bi-eye"></i> View
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="{{ $user->role === 'admin' ? 7 : 6 }}" style="text-align:center; padding:32px; color:var(--text-soft);">
                      <i class="bi bi-truck" style="font-size:1.5rem; display:block; margin-bottom:8px;"></i>
                      No deliveries found.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if ($deliveries->hasPages())
            <div style="padding: 12px 16px;">{{ $deliveries->withQueryString()->links() }}</div>
          @endif
        </section>
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
