<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment #{{ $payment->id }} - Country Yoghurt</title>
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
            <h2>Payment #{{ $payment->id }}</h2>
            <p>Submitted {{ $payment->created_at->format('d M Y, g:ia') }}</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('payments.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> All Payments
            </a>
            <a href="{{ route('orders.show', $payment->order) }}" class="ghost-btn">
              <i class="bi bi-bag"></i> View Order
            </a>
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
            <span class="pay-status-badge {{ $payment->status_css }}">
              {{ $payment->status_label }}
            </span>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Amount</p>
            <p class="ord-meta-value ord-amount">₦{{ number_format($payment->amount, 2) }}</p>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Method</p>
            <p class="ord-meta-value">{{ $payment->method_label }}</p>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Order</p>
            <p class="ord-meta-value">
              @if ($payment->order)
                <a href="{{ route('orders.show', $payment->order) }}" class="pay-order-link">
                  {{ $payment->order->order_number }}
                </a>
              @else
                <span style="color:var(--text-soft); font-size:0.85rem;">No order linked</span>
              @endif
            </p>
          </div>
          <div class="ord-meta-card">
            <p class="ord-meta-label">Submitted By</p>
            <p class="ord-meta-value">{{ $payment->user->name ?? '-' }}</p>
            <small class="ord-meta-sub">{{ ucfirst($payment->user->role ?? '') }}</small>
          </div>
          @if ($payment->reference)
            <div class="ord-meta-card">
              <p class="ord-meta-label">Reference</p>
              <p class="ord-meta-value" style="font-family: 'Courier New', monospace; font-size: 0.85rem;">
                {{ $payment->reference }}
              </p>
            </div>
          @endif
          @if ($payment->reviewed_at)
            <div class="ord-meta-card">
              <p class="ord-meta-label">Reviewed By</p>
              <p class="ord-meta-value">{{ $payment->reviewer->name ?? '-' }}</p>
              <small class="ord-meta-sub">{{ $payment->reviewed_at->format('d M Y, g:ia') }}</small>
            </div>
          @endif
        </div>

        {{-- Reason --}}
        @if ($payment->reason)
          <div class="card" style="margin-bottom: 16px; padding: 14px 16px;">
            <p class="ord-meta-label" style="margin-bottom: 6px;"><i class="bi bi-info-circle"></i> Reason for Payment</p>
            <p style="margin: 0; font-size: 0.88rem; color: var(--text-main);">{{ $payment->reason }}</p>
          </div>
        @endif

        {{-- Notes --}}
        @if ($payment->notes)
          <div class="card" style="margin-bottom: 16px; padding: 14px 16px;">
            <p class="ord-meta-label" style="margin-bottom: 6px;"><i class="bi bi-chat-left-text"></i> Notes</p>
            <p style="margin: 0; font-size: 0.88rem; color: var(--text-main);">{{ $payment->notes }}</p>
          </div>
        @endif

        {{-- Rejection reason --}}
        @if ($payment->rejection_reason)
          <div class="card" style="margin-bottom: 16px; padding: 14px 16px; border-left: 4px solid var(--danger);">
            <p class="ord-meta-label" style="margin-bottom: 6px; color: var(--danger);">
              <i class="bi bi-exclamation-circle"></i> Rejection Reason
            </p>
            <p style="margin: 0; font-size: 0.88rem; color: var(--text-main);">{{ $payment->rejection_reason }}</p>
          </div>
        @endif

        {{-- Proof of payment --}}
        @if ($payment->proof_path)
          <section class="card" style="margin-bottom: 16px; padding: 16px 18px;">
            <p class="ord-meta-label" style="margin-bottom: 12px;">
              <i class="bi bi-file-earmark-image"></i> Proof of Payment
            </p>
            @php
              $ext = strtolower(pathinfo($payment->proof_path, PATHINFO_EXTENSION));
            @endphp
            @if (in_array($ext, ['jpg', 'jpeg', 'png']))
              <a href="{{ Storage::disk('public')->url($payment->proof_path) }}" target="_blank" rel="noopener">
                <img src="{{ Storage::disk('public')->url($payment->proof_path) }}"
                     alt="Proof of payment"
                     class="pay-proof-img" />
              </a>
            @else
              <a href="{{ Storage::disk('public')->url($payment->proof_path) }}"
                 target="_blank" rel="noopener"
                 class="ghost-btn" style="display:inline-flex; align-items:center; gap:6px;">
                <i class="bi bi-file-earmark-pdf"></i> View PDF Proof
              </a>
            @endif
          </section>
        @else
          <div class="card" style="margin-bottom: 16px; padding: 14px 16px; color: var(--text-soft); font-size: 0.85rem;">
            <i class="bi bi-file-earmark-x"></i> No proof of payment uploaded.
          </div>
        @endif

        {{-- Admin actions --}}
        @if ($user->role === 'admin' && $payment->status === 'pending')
          <section class="card ord-action-bar">
            <p class="ord-action-title">
              <i class="bi bi-shield-check"></i> Review Payment
            </p>
            <div class="ord-action-btns">
              <form method="POST" action="{{ route('payments.approve', $payment) }}" style="display:inline;">
                @csrf
                <button type="submit" class="primary-btn">
                  <i class="bi bi-check-lg"></i> Approve Payment
                </button>
              </form>
              <button type="button" class="ghost-btn danger-ghost" id="openRejectBtn">
                <i class="bi bi-x-lg"></i> Reject Payment
              </button>
            </div>
          </section>

          {{-- Reject modal --}}
          <div class="inv-modal-overlay" id="rejectModal">
            <div class="inv-modal inv-modal-sm">
              <div class="inv-modal-head">
                <h3><i class="bi bi-x-circle" style="color:var(--danger)"></i> Reject Payment</h3>
                <button class="inv-modal-close" onclick="closeRejectModal()">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
              <form method="POST" action="{{ route('payments.reject', $payment) }}">
                @csrf
                <div class="inv-modal-body">
                  <p style="margin-bottom: 12px; font-size:0.88rem;">
                    You can optionally provide a reason for rejecting this payment.
                  </p>
                  <label class="inv-field-label" for="rejection_reason">Reason (optional)</label>
                  <textarea name="rejection_reason" id="rejection_reason"
                            class="inv-field-input" rows="3"
                            placeholder="e.g. Amount mismatch, unclear proof…"
                            style="resize:vertical; width:100%;"></textarea>
                </div>
                <div class="inv-modal-footer">
                  <button type="button" class="ghost-btn" onclick="closeRejectModal()">Cancel</button>
                  <button type="submit" class="primary-btn" style="background:var(--danger)">Reject</button>
                </div>
              </form>
            </div>
          </div>
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
