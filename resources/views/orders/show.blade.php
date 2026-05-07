<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order {{ $order->order_number }} - Country Yoghurt</title>
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

        <header class="topbar">
          <div class="title-block">
            <h2>{{ $order->order_number }}</h2>
            <p>Placed on {{ $order->created_at->format('d M Y, g:ia') }}</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('orders.index') }}" class="ghost-btn no-print">
              <i class="bi bi-arrow-left"></i> All Orders
            </a>
            @if (in_array($user->role, ['staff', 'customer'], true))
              <a href="{{ route('orders.create') }}" class="primary-btn no-print">
                <i class="bi bi-plus-lg"></i> New Order
              </a>
            @endif
            <button onclick="window.print()" class="ghost-btn no-print">
              <i class="bi bi-printer"></i> Print
            </button>
          </div>
        </header>

        {{-- Print header --}}
        <div class="print-header">
          <img src="{{ asset('assets/img/logo.png') }}" alt="Country Yoghurt" class="print-logo" />
          <div class="print-company-info">
            <h2>Country Yoghurt</h2>
            <p>Printed {{ now()->format('d M Y, g:ia') }}</p>
          </div>
        </div>

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

        {{-- ── Order meta ── --}}
        @php
          $totalPaid      = $order->payments->where('status', 'approved')->sum('amount');
          $totalRemaining = max(0, (float)$order->total_amount - $totalPaid);
        @endphp
        <div class="ord-meta-grid">
          <div class="ord-meta-card">
            <p class="ord-meta-label">Status</p>
            <span class="ord-status-badge {{ $order->status_css }}">
              {{ $order->status_label }}
            </span>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Placed By</p>
            <p class="ord-meta-value">{{ $order->user->name ?? '-' }}</p>
            <small class="ord-meta-sub">{{ ucfirst($order->user->role ?? '') }}</small>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Total Amount</p>
            <p class="ord-meta-value ord-amount">&#8358;{{ number_format($order->total_amount, 2) }}</p>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Total Paid</p>
            <p class="ord-meta-value" style="color:{{ $totalPaid > 0 ? '#16a34a' : 'inherit' }}">
              &#8358;{{ number_format($totalPaid, 2) }}
            </p>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Balance Remaining</p>
            @if ($totalRemaining <= 0)
              <p class="ord-meta-value" style="color:#16a34a;">&#10003; Fully Paid</p>
            @else
              <p class="ord-meta-value" style="color:#dc2626;">&#8358;{{ number_format($totalRemaining, 2) }}</p>
            @endif
          </div>
          @if ($order->approved_at)
            <div class="ord-meta-card">
              <p class="ord-meta-label">{{ $order->status === 'approved' ? 'Approved' : 'Actioned' }} By</p>
              <p class="ord-meta-value">{{ $order->approvedBy->name ?? '-' }}</p>
              <small class="ord-meta-sub">{{ $order->approved_at->format('d M Y, g:ia') }}</small>
            </div>
          @endif
        </div>

        {{-- Notes --}}
        @if ($order->notes)
          <div class="card" style="margin-bottom: 16px; padding: 14px 16px;">
            <p class="ord-meta-label" style="margin-bottom: 6px;"><i class="bi bi-chat-left-text"></i> Notes</p>
            <p style="margin: 0; font-size: 0.88rem; color: var(--text-main);">{{ $order->notes }}</p>
          </div>
        @endif

        @if ($order->rejection_reason)
          <div class="card" style="margin-bottom: 16px; padding: 14px 16px; border-left: 4px solid var(--danger);">
            <p class="ord-meta-label" style="margin-bottom: 6px; color: var(--danger);">
              <i class="bi bi-exclamation-circle"></i> Rejection Reason
            </p>
            <p style="margin: 0; font-size: 0.88rem; color: var(--text-main);">{{ $order->rejection_reason }}</p>
          </div>
        @endif

        {{-- ── Items table ── --}}
        <section class="card table-card" style="margin-bottom: 16px;">
          <div class="table-scroll">
            <table class="inv-table ord-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Unit Price (₦)</th>
                  <th>Qty</th>
                  <th>Subtotal (₦)</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($order->items as $item)
                  <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="ord-amount">{{ number_format($item->subtotal, 2) }}</td>
                  </tr>
                @endforeach
                <tr class="ord-total-row">
                  <td colspan="3"><strong>Total</strong></td>
                  <td class="ord-amount"><strong>₦{{ number_format($order->total_amount, 2) }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        {{-- ── Payment section ── --}}
        @if (in_array($order->status, ['approved', 'delivered'], true))
          <section class="card table-card" style="margin-bottom: 16px;">
            <div class="card-head">
              <div>
                <h3><i class="bi bi-credit-card" style="margin-right:6px;"></i>Payments</h3>
                <span>{{ $order->payments->count() }} payment(s) &middot; &#8358;{{ number_format($totalPaid, 2) }} approved</span>
              </div>
              @if (in_array($user->role, ['staff', 'customer'], true) && $totalRemaining > 0)
                <a href="{{ route('payments.create', ['order_id' => $order->id]) }}" class="primary-btn">
                  <i class="bi bi-send"></i> Submit Payment
                </a>
              @endif
            </div>
            @if ($order->payments->isNotEmpty())
              <div class="table-scroll">
                <table class="dash-table">
                  <thead>
                    <tr>
                      <th>Amount</th>
                      <th>Method</th>
                      <th>Reference</th>
                      <th>Status</th>
                      <th>Date</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($order->payments->sortByDesc('created_at') as $pmt)
                      <tr>
                        <td><strong>&#8358;{{ number_format($pmt->amount, 2) }}</strong></td>
                        <td>{{ ucwords(str_replace('_', ' ', $pmt->payment_method)) }}</td>
                        <td>{{ $pmt->payment_number ?: '-' }}</td>
                        <td>
                          <span class="pay-status-badge {{ $pmt->status_css }}">{{ $pmt->status_label }}</span>
                        </td>
                        <td>{{ optional($pmt->created_at)->format('d M Y, g:ia') }}</td>
                        <td>
                          <a href="{{ route('payments.show', $pmt) }}" class="ua-btn ua-view">
                            <i class="bi bi-eye"></i> View
                          </a>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <p style="padding: 16px; font-size:0.87rem; color:var(--text-soft); margin:0;">
                No payments recorded yet.
              </p>
            @endif
          </section>
        @endif

        {{-- ── Staff: Delivery section ── --}}
        @if ($user->role === 'staff' && $order->status === 'approved')
          <section class="card ord-action-bar">
            <p class="ord-action-title">
              <i class="bi bi-truck"></i> Delivery
            </p>
            <div class="ord-action-btns">
              <a href="{{ route('deliveries.index') }}" class="ghost-btn">
                <i class="bi bi-truck"></i> View Deliveries
              </a>
              <a href="{{ route('deliveries.create') }}" class="primary-btn">
                <i class="bi bi-plus-lg"></i> New Delivery
              </a>
            </div>
          </section>
        @endif



        {{-- ── Admin actions ── --}}
        @if ($user->isAdmin())
          @if ($order->status === 'pending')
          <section class="card ord-action-bar">
            <p class="ord-action-title">
              <i class="bi bi-shield-check"></i> Admin Review
            </p>
            <div class="ord-action-btns">
              {{-- Approve --}}
              <form method="POST" action="{{ route('orders.approve', $order) }}" style="display:inline;">
                @csrf
                <button type="submit" class="primary-btn">
                  <i class="bi bi-check-lg"></i> Approve Order
                </button>
              </form>

              {{-- Reject --}}
              <button type="button" class="ghost-btn danger-ghost" id="openRejectBtn">
                <i class="bi bi-x-lg"></i> Reject Order
              </button>
            </div>
          </section>

          {{-- Reject modal --}}
          <div class="inv-modal-overlay" id="rejectModal">
            <div class="inv-modal inv-modal-sm">
              <div class="inv-modal-head">
                <h3><i class="bi bi-x-circle" style="color:var(--danger)"></i> Reject Order</h3>
                <button class="inv-modal-close" onclick="closeRejectModal()">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
              <form method="POST" action="{{ route('orders.reject', $order) }}">
                @csrf
                <div class="inv-modal-body">
                  <p style="margin-bottom: 12px; font-size:0.88rem;">
                    Rejecting <strong>{{ $order->order_number }}</strong>. You can optionally provide a reason.
                  </p>
                  <label class="inv-field-label" for="rejection_reason">Reason (optional)</label>
                  <textarea name="rejection_reason" id="rejection_reason"
                            class="inv-field-input" rows="3"
                            placeholder="e.g. Product unavailable, duplicate order…"
                            style="resize:vertical; width:100%;"></textarea>
                </div>
                <div class="inv-modal-footer">
                  <button type="button" class="ghost-btn" onclick="closeRejectModal()">Cancel</button>
                  <button type="submit" class="primary-btn" style="background:var(--danger)">Reject</button>
                </div>
              </form>
            </div>
          </div>
          @elseif ($order->status === 'approved')
          <section class="card ord-action-bar">
            <p class="ord-action-title">
              <i class="bi bi-truck"></i> Delivery
            </p>
            <div class="ord-action-btns">
              <a href="{{ route('deliveries.index') }}" class="ghost-btn">
                <i class="bi bi-truck"></i> View Deliveries
              </a>
              <a href="{{ route('deliveries.create') }}" class="primary-btn">
                <i class="bi bi-plus-lg"></i> New Delivery
              </a>
            </div>
          </section>
          @endif
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

      var rejectModal = document.getElementById('rejectModal');
      var openBtn     = document.getElementById('openRejectBtn');
      if (openBtn) {
        openBtn.addEventListener('click', function() {
          rejectModal.classList.add('active');
          document.body.style.overflow = 'hidden';
        });
        rejectModal.addEventListener('click', function(e) {
          if (e.target === rejectModal) closeRejectModal();
        });
      }
      function closeRejectModal() {
        if (rejectModal) { rejectModal.classList.remove('active'); document.body.style.overflow = ''; }
      }
    </script>
  </body>
</html>
