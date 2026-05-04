<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Schedule Delivery - Country Yoghurt</title>
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
            <h2>Schedule Delivery</h2>
            <p>Select an approved order and provide delivery details</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('deliveries.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> All Deliveries
            </a>
          </div>
        </header>

        @if ($errors->any())
          <div class="lp-error" style="margin-bottom: 14px;">
            <i class="bi bi-exclamation-circle"></i>
            <ul style="margin: 4px 0 0 16px; padding: 0;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('deliveries.store') }}" id="deliveryForm">
          @csrf

          {{-- Order selection --}}
          <section class="card" style="margin-bottom: 16px;">
            <h3 class="ord-section-title" style="margin-bottom: 16px;">
              <i class="bi bi-bag"></i> Order
            </h3>
            <div class="pay-form-field">
              <label class="inv-field-label" for="order_id">Select Order <span class="req">*</span></label>
              @if ($approvedOrders->isEmpty())
                <p style="font-size:0.85rem; color:var(--text-soft); margin:0;">
                  No approved orders are currently available for delivery scheduling.
                </p>
                <input type="hidden" name="order_id" value="" />
              @else
                <select id="order_id" name="order_id"
                        class="inv-select {{ $errors->has('order_id') ? 'is-invalid' : '' }}" required>
                  <option value="">- Select an order -</option>
                  @foreach ($approvedOrders as $o)
                    <option value="{{ $o->id }}"
                            data-number="{{ $o->order_number }}"
                            data-amount="{{ number_format($o->total_amount, 2) }}"
                            {{ (old('order_id', $order?->id) == $o->id) ? 'selected' : '' }}>
                      {{ $o->order_number }} - ₦{{ number_format($o->total_amount, 2) }}
                    </option>
                  @endforeach
                </select>
              @endif
              @error('order_id')
                <span class="inv-field-error">{{ $message }}</span>
              @enderror
            </div>

            {{-- Order summary card (shown by JS) --}}
            <div id="orderSummary" style="display:none; margin-top:14px; padding:14px 16px; background:var(--surface); border:1px solid var(--border); border-radius:10px;">
              <div class="pay-summary-row">
                <span>Order</span>
                <strong id="summaryNumber">-</strong>
              </div>
              <div class="pay-summary-row pay-summary-total">
                <span>Total</span>
                <strong id="summaryAmount">-</strong>
              </div>
            </div>
          </section>

          {{-- Delivery details --}}
          <section class="card" style="margin-bottom: 16px;">
            <h3 class="ord-section-title" style="margin-bottom: 16px;">
              <i class="bi bi-truck"></i> Delivery Details
            </h3>
            <div class="pay-form-field" style="margin-bottom: 14px;">
              <label class="inv-field-label" for="delivery_address">
                Delivery Address <span class="req">*</span>
              </label>
              <textarea id="delivery_address" name="delivery_address" rows="2"
                        class="inv-field-input {{ $errors->has('delivery_address') ? 'is-invalid' : '' }}"
                        style="width:100%; resize:vertical;" required
                        placeholder="Full delivery address…">{{ old('delivery_address') }}</textarea>
              @error('delivery_address')
                <span class="inv-field-error">{{ $message }}</span>
              @enderror
            </div>

            <div class="pay-form-grid">
              <div class="pay-form-field">
                <label class="inv-field-label" for="scheduled_at">Scheduled Date</label>
                <input type="date" id="scheduled_at" name="scheduled_at"
                       class="inv-field-input {{ $errors->has('scheduled_at') ? 'is-invalid' : '' }}"
                       value="{{ old('scheduled_at') }}"
                       min="{{ now()->toDateString() }}" />
                @error('scheduled_at')
                  <span class="inv-field-error">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <div class="pay-form-field" style="margin-top: 12px;">
              <label class="inv-field-label" for="notes">Notes</label>
              <textarea id="notes" name="notes" rows="2"
                        class="inv-field-input {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                        style="width:100%; resize:vertical;"
                        placeholder="Any instructions or remarks for this delivery…">{{ old('notes') }}</textarea>
              @error('notes')
                <span class="inv-field-error">{{ $message }}</span>
              @enderror
            </div>
          </section>

          <div class="ord-submit-row">
            <a href="{{ route('deliveries.index') }}" class="ghost-btn">Cancel</a>
            <button type="submit" class="primary-btn" {{ $approvedOrders->isEmpty() ? 'disabled' : '' }}>
              <i class="bi bi-truck"></i> Schedule Delivery
            </button>
          </div>
        </form>
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

      var orderSelect  = document.getElementById('order_id');
      var summary      = document.getElementById('orderSummary');
      var sumNumber    = document.getElementById('summaryNumber');
      var sumAmount    = document.getElementById('summaryAmount');

      function onOrderChange() {
        var opt = orderSelect ? orderSelect.options[orderSelect.selectedIndex] : null;
        if (opt && opt.value) {
          if (sumNumber) sumNumber.textContent = opt.dataset.number;
          if (sumAmount) sumAmount.textContent  = '₦' + opt.dataset.amount;
          if (summary)  summary.style.display  = '';
        } else {
          if (summary) summary.style.display = 'none';
        }
      }

      if (orderSelect) {
        orderSelect.addEventListener('change', onOrderChange);
        onOrderChange();
      }
    </script>
  </body>
</html>
