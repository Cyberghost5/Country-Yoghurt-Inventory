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
            @if ($selectedState)
              <h2>Debts &mdash; {{ $selectedState }}</h2>
              <p>Outstanding balances for deliveries in <strong>{{ $selectedState }}</strong>.</p>
            @else
              <h2>Debts</h2>
              <p>Approved &amp; dispatched deliveries with outstanding balances, grouped by state.</p>
            @endif
          </div>
          @if ($selectedState)
          <div class="top-actions">
            <a href="{{ route('admin.debts.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> All States
            </a>
          </div>
          @endif
        </header>

        {{-- Summary KPIs --}}
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
            <h4 class="stat-value">{{ $debtByState->sum('delivery_count') }}</h4>
            <small class="stat-label">Unpaid Deliveries</small>
          </div>
          <div class="stat-card" style="cursor:default;">
            <div class="stat-top">
              <span class="mini-icon" style="background:#e8f5e9;color:#2d6a4f;"><i class="bi bi-people"></i></span>
            </div>
            <h4 class="stat-value">{{ $debtByState->sum('customer_count') }}</h4>
            <small class="stat-label">Customers with Debt</small>
          </div>
          <div class="stat-card" style="cursor:default;">
            <div class="stat-top">
              <span class="mini-icon" style="background:#e8f0fe;color:#3b5bdb;"><i class="bi bi-geo-alt"></i></span>
            </div>
            <h4 class="stat-value">{{ $debtByState->count() }}</h4>
            <small class="stat-label">States with Debt</small>
          </div>
        </div>

        @if ($selectedState)
          {{-- ── Delivery-level drill-down ── --}}
          <section class="card table-card">
            <div class="card-head">
              <div>
                <h3>Deliveries in {{ $selectedState }}</h3>
                <span>{{ $debtRows->count() }} {{ Str::plural('delivery', $debtRows->count()) }} with unpaid balance</span>
              </div>
            </div>
            <div class="table-scroll">
              <table>
                <thead>
                  <tr>
                    <th>Delivery #</th>
                    <th>Shop</th>
                    <th>Phone</th>
                    <th>Delivery Total</th>
                    <th>Paid</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($debtRows as $row)
                    <tr>
                      <td><strong>{{ $row->delivery_number }}</strong></td>
                      <td>{{ $row->shop_name ?: '-' }}</td>
                      <td>{{ $row->phone ?: '-' }}</td>
                      <td>₦{{ number_format($row->total_amount, 2) }}</td>
                      <td>₦{{ number_format($row->paid_amount, 2) }}</td>
                      <td><strong style="color:#c0392b;">₦{{ number_format($row->outstanding, 2) }}</strong></td>
                      <td>
                        @if($row->delivery_status === 'completed')
                          <span class="badge-pill" style="background:#e8f5e9;color:#2d6a4f;padding:4px 8px;border-radius:12px;">Completed</span>
                        @else
                          <span class="badge-pill" style="background:#fff3cd;color:#b45309;padding:4px 8px;border-radius:12px;">Dispatched</span>
                        @endif
                      </td>
                      <td>{{ \Carbon\Carbon::parse($row->delivery_date)->format('d M Y') }}</td>
                      <td class="user-actions">
                        <a href="{{ route('deliveries.show', $row->delivery_id) }}" class="ua-btn ua-edit">
                          <i class="bi bi-eye"></i> View Delivery
                        </a>
                        <a href="{{ route('customers.show', $row->customer_id) }}" class="ua-btn" style="background:#f0f4ff;color:#2563eb;">
                          <i class="bi bi-person"></i> Customer
                        </a>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="9" class="table-empty" style="text-align:center;padding:32px;">
                      <i class="bi bi-check-circle" style="font-size:1.6rem;color:#2d6a4f;display:block;margin-bottom:8px;"></i>
                      No outstanding delivery debts in {{ $selectedState }}.
                    </td></tr>
                  @endforelse
                </tbody>
                @if($debtRows->count() > 0)
                <tfoot>
                  <tr style="background:#fdf8f0;">
                    <td colspan="5" style="padding:8px 12px;font-weight:600;font-size:0.85rem;">Total for {{ $selectedState }}</td>
                    <td style="padding:8px 12px;font-weight:700;color:#c0392b;">₦{{ number_format($debtRows->sum('outstanding'), 2) }}</td>
                    <td colspan="3"></td>
                  </tr>
                </tfoot>
                @endif
              </table>
            </div>
          </section>

        @else
          {{-- ── State-level breakdown ── --}}
          <section class="card table-card">
            <div class="card-head">
              <div>
                <h3>Debt Breakdown by State</h3>
                <span>Click a state to view its individual deliveries</span>
              </div>
            </div>
            <div class="table-scroll">
              <table>
                <thead>
                  <tr>
                    <th>State</th>
                    <th style="text-align:right;">Deliveries</th>
                    <th style="text-align:right;">Customers</th>
                    <th style="text-align:right;">Outstanding</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($debtByState as $row)
                    <tr style="cursor:pointer;" onclick="window.location.href=this.querySelector('a.ua-btn').href">
                      <td>
                        <strong>{{ $row->state }}</strong>
                      </td>
                      <td style="text-align:right;">{{ $row->delivery_count }}</td>
                      <td style="text-align:right;">{{ $row->customer_count }}</td>
                      <td style="text-align:right;">
                        <strong style="color:#c0392b;">₦{{ number_format($row->total_debt, 2) }}</strong>
                      </td>
                      <td style="text-align:right;">
                        <a href="{{ route('admin.debts.index', ['state' => $row->state]) }}" class="ua-btn ua-edit" onclick="event.stopPropagation()">
                          <i class="bi bi-chevron-right"></i> View
                        </a>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="5" class="table-empty" style="text-align:center;padding:32px;">
                      <i class="bi bi-check-circle" style="font-size:1.6rem;color:#2d6a4f;display:block;margin-bottom:8px;"></i>
                      No outstanding delivery debts.
                    </td></tr>
                  @endforelse
                </tbody>
                @if($debtByState->count() > 0)
                <tfoot>
                  <tr style="background:#fdf8f0;">
                    <td style="padding:8px 12px;font-weight:600;font-size:0.85rem;">Total</td>
                    <td style="padding:8px 12px;font-weight:600;text-align:right;">{{ $debtByState->sum('delivery_count') }}</td>
                    <td style="padding:8px 12px;font-weight:600;text-align:right;">{{ $debtByState->sum('customer_count') }}</td>
                    <td style="padding:8px 12px;font-weight:700;color:#c0392b;text-align:right;">₦{{ number_format($totalOutstanding, 2) }}</td>
                    <td></td>
                  </tr>
                </tfoot>
                @endif
              </table>
            </div>
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
