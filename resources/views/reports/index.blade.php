<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reports - Country Yoghurt</title>
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

        {{-- ── Page header ─────────────────────────────────────── --}}
        <header class="topbar">
          <div class="title-block">
            <h2>Reports</h2>
            <p>Business overview &amp; analytics for Country Yoghurt.</p>
          </div>
        </header>

        {{-- ── Date filter ─────────────────────────────────────── --}}
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
        <form method="GET" action="{{ route('admin.reports.index') }}" id="reportFilterForm" class="dash-filter-bar">
          <div class="dash-filter-left">
            <label class="dash-filter-label">
              <i class="bi bi-funnel"></i> Period
            </label>
            <select name="range" class="filter-select" id="reportRangeSelect"
                    onchange="reportRangeChanged(this.value)">
              @foreach ($rangeLabels as $key => $label)
                <option value="{{ $key }}" {{ $range === $key ? 'selected' : '' }}>
                  {{ $label }}
                </option>
              @endforeach
            </select>
            <span id="reportCustomInputs" class="dash-custom-range"
                  style="{{ $range === 'custom' ? '' : 'display:none;' }}">
              <input type="date" name="from" class="filter-select"
                     value="{{ $fromInput ?? '' }}" />
              <span class="dash-filter-to">to</span>
              <input type="date" name="to" class="filter-select"
                     value="{{ $toInput ?? '' }}" />
              <button type="submit" class="ghost-btn">Apply</button>
            </span>
          </div>
          @php
            $periodLabel = $rangeLabels[$range] ?? 'Custom Range';
            if ($dateStart && $dateEnd) {
              $periodLabel .= ': ' . $dateStart->format('d M Y') . ' – ' . $dateEnd->format('d M Y');
            } elseif ($dateStart) {
              $periodLabel .= ': from ' . $dateStart->format('d M Y');
            } elseif ($dateEnd) {
              $periodLabel .= ': up to ' . $dateEnd->format('d M Y');
            }
          @endphp
          <span class="dash-filter-period">
            <i class="bi bi-calendar3"></i> {{ $periodLabel }}
          </span>
        </form>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- ── Section 1: Summary KPIs ─────────────────────────── --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="rpt-section-title">
          <i class="bi bi-bar-chart-line"></i> Summary
        </div>
        <div class="stats-grid" style="margin-bottom: 28px;">

          <div class="stat-card">
            <div class="stat-icon" style="background:#e8f4fd;color:#2196F3;">
              <i class="bi bi-bag-check"></i>
            </div>
            <div class="stat-body">
              <p class="stat-label">Total Orders</p>
              <p class="stat-value">{{ number_format($ordersTotal) }}</p>
              <p class="stat-sub">₦{{ number_format($ordersValue, 2) }} total value</p>
            </div>
          </div>

          <div class="stat-card success">
            <div class="stat-icon" style="background:#e8f8ef;color:#4caf50;">
              <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stat-body">
              <p class="stat-label">Revenue Collected</p>
              <p class="stat-value">₦{{ number_format($revenueTotal, 2) }}</p>
              <p class="stat-sub">{{ number_format($paymentsTotal) }} payment(s)</p>
            </div>
          </div>

          <div class="stat-card danger">
            <div class="stat-icon" style="background:#fdecea;color:#f44336;">
              <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="stat-body">
              <p class="stat-label">Outstanding Debt</p>
              <p class="stat-value">₦{{ number_format($totalDebt, 2) }}</p>
              <p class="stat-sub">Unpaid order balances</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e1;color:#ff9800;">
              <i class="bi bi-truck"></i>
            </div>
            <div class="stat-body">
              <p class="stat-label">Deliveries</p>
              <p class="stat-value">{{ number_format($deliveriesTotal) }}</p>
              <p class="stat-sub">{{ number_format($deliveriesDelivered) }} completed</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background:#f3e5f5;color:#9c27b0;">
              <i class="bi bi-calculator"></i>
            </div>
            <div class="stat-body">
              <p class="stat-label">Avg. Order Value</p>
              <p class="stat-value">₦{{ number_format($ordersAvgValue, 2) }}</p>
              <p class="stat-sub">Per order in period</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;">
              <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-body">
              <p class="stat-label">Pending Payments</p>
              <p class="stat-value">{{ number_format($paymentsPending) }}</p>
              <p class="stat-sub">Awaiting approval</p>
            </div>
          </div>

        </div>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- ── Section 2: Orders Breakdown ─────────────────────── --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="rpt-section-title">
          <i class="bi bi-bag"></i> Orders Breakdown
        </div>
        <div class="rpt-two-col" style="margin-bottom: 28px;">

          {{-- Status breakdown ──────────────────────────── --}}
          <div class="rpt-card">
            <div class="rpt-card-header">Orders by Status</div>
            <div class="rpt-card-body">
              @php
                $statusRows = [
                  ['label' => 'Pending',   'count' => $ordersPending,   'css' => 'pending',   'pct' => $ordersTotal > 0 ? ($ordersPending / $ordersTotal * 100) : 0],
                  ['label' => 'Approved',  'count' => $ordersApproved,  'css' => 'approved',  'pct' => $ordersTotal > 0 ? ($ordersApproved / $ordersTotal * 100) : 0],
                  ['label' => 'Delivered', 'count' => $ordersDelivered, 'css' => 'delivered', 'pct' => $ordersTotal > 0 ? ($ordersDelivered / $ordersTotal * 100) : 0],
                  ['label' => 'Rejected',  'count' => $ordersRejected,  'css' => 'rejected',  'pct' => $ordersTotal > 0 ? ($ordersRejected / $ordersTotal * 100) : 0],
                ];
              @endphp
              @forelse ($statusRows as $row)
                <div class="rpt-bar-row">
                  <span class="rpt-bar-label">{{ $row['label'] }}</span>
                  <div class="rpt-bar-track">
                    <div class="rpt-bar-fill rpt-bar-{{ $row['css'] }}"
                         style="width: {{ round($row['pct']) }}%"></div>
                  </div>
                  <span class="rpt-bar-count">{{ number_format($row['count']) }}</span>
                  <span class="rpt-bar-pct">({{ number_format($row['pct'], 1) }}%)</span>
                </div>
              @empty
                <p class="rpt-empty">No orders in this period.</p>
              @endforelse
              <div class="rpt-bar-total">
                Total: <strong>{{ number_format($ordersTotal) }}</strong> orders
                &bull; Value: <strong>₦{{ number_format($ordersValue, 2) }}</strong>
              </div>
            </div>
          </div>

          {{-- Orders by State ──────────────────────────── --}}
          <div class="rpt-card">
            <div class="rpt-card-header">Orders by State</div>
            <div class="rpt-card-body" style="padding: 0;">
              @if ($ordersByState->isEmpty())
                <p class="rpt-empty" style="padding: 18px;">No data for this period.</p>
              @else
                <table class="rpt-table">
                  <thead>
                    <tr>
                      <th>State</th>
                      <th class="ta-right">Orders</th>
                      <th class="ta-right">Value (₦)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($ordersByState as $row)
                      <tr>
                        <td>{{ $row->state ?? '—' }}</td>
                        <td class="ta-right">{{ number_format($row->order_count) }}</td>
                        <td class="ta-right">{{ number_format($row->total_value, 2) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              @endif
            </div>
          </div>

        </div>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- ── Section 3: Revenue ──────────────────────────────── --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="rpt-section-title">
          <i class="bi bi-cash-stack"></i> Revenue &amp; Payments
        </div>
        <div class="rpt-two-col" style="margin-bottom: 28px;">

          {{-- Payment method breakdown ───────────────── --}}
          <div class="rpt-card">
            <div class="rpt-card-header">Revenue by Payment Method</div>
            <div class="rpt-card-body" style="padding: 0;">
              @if ($revenueByMethod->isEmpty())
                <p class="rpt-empty" style="padding: 18px;">No approved payments in this period.</p>
              @else
                <table class="rpt-table">
                  <thead>
                    <tr>
                      <th>Method</th>
                      <th class="ta-right">Transactions</th>
                      <th class="ta-right">Total (₦)</th>
                      <th class="ta-right">Share</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($revenueByMethod as $row)
                      @php
                        $methodLabel = match($row->payment_method) {
                          'bank_transfer' => 'Bank Transfer',
                          'cash'          => 'Cash',
                          'pos'           => 'POS',
                          'mobile_money'  => 'Mobile Money',
                          default         => ucwords(str_replace('_', ' ', $row->payment_method)),
                        };
                        $share = $revenueTotal > 0 ? ($row->total / $revenueTotal * 100) : 0;
                      @endphp
                      <tr>
                        <td>{{ $methodLabel }}</td>
                        <td class="ta-right">{{ number_format($row->count) }}</td>
                        <td class="ta-right">{{ number_format($row->total, 2) }}</td>
                        <td class="ta-right">{{ number_format($share, 1) }}%</td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr class="rpt-tfoot">
                      <td><strong>Total</strong></td>
                      <td class="ta-right"><strong>{{ number_format($paymentsTotal) }}</strong></td>
                      <td class="ta-right"><strong>₦{{ number_format($revenueTotal, 2) }}</strong></td>
                      <td class="ta-right">100%</td>
                    </tr>
                  </tfoot>
                </table>
              @endif
            </div>
          </div>

          {{-- Debt overview ──────────────────────────── --}}
          <div class="rpt-card">
            <div class="rpt-card-header" style="color: #c0392b;">
              <i class="bi bi-exclamation-circle"></i> Outstanding Debt (Top 20 Orders)
            </div>
            <div class="rpt-card-body" style="padding: 0;">
              @if ($debtOrders->isEmpty())
                <p class="rpt-empty" style="padding: 18px;">No outstanding debt in this period. 🎉</p>
              @else
                <table class="rpt-table">
                  <thead>
                    <tr>
                      <th>Order</th>
                      <th>Customer</th>
                      <th class="ta-right">Total (₦)</th>
                      <th class="ta-right">Paid (₦)</th>
                      <th class="ta-right">Remaining (₦)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($debtOrders as $row)
                      <tr>
                        <td>
                          <a href="{{ route('orders.show', $row->id) }}" class="rpt-link">
                            {{ $row->order_number }}
                          </a>
                        </td>
                        <td>
                          {{ $row->customer_name }}
                          @if ($row->state)
                            <span class="rpt-muted">({{ $row->state }})</span>
                          @endif
                        </td>
                        <td class="ta-right">{{ number_format($row->total_amount, 2) }}</td>
                        <td class="ta-right">{{ number_format($row->paid, 2) }}</td>
                        <td class="ta-right rpt-debt-cell">{{ number_format($row->remaining, 2) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr class="rpt-tfoot">
                      <td colspan="4"><strong>Total Debt</strong></td>
                      <td class="ta-right rpt-debt-cell"><strong>₦{{ number_format($totalDebt, 2) }}</strong></td>
                    </tr>
                  </tfoot>
                </table>
              @endif
            </div>
          </div>

        </div>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- ── Section 4: Deliveries ───────────────────────────── --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="rpt-section-title">
          <i class="bi bi-truck"></i> Deliveries
        </div>
        <div class="rpt-two-col" style="margin-bottom: 28px;">

          {{-- Delivery status bars ────────────────────── --}}
          <div class="rpt-card">
            <div class="rpt-card-header">Deliveries by Status</div>
            <div class="rpt-card-body">
              @php
                $dlvRows = [
                  ['label' => 'Pending Approval',  'count' => $deliveriesPending,   'css' => 'pending'],
                  ['label' => 'Out for Delivery',  'count' => $deliveriesApproved,  'css' => 'approved'],
                  ['label' => 'Delivered',         'count' => $deliveriesDelivered, 'css' => 'delivered'],
                ];
              @endphp
              @if ($deliveriesTotal === 0)
                <p class="rpt-empty">No deliveries in this period.</p>
              @else
                @foreach ($dlvRows as $row)
                  @php $pct = $deliveriesTotal > 0 ? ($row['count'] / $deliveriesTotal * 100) : 0; @endphp
                  <div class="rpt-bar-row">
                    <span class="rpt-bar-label">{{ $row['label'] }}</span>
                    <div class="rpt-bar-track">
                      <div class="rpt-bar-fill rpt-bar-{{ $row['css'] }}"
                           style="width: {{ round($pct) }}%"></div>
                    </div>
                    <span class="rpt-bar-count">{{ number_format($row['count']) }}</span>
                    <span class="rpt-bar-pct">({{ number_format($pct, 1) }}%)</span>
                  </div>
                @endforeach
                <div class="rpt-bar-total">
                  Total: <strong>{{ number_format($deliveriesTotal) }}</strong> deliveries
                </div>
              @endif
            </div>
          </div>

          {{-- Staff performance ──────────────────────── --}}
          <div class="rpt-card">
            <div class="rpt-card-header">Staff Delivery Performance</div>
            <div class="rpt-card-body" style="padding: 0;">
              @if ($staffPerformance->isEmpty())
                <p class="rpt-empty" style="padding: 18px;">No delivery data for this period.</p>
              @else
                <table class="rpt-table">
                  <thead>
                    <tr>
                      <th>Staff</th>
                      <th>State</th>
                      <th class="ta-right">Total</th>
                      <th class="ta-right">Done</th>
                      <th class="ta-right">Pending</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($staffPerformance as $row)
                      <tr>
                        <td>{{ $row->staff_name }}</td>
                        <td>{{ $row->state ?? '—' }}</td>
                        <td class="ta-right">{{ $row->total_deliveries }}</td>
                        <td class="ta-right" style="color:#27ae60;font-weight:600;">{{ $row->completed }}</td>
                        <td class="ta-right" style="color:#e67e22;">{{ $row->pending }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              @endif
            </div>
          </div>

        </div>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- ── Section 5: Top Products & Customers ─────────────── --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="rpt-section-title">
          <i class="bi bi-trophy"></i> Top Performers
        </div>
        <div class="rpt-two-col" style="margin-bottom: 28px;">

          {{-- Top products ────────────────────────────── --}}
          <div class="rpt-card">
            <div class="rpt-card-header">Top 10 Products by Revenue</div>
            <div class="rpt-card-body" style="padding: 0;">
              @if ($topProducts->isEmpty())
                <p class="rpt-empty" style="padding: 18px;">No order items in this period.</p>
              @else
                <table class="rpt-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Product</th>
                      <th class="ta-right">Qty Sold</th>
                      <th class="ta-right">Revenue (₦)</th>
                      <th class="ta-right">Orders</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($topProducts as $i => $row)
                      <tr>
                        <td class="rpt-rank">{{ $i + 1 }}</td>
                        <td>{{ $row->product_name }}</td>
                        <td class="ta-right">{{ number_format($row->total_qty) }}</td>
                        <td class="ta-right">{{ number_format($row->total_revenue, 2) }}</td>
                        <td class="ta-right">{{ number_format($row->order_count) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              @endif
            </div>
          </div>

          {{-- Top customers ───────────────────────────── --}}
          <div class="rpt-card">
            <div class="rpt-card-header">Top 10 Customers by Order Value</div>
            <div class="rpt-card-body" style="padding: 0;">
              @if ($topCustomers->isEmpty())
                <p class="rpt-empty" style="padding: 18px;">No customer orders in this period.</p>
              @else
                <table class="rpt-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Customer</th>
                      <th>State</th>
                      <th class="ta-right">Orders</th>
                      <th class="ta-right">Value (₦)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($topCustomers as $i => $row)
                      <tr>
                        <td class="rpt-rank">{{ $i + 1 }}</td>
                        <td>
                          <a href="{{ route('customers.show', $row->id) }}" class="rpt-link">
                            {{ $row->name }}
                          </a>
                          @if ($row->shop_name)
                            <br><span class="rpt-muted">{{ $row->shop_name }}</span>
                          @endif
                        </td>
                        <td>{{ $row->state ?? '—' }}</td>
                        <td class="ta-right">{{ number_format($row->order_count) }}</td>
                        <td class="ta-right">{{ number_format($row->total_value, 2) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              @endif
            </div>
          </div>

        </div>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- ── Section 6: Recent Orders ────────────────────────── --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="rpt-section-title">
          <i class="bi bi-clock-history"></i> Recent Orders (Last 20 in Period)
        </div>
        <div class="rpt-card" style="margin-bottom: 40px;">
          <div class="rpt-card-body" style="padding: 0;">
            @if ($recentOrders->isEmpty())
              <p class="rpt-empty" style="padding: 18px;">No orders in this period.</p>
            @else
              <table class="rpt-table">
                <thead>
                  <tr>
                    <th>Order No.</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th class="ta-right">Amount (₦)</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($recentOrders as $order)
                    <tr>
                      <td>
                        <a href="{{ route('orders.show', $order) }}" class="rpt-link">
                          {{ $order->order_number }}
                        </a>
                      </td>
                      <td>{{ $order->user->name ?? '—' }}</td>
                      <td>
                        <span class="ord-status-badge ord-status-{{ $order->status }}">
                          {{ $order->status_label }}
                        </span>
                      </td>
                      <td class="ta-right">{{ number_format($order->total_amount, 2) }}</td>
                      <td>{{ $order->created_at->format('d M Y') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          </div>
        </div>

      </main>
    </div>

    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    <script>
      function reportRangeChanged(val) {
        var custom = document.getElementById('reportCustomInputs');
        if (custom) custom.style.display = (val === 'custom') ? 'flex' : 'none';
        if (val !== 'custom') document.getElementById('reportFilterForm').submit();
      }
    </script>
  </body>
</html>
