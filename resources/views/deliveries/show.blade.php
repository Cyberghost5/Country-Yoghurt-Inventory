<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Delivery {{ $delivery->delivery_number }} - Country Yoghurt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <style>
      .dlv-meta-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px,1fr)); gap:12px; margin-bottom:24px; }
      .dlv-meta-item { background:#fafaf8; border:1px solid #e5e0d6; border-radius:8px; padding:14px; }
      .dlv-meta-label{ font-size:0.75rem; color:var(--text-soft); margin-bottom:4px; }
      .dlv-meta-value{ font-size:0.97rem; font-weight:600; color:var(--text-main); }
      .alloc-card    { background:#fff; border:1px solid #e5e0d6; border-radius:10px; padding:20px; margin-bottom:16px; }
      .alloc-header  { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
      .alloc-name    { font-weight:700; font-size:1rem; }
      .alloc-balance { text-align:right; }
      .alloc-bal-row { font-size:0.82rem; color:var(--text-soft); margin-bottom:2px; }
      .alloc-bal-rem { font-size:1.05rem; font-weight:700; }
      .alloc-bal-rem.paid { color:#16a34a; }
      .alloc-bal-rem.owed { color:#dc2626; }
      .mini-table    { width:100%; border-collapse:collapse; margin-bottom:12px; }
      .mini-table th { font-size:0.75rem; font-weight:600; color:var(--text-soft); padding:5px 8px; border-bottom:1px solid #e5e0d6; text-align:left; }
      .mini-table td { padding:6px 8px; font-size:0.88rem; border-bottom:1px solid #f0ece4; }
      .pay-history   { font-size:0.82rem; color:var(--text-soft); margin-top:8px; }
      .pay-row       { display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px dashed #e5e0d6; }
    </style>
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">@include('partials._sidebar')</aside>
      <main class="main-content">
        <header class="topbar">
          <div class="title-block">
            <h2>{{ $delivery->delivery_number }}</h2>
            <p>
              <span class="status-badge {{ $delivery->status_css }}">{{ $delivery->status_label }}</span>
            </p>
          </div>
          <div class="top-actions">
            @php
              $allFullyPaid = $delivery->allocations->every(function ($alloc) {
                  $paid = $alloc->payments->where('status', 'approved')->sum('amount');
                  return $alloc->total_amount > 0 && $paid >= $alloc->total_amount;
              });
            @endphp
            <a href="{{ route('deliveries.index') }}" class="ghost-btn"><i class="bi bi-arrow-left"></i> Back</a>
            @if ($user->isAdminOrStaff())
              <a href="{{ route('deliveries.edit', $delivery) }}" class="ghost-btn"><i class="bi bi-pencil"></i> Edit</a>
            @endif
            @if ($delivery->status === 'pending' && $user->isAdminOrStaff())
              <form method="POST" action="{{ route('deliveries.dispatch', $delivery) }}" style="display:inline;">
                @csrf
                <button type="submit" class="primary-btn"><i class="bi bi-truck"></i> Dispatch</button>
              </form>
            @endif
            @if ($delivery->status === 'dispatched' && $user->isAdminOrStaff())
              @if ($allFullyPaid)
                <form method="POST" action="{{ route('deliveries.complete', $delivery) }}" style="display:inline;">
                  @csrf
                  <button type="submit" class="primary-btn" style="background:var(--green, #16a34a);"><i class="bi bi-check-circle"></i> Mark Completed</button>
                </form>
              @else
                <span class="ghost-btn" style="opacity:0.55;cursor:not-allowed;" title="All customer payments must be approved before marking complete">
                  <i class="bi bi-lock"></i> Awaiting Payment
                </span>
              @endif
            @endif
          </div>
        </header>

        @if (session('status'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- Delivery meta --}}
        @php
          $myAlloc = $user->role === 'customer'
              ? $delivery->allocations->firstWhere('customer_id', $user->id)
              : null;
        @endphp
        <div class="dlv-meta-grid">
          <div class="dlv-meta-item">
            <div class="dlv-meta-label">Date Created</div>
            <div class="dlv-meta-value">{{ $delivery->scheduled_at ? $delivery->scheduled_at->format('d M Y') : '-' }}</div>
          </div>
          @if ($user->role !== 'customer')
          <div class="dlv-meta-item">
            <div class="dlv-meta-label">Created By</div>
            <div class="dlv-meta-value">{{ $delivery->staff->name ?? '-' }}</div>
          </div>
          <div class="dlv-meta-item">
            <div class="dlv-meta-label">Customers</div>
            <div class="dlv-meta-value">{{ $delivery->allocations->count() }}</div>
          </div>
          <div class="dlv-meta-item">
            <div class="dlv-meta-label">Total Value</div>
            <div class="dlv-meta-value">&#8358;{{ number_format($delivery->totalAmount(), 2) }}</div>
          </div>
          @php
            $totalOutstanding = $delivery->allocations->sum(fn($a) => $a->remainingAmount());
          @endphp
          <div class="dlv-meta-item">
            <div class="dlv-meta-label">Outstanding Balance</div>
            <div class="dlv-meta-value" style="color:{{ $totalOutstanding > 0 ? '#dc2626' : '#16a34a' }};">
              {{ $totalOutstanding > 0 ? '₦'.number_format($totalOutstanding, 2) : 'Fully Paid' }}
            </div>
          </div>
          @else
          {{-- Customer: show only their own figures --}}
          @if ($myAlloc)
          <div class="dlv-meta-item">
            <div class="dlv-meta-label">Your Total</div>
            <div class="dlv-meta-value">&#8358;{{ number_format($myAlloc->total_amount, 2) }}</div>
          </div>
          <div class="dlv-meta-item">
            <div class="dlv-meta-label">Amount Paid</div>
            <div class="dlv-meta-value" style="color:#16a34a;">&#8358;{{ number_format($myAlloc->paidAmount(), 2) }}</div>
          </div>
          <div class="dlv-meta-item">
            <div class="dlv-meta-label">Balance</div>
            @php $myRemaining = $myAlloc->remainingAmount(); @endphp
            <div class="dlv-meta-value" style="color:{{ $myRemaining > 0 ? '#dc2626' : '#16a34a' }};">
              {{ $myRemaining > 0 ? '₦'.number_format($myRemaining, 2) : 'Fully Paid' }}
            </div>
          </div>
          @endif
          @endif
          @if ($delivery->dispatched_at)
            <div class="dlv-meta-item">
              <div class="dlv-meta-label">Dispatched At</div>
              <div class="dlv-meta-value">{{ $delivery->dispatched_at->format('d M Y, g:ia') }}</div>
            </div>
          @endif
          @if ($delivery->completed_at)
            <div class="dlv-meta-item">
              <div class="dlv-meta-label">Completed At</div>
              <div class="dlv-meta-value">{{ $delivery->completed_at->format('d M Y, g:ia') }}</div>
            </div>
          @endif
          @if ($delivery->notes)
            <div class="dlv-meta-item" style="grid-column:1/-1;">
              <div class="dlv-meta-label">Notes</div>
              <div class="dlv-meta-value" style="font-weight:400;">{{ $delivery->notes }}</div>
            </div>
          @endif
        </div>

        {{-- Allocations --}}
        <h3 style="font-size:1rem; margin-bottom:12px; color:var(--text-main);">{{ $user->role === 'customer' ? 'Your Delivery Details' : 'Customer Allocations' }}</h3>

        @php
          $visibleAllocations = $user->role === 'customer'
              ? $delivery->allocations->where('customer_id', $user->id)
              : $delivery->allocations;
        @endphp

        @forelse ($visibleAllocations as $alloc)
          @php
            $paid      = $alloc->paidAmount();
            $remaining = $alloc->remainingAmount();
            $fullyPaid = $alloc->isFullyPaid();
          @endphp
          <div class="alloc-card">
            @if ($user->role !== 'customer')
            <div class="alloc-header">
              <div>
                <div class="alloc-name">{{ $alloc->customer->name ?? '-' }}</div>
                @if ($alloc->customer->shop_name)
                  <div style="font-size:0.82rem; color:var(--text-soft);">{{ $alloc->customer->shop_name }}</div>
                @endif
                @if ($alloc->allocation_date)
                  <div style="font-size:0.82rem; color:var(--text-soft); margin-top:4px;">
                    <i class="bi bi-calendar3"></i> {{ $alloc->allocation_date->format('d M Y') }}
                  </div>
                @endif
              </div>
              <div class="alloc-balance">
                <div class="alloc-bal-row">Total: &#8358;{{ number_format($alloc->total_amount, 2) }}</div>
                <div class="alloc-bal-row">Paid: &#8358;{{ number_format($paid, 2) }}</div>
                <div class="alloc-bal-rem {{ $fullyPaid ? 'paid' : 'owed' }}">
                  @if ($fullyPaid) Fully Paid
                  @else Balance: &#8358;{{ number_format($remaining, 2) }}
                  @endif
                </div>
              </div>
            </div>
            @else
            @if ($alloc->allocation_date)
              <div style="font-size:0.82rem; color:var(--text-soft); margin-bottom:10px;">
                <i class="bi bi-calendar3"></i> {{ $alloc->allocation_date->format('d M Y') }}
              </div>
            @endif
            @endif

            {{-- Items --}}
            <table class="mini-table">
              <thead>
                <tr><th>Product</th><th>Unit Price</th><th>Qty</th><th>Subtotal</th></tr>
              </thead>
              <tbody>
                @foreach ($alloc->items as $item)
                  <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>&#8358;{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>&#8358;{{ number_format($item->subtotal, 2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>

            {{-- Payment history --}}
            @if ($alloc->payments->count())
              <div class="pay-history">
                <strong>Payments:</strong>
                @foreach ($alloc->payments as $pay)
                  <div class="pay-row">
                    <span>{{ $pay->payment_number }} - {{ ucfirst(str_replace('_',' ',$pay->payment_method)) }}</span>
                    <span>
                      &#8358;{{ number_format($pay->amount, 2) }}
                      <span class="status-badge {{ $pay->status === 'approved' ? 'status-delivered' : ($pay->status === 'rejected' ? 'status-rejected' : 'status-pending') }}" style="font-size:0.7rem; padding:1px 6px;">{{ ucfirst($pay->status) }}</span>
                    </span>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        @empty
          <p style="color:var(--text-soft);">No allocation found.</p>
        @endforelse

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
