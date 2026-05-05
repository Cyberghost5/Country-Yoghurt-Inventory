<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Debts - Country Yoghurt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
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
        <header class="topbar">
          <div class="title-block">
            <h2>Debts</h2>
            <p>Approved &amp; delivered orders with outstanding balances.</p>
          </div>
        </header>

        {{-- Summary stat --}}
        <div class="kpi-grid" style="margin-bottom: 20px;">
          <div class="stat-card" style="cursor:default;">
            <div class="stat-top">
              <span class="mini-icon" style="background:#fff3cd;color:#b45309;"><i class="bi bi-exclamation-circle"></i></span>
            </div>
            <h4 class="stat-value">₦{{ number_format($totalOutstanding, 2) }}</h4>
            <small class="stat-label">Total Outstanding</small>
          </div>
          <div class="stat-card" style="cursor:default;">
            <div class="stat-top">
              <span class="mini-icon" style="background:#fde8e8;color:#c0392b;"><i class="bi bi-receipt"></i></span>
            </div>
            <h4 class="stat-value">{{ $debtRows->count() }}</h4>
            <small class="stat-label">Unpaid Orders</small>
          </div>
          <div class="stat-card" style="cursor:default;">
            <div class="stat-top">
              <span class="mini-icon" style="background:#e8f5e9;color:#2d6a4f;"><i class="bi bi-people"></i></span>
            </div>
            <h4 class="stat-value">{{ $debtRows->pluck('customer_id')->unique()->count() }}</h4>
            <small class="stat-label">Customers with Debt</small>
          </div>
        </div>

        <section class="card table-card">
          <div class="card-head">
            <div>
              <h3>Outstanding Payments</h3>
              <span>{{ $debtRows->count() }} {{ Str::plural('order', $debtRows->count()) }} with unpaid balance</span>
            </div>
          </div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Shop</th>
                  <th>State</th>
                  <th>Phone</th>
                  <th>Order Total</th>
                  <th>Paid</th>
                  <th>Outstanding</th>
                  <th>Status</th>
                  <th>Order Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($debtRows as $row)
                  <tr>
                    <td><strong>{{ $row->order_number }}</strong></td>
                    <td>{{ $row->customer_name }}</td>
                    <td>{{ $row->shop_name ?: '-' }}</td>
                    <td>{{ $row->state ?: '-' }}</td>
                    <td>{{ $row->phone ?: '-' }}</td>
                    <td>₦{{ number_format($row->total_amount, 2) }}</td>
                    <td>₦{{ number_format($row->paid_amount, 2) }}</td>
                    <td>
                      <strong style="color: #c0392b;">₦{{ number_format($row->outstanding, 2) }}</strong>
                    </td>
                    <td>
                      @if($row->order_status === 'delivered')
                        <span class="badge-pill" style="background:#e8f5e9;color:#2d6a4f;padding:4px 8px;border-radius:12px;">Delivered</span>
                      @else
                        <span class="badge-pill" style="background:#fff3cd;color:#b45309;padding:4px 8px;border-radius:12px;">Approved</span>
                      @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($row->order_date)->format('d M Y') }}</td>
                    <td class="user-actions">
                      <a href="{{ route('orders.show', $row->order_id) }}" class="ua-btn ua-edit">
                        <i class="bi bi-eye"></i> View Order
                      </a>
                      <a href="{{ route('customers.show', $row->customer_id) }}" class="ua-btn" style="background:#f0f4ff;color:#2563eb;">
                        <i class="bi bi-person"></i> Customer
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="11" class="table-empty" style="text-align:center;padding:32px;">
                    <i class="bi bi-check-circle" style="font-size:1.6rem;color:#2d6a4f;display:block;margin-bottom:8px;"></i>
                    No outstanding debts. All approved orders are fully paid.
                  </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
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
