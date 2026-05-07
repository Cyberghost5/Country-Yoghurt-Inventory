<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Orders - Country Yoghurt</title>
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

      {{-- ─── Sidebar ─────────────────────────────────── --}}
      <aside class="sidebar" id="sidebar">
        @include('partials._sidebar')
      </aside>

      {{-- ─── Main content ──────────────────────────────── --}}
      <main class="main-content">

        {{-- Top bar --}}
        <header class="topbar">
          <div class="title-block">
            <h2>Orders</h2>
            <p>
              @if ($user->isAdmin())
                All orders placed by staff and customers.
              @else
                Your order history and status.
              @endif
            </p>
          </div>
          <div class="top-actions">
            @if (in_array($user->role, ['staff', 'customer'], true))
              <a href="{{ route('orders.create') }}" class="primary-btn">
                <i class="bi bi-plus-lg"></i> Place Order
              </a>
            @endif
          </div>
        </header>

        {{-- Alerts --}}
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

        {{-- Status filter tabs --}}
        <div class="ord-tabs">
          <a href="{{ route('orders.index') }}"
             class="ord-tab {{ !request('status') ? 'active' : '' }}">
            All <span class="ord-tab-count">{{ $counts['all'] }}</span>
          </a>
          <a href="{{ route('orders.index', ['status' => 'pending']) }}"
             class="ord-tab {{ request('status') === 'pending' ? 'active' : '' }}">
            Pending <span class="ord-tab-count pending">{{ $counts['pending'] }}</span>
          </a>
          <a href="{{ route('orders.index', ['status' => 'approved']) }}"
             class="ord-tab {{ request('status') === 'approved' ? 'active' : '' }}">
            Approved <span class="ord-tab-count approved">{{ $counts['approved'] }}</span>
          </a>
          <a href="{{ route('orders.index', ['status' => 'delivered']) }}"
             class="ord-tab {{ request('status') === 'delivered' ? 'active' : '' }}">
            Delivered <span class="ord-tab-count delivered">{{ $counts['delivered'] }}</span>
          </a>
          <a href="{{ route('orders.index', ['status' => 'rejected']) }}"
             class="ord-tab {{ request('status') === 'rejected' ? 'active' : '' }}">
            Rejected <span class="ord-tab-count rejected">{{ $counts['rejected'] }}</span>
          </a>
        </div>

        {{-- Orders table --}}
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table ord-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  @if ($user->isAdmin())<th>Placed By</th>@endif
                  <th>Date</th>
                  <th>Items</th>
                  <th>Total (&#8358;)</th>
                  <th>Remaining (&#8358;)</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($orders as $order)
                  <tr>
                    <td>
                      <span class="ord-number">{{ $order->order_number }}</span>
                    </td>
                    @if ($user->isAdmin())
                      <td>
                        <span class="ord-placer">{{ $order->user->name ?? '-' }}</span>
                        <small class="ord-role">{{ ucfirst($order->user->role ?? '') }}</small>
                      </td>
                    @endif
                    <td>{{ $order->created_at->format('d M Y, g:ia') }}</td>
                    <td>{{ $order->items->count() }}</td>
                    <td class="ord-amount">{{ number_format($order->total_amount, 2) }}</td>
                    <td class="ord-amount">
                      @php $rem = max(0, (float)$order->total_amount - $order->payments->sum('amount')); @endphp
                      @if ($rem <= 0)
                        <span style="color:#16a34a; font-weight:600;">Paid</span>
                      @else
                        <span style="color:#dc2626; font-weight:600;">{{ number_format($rem, 2) }}</span>
                      @endif
                    </td>
                    <td>
                      <span class="ord-status-badge {{ $order->status_css }}">
                        {{ $order->status_label }}
                      </span>
                    </td>
                    <td>
                      <a href="{{ route('orders.show', $order) }}" class="inv-action-btn" title="View">
                        <i class="bi bi-eye"></i>
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                      <td colspan="{{ $user->isAdmin() ? 8 : 7 }}" class="inv-empty-row">
                      <i class="bi bi-inbox" style="font-size:1.4rem;"></i>
                      <p>No orders found.</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if ($orders->hasPages())
            <div class="ord-pagination">
              {{ $orders->links() }}
            </div>
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
