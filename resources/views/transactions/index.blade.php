<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Transaction History - Country Yoghurt</title>
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

        {{-- Header --}}
        <header class="topbar">
          <div class="title-block">
            <h2>Transaction History</h2>
            <p>
              @if ($user->isAdmin())
                All orders, payments and deliveries across the system.
              @elseif ($user->role === 'staff')
                Your orders, payments and deliveries you scheduled.
              @else
                Your full transaction history.
              @endif
            </p>
          </div>
          <div class="top-actions">
            {{-- Type filter --}}
            <form method="GET" action="{{ route('transactions.index') }}" style="display:flex;gap:8px;align-items:center;">
              <select name="type" class="filter-select" onchange="this.form.submit()">
                <option value="">All types</option>
                <option value="order"    {{ request('type') === 'order'    ? 'selected' : '' }}>Orders</option>
                <option value="payment"  {{ request('type') === 'payment'  ? 'selected' : '' }}>Payments</option>
                <option value="delivery" {{ request('type') === 'delivery' ? 'selected' : '' }}>Deliveries</option>
              </select>
            </form>
          </div>
        </header>

        {{-- Alerts --}}
        @if (session('status'))
          <div class="lp-success" style="margin-bottom:14px;">
            <i class="bi bi-check-circle"></i> {{ session('status') }}
          </div>
        @endif

        {{-- Transaction list --}}
        @php $items = $transactions->getCollection(); @endphp

        @if ($items->isEmpty())
          <div class="card" style="text-align:center;padding:48px 24px;color:var(--text-soft);">
            <i class="bi bi-clock-history" style="font-size:2.5rem;display:block;margin-bottom:12px;"></i>
            <p style="margin:0;font-size:0.9rem;">No transactions found.</p>
          </div>
        @else
          {{-- Desktop table --}}
          <div class="txn-table-wrap">
            <table class="txn-table">
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Reference</th>
                  <th>Description</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($items as $txn)
                  @php
                    $statusClass = match($txn->status) {
                        'approved', 'delivered' => 'txn-status-success',
                        'rejected'              => 'txn-status-danger',
                        'pending'               => 'txn-status-warn',
                        default                 => 'txn-status-neutral',
                    };
                    $typeClass = match($txn->type) {
                        'order'    => 'txn-type-order',
                        'payment'  => 'txn-type-payment',
                        'delivery' => 'txn-type-delivery',
                        default    => '',
                    };
                  @endphp
                  <tr class="txn-row" onclick="window.location='{{ $txn->url }}'" style="cursor:pointer;">
                    <td>
                      <span class="txn-type-badge {{ $typeClass }}">
                        <i class="bi {{ $txn->icon }}"></i>
                        {{ ucfirst($txn->type) }}
                      </span>
                    </td>
                    <td class="txn-ref">{{ $txn->ref }}</td>
                    <td class="txn-desc">{{ $txn->description }}</td>
                    <td class="txn-amount">
                      @if (!is_null($txn->amount))
                        ₦{{ number_format($txn->amount, 2) }}
                      @else
                        <span style="color:var(--text-muted);">-</span>
                      @endif
                    </td>
                    <td>
                      <span class="txn-status {{ $statusClass }}">{{ ucfirst($txn->status) }}</span>
                    </td>
                    <td class="txn-date">
                      <span title="{{ $txn->date->format('d M Y, H:i') }}">
                        {{ $txn->date->format('d M Y') }}
                      </span>
                      <small>{{ $txn->date->format('H:i') }}</small>
                    </td>
                    <td>
                      <a href="{{ $txn->url }}" class="txn-view-btn" title="View details" onclick="event.stopPropagation();">
                        <i class="bi bi-arrow-right"></i>
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          {{-- Mobile cards --}}
          <div class="txn-cards">
            @foreach ($items as $txn)
              @php
                $statusClass = match($txn->status) {
                    'approved', 'delivered' => 'txn-status-success',
                    'rejected'              => 'txn-status-danger',
                    'pending'               => 'txn-status-warn',
                    default                 => 'txn-status-neutral',
                };
                $typeClass = match($txn->type) {
                    'order'    => 'txn-type-order',
                    'payment'  => 'txn-type-payment',
                    'delivery' => 'txn-type-delivery',
                    default    => '',
                };
              @endphp
              <a href="{{ $txn->url }}" class="txn-card">
                <div class="txn-card-icon {{ $typeClass }}">
                  <i class="bi {{ $txn->icon }}"></i>
                </div>
                <div class="txn-card-body">
                  <div class="txn-card-top">
                    <span class="txn-ref">{{ $txn->ref }}</span>
                    <span class="txn-status {{ $statusClass }}">{{ ucfirst($txn->status) }}</span>
                  </div>
                  <p class="txn-desc">{{ $txn->description }}</p>
                  <div class="txn-card-foot">
                    @if (!is_null($txn->amount))
                      <span class="txn-amount">₦{{ number_format($txn->amount, 2) }}</span>
                    @endif
                    <span class="txn-date">{{ $txn->date->format('d M Y, H:i') }}</span>
                  </div>
                </div>
                <i class="bi bi-chevron-right txn-card-chevron"></i>
              </a>
            @endforeach
          </div>
        @endif

        {{-- Pagination --}}
        @if ($transactions->hasPages())
          <div style="margin-top:16px;">
            {{ $transactions->links() }}
          </div>
        @endif

      </main>
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <script>
      (function () {
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
