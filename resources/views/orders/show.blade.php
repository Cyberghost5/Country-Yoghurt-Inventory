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
            <a href="{{ route('orders.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> All Orders
            </a>
            @if (in_array($user->role, ['staff', 'customer'], true))
              <a href="{{ route('orders.create') }}" class="primary-btn">
                <i class="bi bi-plus-lg"></i> New Order
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

        {{-- ── Order meta ── --}}
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
            <p class="ord-meta-value ord-amount">₦{{ number_format($order->total_amount, 2) }}</p>
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

        {{-- ── Staff: Delivery section ── --}}
        @if ($user->role === 'staff' && $order->status === 'approved')
          @php $latestDelivery = $order->deliveries->sortByDesc('created_at')->first(); @endphp
          @if ($latestDelivery)
            <section class="card ord-action-bar" style="border-left: 4px solid #1a6b45;">
              <p class="ord-action-title">
                <i class="bi bi-truck"></i> Delivery
              </p>
              <div class="ord-action-btns">
                <span class="dlv-status-badge {{ $latestDelivery->status_css }}">{{ $latestDelivery->status_label }}</span>
                <a href="{{ route('deliveries.show', $latestDelivery) }}" class="ghost-btn">
                  <i class="bi bi-eye"></i> View Delivery
                </a>
              </div>
            </section>
          @else
            <section class="card ord-action-bar">
              <p class="ord-action-title">
                <i class="bi bi-truck"></i> Delivery
              </p>
              <div class="ord-action-btns">
                <a href="{{ route('deliveries.create', ['order_id' => $order->id]) }}" class="primary-btn">
                  <i class="bi bi-truck"></i> Schedule Delivery
                </a>
              </div>
            </section>
          @endif
        @endif

        {{-- ── Payment section (non-admin) ── --}}
        @if (in_array($user->role, ['staff', 'customer'], true) && in_array($order->status, ['approved', 'delivered'], true))
          @php
            $activePayment = $order->payments->whereIn('status', ['pending', 'approved'])->first();
          @endphp
          @if ($activePayment)
            <section class="card ord-action-bar" style="border-left: 4px solid #1565c0;">
              <p class="ord-action-title">
                <i class="bi bi-credit-card"></i> Payment
              </p>
              <div class="ord-action-btns">
                <span class="pay-status-badge {{ $activePayment->status_css }}">
                  {{ $activePayment->status_label }}
                </span>
                <a href="{{ route('payments.show', $activePayment) }}" class="ghost-btn">
                  <i class="bi bi-eye"></i> View Payment
                </a>
              </div>
            </section>
          @else
            <section class="card ord-action-bar">
              <p class="ord-action-title">
                <i class="bi bi-credit-card"></i> Payment
              </p>
              <div class="ord-action-btns">
                <a href="{{ route('payments.create', ['order_id' => $order->id]) }}" class="primary-btn">
                  <i class="bi bi-send"></i> Submit Payment
                </a>
              </div>
            </section>
          @endif
        @endif

        {{-- ── Admin actions ── --}}
        @if ($user->role === 'admin')
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
          @php $latestDelivery = $order->deliveries->sortByDesc('created_at')->first(); @endphp
          <section class="card ord-action-bar">
            <p class="ord-action-title">
              <i class="bi bi-truck"></i> Delivery
            </p>
            <div class="ord-action-btns">
              @if ($latestDelivery)
                <span class="dlv-status-badge {{ $latestDelivery->status_css }}">{{ $latestDelivery->status_label }}</span>
                <a href="{{ route('deliveries.show', $latestDelivery) }}" class="ghost-btn">
                  <i class="bi bi-eye"></i> View Delivery
                </a>
                @if ($latestDelivery->status === 'pending')
                  <form method="POST" action="{{ route('deliveries.approve', $latestDelivery) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="primary-btn">
                      <i class="bi bi-check-lg"></i> Approve Delivery
                    </button>
                  </form>
                @elseif ($latestDelivery->status === 'approved')
                  <form method="POST" action="{{ route('deliveries.deliver', $latestDelivery) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="primary-btn" style="background:#1a6b45;">
                      <i class="bi bi-check2-all"></i> Mark as Delivered
                    </button>
                  </form>
                @endif
              @else
                <span style="font-size:0.85rem; color:var(--text-soft);">No delivery scheduled yet</span>
                <a href="{{ route('deliveries.index') }}" class="ghost-btn">
                  <i class="bi bi-truck"></i> View Deliveries
                </a>
              @endif
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
