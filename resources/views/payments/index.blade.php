<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payments - Country Yoghurt</title>
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
            <h2>Payments</h2>
            <p>{{ $user->role === 'admin' ? 'All payment submissions' : 'Your payment submissions' }}</p>
          </div>
          @if (in_array($user->role, ['staff', 'customer'], true))
            <div class="top-actions">
              <a href="{{ route('payments.create') }}" class="primary-btn">
                <i class="bi bi-plus-lg"></i> New Payment
              </a>
            </div>
          @endif
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

        {{-- Tabs --}}
        <div class="ord-tabs" style="margin-bottom: 16px;">
          <a href="{{ route('payments.index') }}"
             class="ord-tab {{ !request('status') ? 'active' : '' }}">
            All <span class="ord-tab-count">{{ $counts['all'] }}</span>
          </a>
          <a href="{{ route('payments.index', ['status' => 'pending']) }}"
             class="ord-tab {{ request('status') === 'pending' ? 'active' : '' }}">
            Pending <span class="ord-tab-count pending">{{ $counts['pending'] }}</span>
          </a>
          <a href="{{ route('payments.index', ['status' => 'approved']) }}"
             class="ord-tab {{ request('status') === 'approved' ? 'active' : '' }}">
            Approved <span class="ord-tab-count approved">{{ $counts['approved'] }}</span>
          </a>
          <a href="{{ route('payments.index', ['status' => 'rejected']) }}"
             class="ord-tab {{ request('status') === 'rejected' ? 'active' : '' }}">
            Rejected <span class="ord-tab-count rejected">{{ $counts['rejected'] }}</span>
          </a>
        </div>

        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>#</th>
                  @if ($user->role === 'admin')
                    <th>Submitted By</th>
                  @endif
                  <th>Order</th>
                  <th>Amount (₦)</th>
                  <th>Method</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($payments as $payment)
                  <tr>
                    <td><span class="ord-number">{{ $payment->id }}</span></td>
                    @if ($user->role === 'admin')
                      <td>
                        <span class="ord-placer">{{ $payment->user->name ?? '-' }}</span>
                        <small class="ord-role">{{ ucfirst($payment->user->role ?? '') }}</small>
                      </td>
                    @endif
                    <td>
                      @if ($payment->order)
                        <a href="{{ route('orders.show', $payment->order_id) }}" class="pay-order-link">
                          {{ $payment->order->order_number }}
                        </a>
                      @else
                        <span style="color:var(--text-soft); font-size:0.82rem;">- standalone -</span>
                      @endif
                    </td>
                    <td class="ord-amount">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->method_label }}</td>
                    <td>{{ $payment->created_at->format('d M Y, g:ia') }}</td>
                    <td>
                      <span class="pay-status-badge {{ $payment->status_css }}">
                        {{ $payment->status_label }}
                      </span>
                    </td>
                    <td>
                      <a href="{{ route('payments.show', $payment) }}" class="inv-action-btn" title="View">
                        <i class="bi bi-eye"></i>
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="{{ $user->role === 'admin' ? 8 : 7 }}" class="inv-empty-row">
                      <i class="bi bi-credit-card" style="font-size:1.4rem;"></i>
                      <p>No payments found.</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          @if ($payments->hasPages())
            <div class="ord-pagination">
              {{ $payments->links() }}
            </div>
          @endif
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
