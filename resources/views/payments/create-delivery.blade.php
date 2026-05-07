<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pay for Delivery - Country Yoghurt</title>
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
      <aside class="sidebar" id="sidebar">@include('partials._sidebar')</aside>

      <main class="main-content">
        <header class="topbar">
          <div class="title-block">
            <h2>Pay for a Delivery</h2>
            <p>Select the delivery allocation you want to pay for</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('payments.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> Payments
            </a>
          </div>
        </header>

        @if (session('status'))
          <div class="lp-success" style="margin-bottom:14px;">
            <i class="bi bi-check-circle"></i> {{ session('status') }}
          </div>
        @endif

        @if ($payableAllocations->isEmpty())
          <div class="card" style="padding:40px 24px; text-align:center; color:var(--text-soft);">
            <i class="bi bi-truck" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
            <p style="font-size:0.95rem; margin:0;">You have no outstanding delivery balances at the moment.</p>
            <a href="{{ route('payments.index') }}" class="ghost-btn" style="display:inline-flex;margin-top:16px;">
              <i class="bi bi-arrow-left"></i> Back to Payments
            </a>
          </div>
        @else
          <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px;">
            @foreach ($payableAllocations as $alloc)
              @php
                $paid      = $alloc->payments->where('status','approved')->sum('amount');
                $remaining = max(0, $alloc->total_amount - $paid);
              @endphp
              <div class="card" style="padding:20px 22px; display:flex; flex-direction:column; gap:14px;">
                {{-- Delivery header --}}
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                  <div>
                    <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Delivery</p>
                    <p style="font-weight:700; font-size:1rem; margin:0; font-family:'Courier New',monospace;">
                      {{ $alloc->delivery->delivery_number ?? '-' }}
                    </p>
                  </div>
                  <span class="status-badge status-approved" style="font-size:0.73rem;">
                    {{ ucfirst($alloc->delivery->status ?? '') }}
                  </span>
                </div>

                {{-- Staff --}}
                @if ($alloc->delivery?->staff)
                  <p style="font-size:0.82rem; color:var(--text-soft); margin:0;">
                    <i class="bi bi-person-badge"></i> Delivered by {{ $alloc->delivery->staff->name }}
                  </p>
                @endif

                {{-- Financials --}}
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                  <div style="background:#f7f4ee; border-radius:8px; padding:10px 12px;">
                    <p style="font-size:0.7rem; color:var(--text-soft); margin:0 0 2px;">Total</p>
                    <p style="font-weight:700; font-size:0.88rem; margin:0;">₦{{ number_format($alloc->total_amount, 2) }}</p>
                  </div>
                  <div style="background:#f7f4ee; border-radius:8px; padding:10px 12px;">
                    <p style="font-size:0.7rem; color:var(--text-soft); margin:0 0 2px;">Paid</p>
                    <p style="font-weight:700; font-size:0.88rem; margin:0; color:#16a34a;">₦{{ number_format($paid, 2) }}</p>
                  </div>
                  <div style="background:#fff0f0; border-radius:8px; padding:10px 12px;">
                    <p style="font-size:0.7rem; color:var(--text-soft); margin:0 0 2px;">Owed</p>
                    <p style="font-weight:700; font-size:0.88rem; margin:0; color:#dc2626;">₦{{ number_format($remaining, 2) }}</p>
                  </div>
                </div>

                {{-- Items summary --}}
                @if ($alloc->items->count())
                  <div style="font-size:0.8rem; color:var(--text-soft);">
                    <i class="bi bi-box-seam"></i>
                    {{ $alloc->items->count() }} item{{ $alloc->items->count() > 1 ? 's' : '' }}:
                    {{ $alloc->items->pluck('product_name')->filter()->implode(', ') }}
                  </div>
                @endif

                {{-- Date --}}
                @if ($alloc->delivery?->scheduled_at)
                  <p style="font-size:0.78rem; color:var(--text-soft); margin:0;">
                    <i class="bi bi-calendar3"></i> {{ $alloc->delivery->scheduled_at->format('d M Y') }}
                  </p>
                @endif

                <a href="{{ route('deliveries.allocation.pay', $alloc) }}"
                   class="primary-btn" style="text-align:center; margin-top:auto;">
                  <i class="bi bi-cash-coin"></i> Pay ₦{{ number_format($remaining, 2) }}
                </a>
              </div>
            @endforeach
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
    </script>
  </body>
</html>
