<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $customer->name }} - Customer Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <style>
      /* ── Customer profile card ── */
      .cust-profile-grid {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 24px;
        align-items: start;
      }
      .cust-avatar {
        width: 72px; height: 72px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-size: 1.8rem;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        text-transform: uppercase;
        letter-spacing: 1px;
      }
      .cust-meta h2 { font-size: 1.25rem; font-weight: 700; margin: 0 0 2px; }
      .cust-meta .cust-shop { font-size: 0.88rem; color: var(--text-soft); margin: 0 0 10px; }
      .cust-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px 34px;
        margin-top: 4px;
      }
      .cust-detail-item { font-size: 0.83rem; }
      .cust-detail-label { color: var(--text-soft); display: block; margin-bottom: 1px; font-size: 0.75rem; }
      .cust-detail-value { font-weight: 500; }

      /* ── Summary stat strip ── */
      .cust-stat-strip {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
      }
      .cust-stat { padding: 16px 20px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border); }
      .cust-stat-value { font-size: 1.35rem; font-weight: 700; display: block; margin-bottom: 2px; }
      .cust-stat-label { font-size: 0.75rem; color: var(--text-soft); }
      .cust-stat.danger { border-color: #fecaca; background: #fff5f5; }
      .cust-stat.danger .cust-stat-value { color: #dc2626; }
      .cust-stat.success { border-color: #bbf7d0; background: #f0fdf4; }
      .cust-stat.success .cust-stat-value { color: #16a34a; }

      /* ── Section tables ── */
      .section-heading {
        font-size: 0.88rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-soft);
        margin: 0 0 12px;
        display: flex; align-items: center; gap: 8px;
      }
      .section-heading i { font-size: 1rem; }
      .status-pill {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: capitalize;
      }
      .pill-pending  { background: #fef3c7; color: #92400e; }
      .pill-approved { background: #dcfce7; color: #166534; }
      .pill-rejected { background: #fee2e2; color: #991b1b; }
      .pill-delivered{ background: #dbeafe; color: #1e40af; }
    </style>
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
            <h2>Customer Profile</h2>
            <p>Full account details for {{ $customer->name }}</p>
          </div>
          <div class="top-actions">
            @if ($user->role === 'admin')
              <a href="{{ route('admin.customers.index') }}" class="ghost-btn">
                <i class="bi bi-arrow-left"></i> All Customers
              </a>
            @else
              <a href="{{ route('staff.customers.index') }}" class="ghost-btn">
                <i class="bi bi-arrow-left"></i> All Customers
              </a>
            @endif
            @if ($user->role === 'admin')
              <a href="{{ route('users.edit', $customer->id) }}" class="ghost-btn">
                <i class="bi bi-pencil"></i> Edit
              </a>
            @endif
          </div>
        </header>

        {{-- ── Profile card ── --}}
        <section class="card" style="margin-bottom: 16px;">
          <div class="cust-profile-grid">
            <div class="cust-meta">
              <h2>{{ $customer->name }}</h2>
              <p class="cust-shop">
                <i class="bi bi-shop" style="margin-right:4px;"></i>
                {{ $customer->shop_name ?: 'No shop name' }}
              </p>
              <div class="cust-detail-grid">
                <div class="cust-detail-item">
                  <span class="cust-detail-label">Email</span>
                  <span class="cust-detail-value">{{ $customer->email ?: '—' }}</span>
                </div>
                <div class="cust-detail-item">
                  <span class="cust-detail-label">Phone</span>
                  <span class="cust-detail-value">{{ $customer->phone ?: '—' }}</span>
                </div>
                <div class="cust-detail-item">
                  <span class="cust-detail-label">State</span>
                  <span class="cust-detail-value">{{ $customer->state ?: '—' }}</span>
                </div>
                <div class="cust-detail-item">
                  <span class="cust-detail-label">LGA</span>
                  <span class="cust-detail-value">{{ $customer->lga ?: '—' }}</span>
                </div>
                <div class="cust-detail-item" style="grid-column: span 2;">
                  <span class="cust-detail-label">Address</span>
                  <span class="cust-detail-value">{{ $customer->address ?: '—' }}</span>
                </div>
                <div class="cust-detail-item">
                  <span class="cust-detail-label">Member Since</span>
                  <span class="cust-detail-value">{{ optional($customer->created_at)->format('d M Y') }}</span>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- ── Summary stats ── --}}
        <div class="cust-stat-strip">
          <div class="cust-stat">
            <span class="cust-stat-value">{{ $totalOrders }}</span>
            <span class="cust-stat-label">Total Orders</span>
          </div>
          <div class="cust-stat">
            <span class="cust-stat-value">{{ $payments->count() }}</span>
            <span class="cust-stat-label">Total Payments</span>
          </div>
          <div class="cust-stat success">
            <span class="cust-stat-value">&#8358;{{ number_format($totalPaid, 2) }}</span>
            <span class="cust-stat-label">Total Paid</span>
          </div>
          <div class="cust-stat {{ $totalDebt > 0 ? 'danger' : '' }}">
            <span class="cust-stat-value">&#8358;{{ number_format($totalDebt, 2) }}</span>
            <span class="cust-stat-label">Outstanding Debt</span>
          </div>
          <div class="cust-stat">
            <span class="cust-stat-value">{{ $deliveries->count() }}</span>
            <span class="cust-stat-label">Deliveries</span>
          </div>
        </div>

        {{-- ── Orders ── --}}
        <section class="card table-card" style="margin-bottom: 16px;">
          <div class="card-head">
            <div>
              <h3 class="section-heading"><i class="bi bi-bag"></i> Orders</h3>
              <span>{{ $totalOrders }} total</span>
            </div>
            <a href="{{ route('orders.create') }}?customer_id={{ $customer->id }}" class="ghost-btn">
              <i class="bi bi-plus"></i> New Order
            </a>
          </div>
          <div class="table-scroll">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Items</th>
                  <th>Total</th>
                  <th>Paid</th>
                  <th>Remaining</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse($orders as $o)
                  @php
                    $paid      = $o->payments->sum('amount');
                    $remaining = max(0, (float)$o->total_amount - $paid);
                  @endphp
                  <tr>
                    <td><strong>{{ $o->order_number }}</strong></td>
                    <td>{{ $o->items_count }}</td>
                    <td>&#8358;{{ number_format($o->total_amount, 2) }}</td>
                    <td>&#8358;{{ number_format($paid, 2) }}</td>
                    <td>
                      @if ($remaining > 0)
                        <span style="color:#dc2626; font-weight:600;">&#8358;{{ number_format($remaining, 2) }}</span>
                      @else
                        <span style="color:#16a34a; font-weight:600;">Paid</span>
                      @endif
                    </td>
                    <td>
                      <span class="status-pill
                        {{ match($o->status) {
                          'pending'   => 'pill-pending',
                          'approved'  => 'pill-approved',
                          'rejected'  => 'pill-rejected',
                          'delivered' => 'pill-delivered',
                          default     => ''
                        } }}">{{ ucfirst($o->status) }}</span>
                    </td>
                    <td>{{ optional($o->created_at)->format('d M Y') }}</td>
                    <td><a href="{{ route('orders.show', $o->id) }}" class="ua-btn ua-view"><i class="bi bi-eye"></i> View</a></td>
                  </tr>
                @empty
                  <tr><td colspan="8" style="text-align:center; color:var(--text-soft); padding:20px;">No orders yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>

        {{-- ── Payments ── --}}
        <section class="card table-card" style="margin-bottom: 16px;">
          <div class="card-head">
            <div>
              <h3 class="section-heading"><i class="bi bi-credit-card"></i> Payments</h3>
              <span>{{ $payments->count() }} total</span>
            </div>
            <a href="{{ route('payments.create') }}" class="ghost-btn">
              <i class="bi bi-plus"></i> New Payment
            </a>
          </div>
          <div class="table-scroll">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Amount</th>
                  <th>Method</th>
                  <th>Reference</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse($payments as $p)
                  <tr>
                    <td>{{ $p->order?->order_number ?? '—' }}</td>
                    <td>&#8358;{{ number_format($p->amount, 2) }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $p->payment_method)) }}</td>
                    <td>{{ $p->payment_number ?: '—' }}</td>
                    <td>
                      <span class="status-pill
                        {{ match($p->status) {
                          'pending'  => 'pill-pending',
                          'approved' => 'pill-approved',
                          'rejected' => 'pill-rejected',
                          default    => ''
                        } }}">{{ ucfirst($p->status) }}</span>
                    </td>
                    <td>{{ optional($p->created_at)->format('d M Y') }}</td>
                    <td><a href="{{ route('payments.show', $p->id) }}" class="ua-btn ua-view"><i class="bi bi-eye"></i> View</a></td>
                  </tr>
                @empty
                  <tr><td colspan="7" style="text-align:center; color:var(--text-soft); padding:20px;">No payments yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>

        {{-- ── Deliveries ── --}}
        <section class="card table-card" style="margin-bottom: 16px;">
          <div class="card-head">
            <div>
              <h3 class="section-heading"><i class="bi bi-truck"></i> Deliveries</h3>
              <span>{{ $deliveries->count() }} total</span>
            </div>
          </div>
          <div class="table-scroll">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Order Total</th>
                  <th>Assigned Staff</th>
                  <th>Address</th>
                  <th>Scheduled</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse($deliveries as $d)
                  <tr>
                    <td><strong>{{ $d->order?->order_number ?? '—' }}</strong></td>
                    <td>&#8358;{{ number_format($d->order?->total_amount ?? 0, 2) }}</td>
                    <td>{{ $d->staff?->name ?? '—' }}</td>
                    <td>{{ Str::limit($d->delivery_address, 40) }}</td>
                    <td>{{ $d->scheduled_at ? \Carbon\Carbon::parse($d->scheduled_at)->format('d M Y') : '—' }}</td>
                    <td>
                      <span class="status-pill
                        {{ match($d->status) {
                          'pending'   => 'pill-pending',
                          'approved'  => 'pill-approved',
                          'delivered' => 'pill-delivered',
                          'rejected' => 'pill-rejected',
                          default     => ''
                        } }}">{{ ucfirst($d->status) }}</span>
                    </td>
                    <td><a href="{{ route('deliveries.show', $d->id) }}" class="ua-btn ua-view"><i class="bi bi-eye"></i> View</a></td>
                  </tr>
                @empty
                  <tr><td colspan="7" style="text-align:center; color:var(--text-soft); padding:20px;">No deliveries yet.</td></tr>
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
