<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Delivery - {{ $delivery->order->order_number ?? 'Detail' }} - Country Yoghurt</title>
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
            <h2>Delivery - {{ $delivery->order->order_number ?? '#' . $delivery->id }}</h2>
            <p>Scheduled on {{ $delivery->created_at->format('d M Y, g:ia') }}</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('deliveries.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> All Deliveries
            </a>
            @if ($delivery->order)
              <a href="{{ route('orders.show', $delivery->order) }}" class="ghost-btn">
                <i class="bi bi-bag"></i> View Order
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

        {{-- Meta grid --}}
        <div class="ord-meta-grid" style="margin-bottom: 16px;">
          <div class="ord-meta-card">
            <p class="ord-meta-label">Status</p>
            <span class="dlv-status-badge {{ $delivery->status_css }}">
              {{ $delivery->status_label }}
            </span>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Order</p>
            <p class="ord-meta-value">
              @if ($delivery->order)
                <a href="{{ route('orders.show', $delivery->order) }}" class="pay-order-link">
                  {{ $delivery->order->order_number }}
                </a>
              @else
                -
              @endif
            </p>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Customer</p>
            <p class="ord-meta-value">{{ $delivery->order->user->name ?? '-' }}</p>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Order Total</p>
            <p class="ord-meta-value ord-amount">
              ₦{{ number_format($delivery->order->total_amount ?? 0, 2) }}
            </p>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Scheduled By</p>
            <p class="ord-meta-value">{{ $delivery->staff->name ?? '-' }}</p>
            <small class="ord-meta-sub">Staff</small>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Scheduled For</p>
            <p class="ord-meta-value">
              {{ $delivery->scheduled_at ? $delivery->scheduled_at->format('d M Y') : '-' }}
            </p>
          </div>
          @if ($delivery->approved_at)
            <div class="ord-meta-card">
              <p class="ord-meta-label">Approved By</p>
              <p class="ord-meta-value">{{ $delivery->approvedBy->name ?? '-' }}</p>
              <small class="ord-meta-sub">{{ $delivery->approved_at->format('d M Y, g:ia') }}</small>
            </div>
          @endif
          @if ($delivery->delivered_at)
            <div class="ord-meta-card">
              <p class="ord-meta-label">Delivered At</p>
              <p class="ord-meta-value">{{ $delivery->delivered_at->format('d M Y, g:ia') }}</p>
            </div>
          @endif
        </div>

        {{-- Delivery address --}}
        <div class="card" style="margin-bottom: 16px; padding: 14px 16px;">
          <p class="ord-meta-label" style="margin-bottom: 6px;">
            <i class="bi bi-geo-alt"></i> Delivery Address
          </p>
          <p style="margin: 0; font-size: 0.88rem; color: var(--text-main);">
            {{ $delivery->delivery_address }}
          </p>
        </div>

        {{-- Notes --}}
        @if ($delivery->notes)
          <div class="card" style="margin-bottom: 16px; padding: 14px 16px;">
            <p class="ord-meta-label" style="margin-bottom: 6px;">
              <i class="bi bi-chat-left-text"></i> Notes
            </p>
            <p style="margin: 0; font-size: 0.88rem; color: var(--text-main);">
              {{ $delivery->notes }}
            </p>
          </div>
        @endif

        {{-- Order items summary --}}
        @if ($delivery->order && $delivery->order->items->count())
          <section class="card table-card" style="margin-bottom: 16px;">
            <p class="ord-meta-label" style="padding: 14px 16px 8px; margin: 0;">
              <i class="bi bi-list-ul"></i> Order Items
            </p>
            <div class="table-scroll">
              <table class="inv-table ord-table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Subtotal (₦)</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($delivery->order->items as $item)
                    <tr>
                      <td>{{ $item->product_name }}</td>
                      <td>{{ $item->quantity }}</td>
                      <td class="ord-amount">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                  @endforeach
                  <tr class="ord-total-row">
                    <td colspan="2"><strong>Total</strong></td>
                    <td class="ord-amount"><strong>₦{{ number_format($delivery->order->total_amount, 2) }}</strong></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        @endif

        {{-- Admin actions --}}
        @if ($user->role === 'admin')
          @if ($delivery->status === 'pending')
            <section class="card ord-action-bar">
              <p class="ord-action-title">
                <i class="bi bi-shield-check"></i> Admin Review
              </p>
              <div class="ord-action-btns">
                <form method="POST" action="{{ route('deliveries.approve', $delivery) }}" style="display:inline;">
                  @csrf
                  <button type="submit" class="primary-btn">
                    <i class="bi bi-check-lg"></i> Approve Delivery
                  </button>
                </form>
              </div>
            </section>
          @elseif ($delivery->status === 'approved')
            <section class="card ord-action-bar">
              <p class="ord-action-title">
                <i class="bi bi-truck"></i> Confirm Delivery
              </p>
              <div class="ord-action-btns">
                <form method="POST" action="{{ route('deliveries.deliver', $delivery) }}" style="display:inline;">
                  @csrf
                  <button type="submit" class="primary-btn" style="background:#1a6b45;">
                    <i class="bi bi-check2-all"></i> Mark as Delivered
                  </button>
                </form>
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
    </script>
  </body>
</html>
