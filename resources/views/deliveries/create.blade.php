<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $order ? 'Delivery for ' . $order->order_number : 'Schedule Delivery' }} - Country Yoghurt</title>
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
            <h2>{{ $order ? 'Schedule Delivery' : 'Schedule Delivery' }}</h2>
            <p>
              @if ($order)
                For order <strong>{{ $order->order_number }}</strong>
                &middot; {{ $order->user->name ?? '' }}
              @else
                Select an approved order and provide delivery details
              @endif
            </p>
          </div>
          <div class="top-actions">
            @if ($order)
              <a href="{{ route('orders.show', $order) }}" class="ghost-btn">
                <i class="bi bi-arrow-left"></i> Back to Order
              </a>
            @else
              <a href="{{ route('deliveries.index') }}" class="ghost-btn">
                <i class="bi bi-arrow-left"></i> All Deliveries
              </a>
            @endif
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

          @if ($order)
            {{-- ── Locked order summary when coming from order page ── --}}
            <input type="hidden" name="order_id" value="{{ $order->id }}" />
            @php
              $preAddress = collect([
                $order->user->address ?? null,
                $order->user->lga     ?? null,
                $order->user->state   ?? null,
              ])->filter()->implode(', ');
            @endphp
            <section class="card" style="margin-bottom: 16px; padding: 16px 20px;">
              <h3 class="ord-section-title" style="margin-bottom: 14px;">
                <i class="bi bi-bag-check"></i> Order Summary
              </h3>
              <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap:12px 24px;">
                <div>
                  <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Order Number</p>
                  <p style="font-weight:600; margin:0;">{{ $order->order_number }}</p>
                </div>
                <div>
                  <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Customer</p>
                  <p style="font-weight:600; margin:0;">{{ $order->user->name ?? '—' }}</p>
                </div>
                <div>
                  <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Order Total</p>
                  <p style="font-weight:600; margin:0;">&#8358;{{ number_format($order->total_amount, 2) }}</p>
                </div>
                @if ($order->user->state)
                  <div>
                    <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">State</p>
                    <p style="font-weight:600; margin:0;">{{ $order->user->state }}</p>
                  </div>
                @endif
              </div>
            </section>

          @else
            {{-- ── Customer selector (staff + admin, no pre-selected order) ── --}}
            <section class="card" style="margin-bottom: 16px;">
              <h3 class="ord-section-title" style="margin-bottom: 16px;">
                <i class="bi bi-person"></i> Customer
              </h3>
              <div class="pay-form-field">
                <label class="inv-field-label" for="customer_id">
                  Customer <span class="req">*</span>
                </label>
                @if ($customers->isEmpty())
                  <p style="font-size:0.85rem; color:var(--text-soft); margin:0;">
                    No customers found {{ $user->role === 'staff' ? 'in your state' : '' }}.
                  </p>
                @else
                  <select id="customer_id" name="customer_id" class="inv-select" required>
                    <option value="">- Select customer -</option>
                    @foreach ($customers as $c)
                      <option value="{{ $c->id }}">
                        {{ $c->name }}{{ $c->shop_name ? ' — ' . $c->shop_name : '' }} ({{ $c->state }})
                      </option>
                    @endforeach
                  </select>
                @endif
              </div>
            </section>

            {{-- ── Order selection ── --}}
            <section class="card" style="margin-bottom: 16px;">
              <h3 class="ord-section-title" style="margin-bottom: 16px;">
                <i class="bi bi-bag"></i> Order
              </h3>
              <div class="pay-form-field">
                <label class="inv-field-label" for="order_id">Select Order <span class="req">*</span></label>
                @if ($approvedOrders->isEmpty() && $customers->isEmpty())
                  <p style="font-size:0.85rem; color:var(--text-soft); margin:0;">
                    No approved orders are currently available for delivery scheduling.
                  </p>
                  <input type="hidden" name="order_id" value="" />
                @else
                  <select id="order_id" name="order_id"
                          class="inv-select {{ $errors->has('order_id') ? 'is-invalid' : '' }}" required>
                    <option value="">- Select a customer first -</option>
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
          @endif

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
                        placeholder="{{ $order ? 'Enter delivery address…' : 'Select a customer to auto-fill, or type the address…' }}">{{ old('delivery_address', $order ? ($preAddress ?? '') : '') }}</textarea>
              @error('delivery_address')
                <span class="inv-field-error">{{ $message }}</span>
              @enderror
            </div>

            <div class="pay-form-grid">
              <div class="pay-form-field">
                <label class="inv-field-label" for="scheduled_at">Scheduled Date <span class="req">*</span></label>
                <input type="date" id="scheduled_at" name="scheduled_at"
                       class="inv-field-input {{ $errors->has('scheduled_at') ? 'is-invalid' : '' }}"
                       value="{{ old('scheduled_at') }}"
                       min="{{ now()->toDateString() }}" required />
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
            @if ($order)
              <a href="{{ route('orders.show', $order) }}" class="ghost-btn">Cancel</a>
            @else
              <a href="{{ route('deliveries.index') }}" class="ghost-btn">Cancel</a>
            @endif
            <button type="submit" class="primary-btn" {{ (!$order && $approvedOrders->isEmpty() && $customers->isEmpty()) ? 'disabled' : '' }}>
              <i class="bi bi-truck"></i> Schedule Delivery
            </button>
          </div>
        </form>
      </main>
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    @if (!$order)
    @php
      $ordersMapJson = json_encode(
        $approvedOrders->groupBy('user_id')->map(fn ($orders) => $orders->map(fn ($o) => [
          'id'           => $o->id,
          'order_number' => $o->order_number,
          'total_amount' => number_format((float)$o->total_amount, 2),
        ])->values()->all())->all(),
        JSON_HEX_TAG
      );
      $customerAddressMap = json_encode(
        $customers->mapWithKeys(fn ($c) => [
          $c->id => collect([$c->address, $c->lga, $c->state])
                      ->filter()
                      ->implode(', ')
        ])->all(),
        JSON_HEX_TAG
      );
    @endphp
    <script>
      const CY_ORDERS_BY_CUSTOMER    = {!! $ordersMapJson !!};
      const CY_AJAX_ORDERS_URL       = '{{ route('ajax.customerOrders') }}';
      const CY_CSRF                  = '{{ csrf_token() }}';
      const CY_CUSTOMER_ADDRESSES    = {!! $customerAddressMap !!};
    </script>

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

      var customerSelect = document.getElementById('customer_id');
      var orderSelect    = document.getElementById('order_id');
      var summary        = document.getElementById('orderSummary');
      var sumNumber      = document.getElementById('summaryNumber');
      var sumAmount      = document.getElementById('summaryAmount');

      function populateOrders(orders) {
        if (!orderSelect) return;
        orderSelect.innerHTML = '<option value="">- Select an order -</option>';
        if (!orders || !orders.length) {
          orderSelect.innerHTML = '<option value="">No approved orders for this customer</option>';
          return;
        }
        orders.forEach(function(o) {
          var opt = document.createElement('option');
          opt.value = o.id;
          opt.dataset.number = o.order_number;
          opt.dataset.amount = o.total_amount;
          opt.textContent    = o.order_number + ' - ₦' + o.total_amount;
          orderSelect.appendChild(opt);
        });
      }

      if (customerSelect) {
        customerSelect.addEventListener('change', function() {
          var cid = this.value;

          // Auto-fill delivery address from saved customer address
          var addrField = document.getElementById('delivery_address');
          if (addrField && !addrField.dataset.userEdited) {
            addrField.value = (cid && CY_CUSTOMER_ADDRESSES[cid]) ? CY_CUSTOMER_ADDRESSES[cid] : '';
          }

          if (!cid) {
            if (orderSelect) orderSelect.innerHTML = '<option value="">- Select a customer first -</option>';
            if (summary) summary.style.display = 'none';
            return;
          }
          // Fetch via AJAX so it's always fresh
          fetch(CY_AJAX_ORDERS_URL + '?customer_id=' + cid + '&filter=approved', {
            headers: { 'X-CSRF-TOKEN': CY_CSRF, 'Accept': 'application/json' }
          })
          .then(function(r) { return r.json(); })
          .then(function(orders) { populateOrders(orders); onOrderChange(); });
        });
      }

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

      // Mark address as user-edited so switching customer doesn't overwrite manual input
      var addrField = document.getElementById('delivery_address');
      if (addrField) {
        addrField.addEventListener('input', function() { this.dataset.userEdited = '1'; });
      }
    </script>
    @endif
  </body>
</html>
