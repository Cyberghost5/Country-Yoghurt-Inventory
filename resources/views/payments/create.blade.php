<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $order ? 'Pay for ' . $order->order_number : 'New Payment' }} - Country Yoghurt</title>
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
            <h2>{{ $order ? 'Submit Payment' : 'New Payment' }}</h2>
            <p>
              @if ($order)
                For order <strong>{{ $order->order_number }}</strong> - ?{{ number_format($order->total_amount, 2) }}
              @else
                Submit a payment with proof of transfer
              @endif
            </p>
          </div>
          <div class="top-actions">
            @if ($order)
              <a href="{{ route('orders.show', $order) }}" class="ghost-btn">
                <i class="bi bi-arrow-left"></i> Back to Order
              </a>
            @else
              <a href="{{ route('payments.index') }}" class="ghost-btn">
                <i class="bi bi-arrow-left"></i> All Payments
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

        <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" id="paymentForm">
          @csrf
          <input type="hidden" name="payment_type" value="order" />

          {{-- When order is pre-selected: skip all selectors, just show locked summary --}}
          @if ($order)
            <input type="hidden" name="order_id" value="{{ $order->id }}" />
            @php $preRemaining = $order->remainingAmount(); @endphp
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
                  <p style="font-weight:600; margin:0;">{{ $order->user->name ?? '-' }}</p>
                </div>
                <div>
                  <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Order Total</p>
                  <p style="font-weight:600; margin:0;">&#8358;{{ number_format($order->total_amount, 2) }}</p>
                </div>
                <div>
                  <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Already Paid</p>
                  <p style="font-weight:600; color:#16a34a; margin:0;">&#8358;{{ number_format($order->paidAmount(), 2) }}</p>
                </div>
                <div>
                  <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Remaining Balance</p>
                  <p style="font-weight:700; color:#dc2626; margin:0;">&#8358;{{ number_format($preRemaining, 2) }}</p>
                </div>
              </div>
            </section>

          @else
            {{-- Customer selector (staff / admin only, no pre-selected order) --}}
            @if ($user->isAdminOrStaff())
            <section class="card" style="margin-bottom: 16px;">
              <h3 class="ord-section-title" style="margin-bottom: 16px;">
                <i class="bi bi-person"></i> Customer
              </h3>
              <div class="pay-form-field">
                <label class="inv-field-label" for="customer_id">
                  Payment on behalf of <span class="req">*</span>
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
                        {{ $c->name }}{{ $c->shop_name ? ' - ' . $c->shop_name : '' }} ({{ $c->state }})
                      </option>
                    @endforeach
                  </select>
                @endif
              </div>
            </section>
            @endif

            <section class="card" style="margin-bottom: 16px;">
              <h3 class="ord-section-title" style="margin-bottom: 16px;">
                <i class="bi bi-bag"></i> Link to Order
                <small style="font-weight:400; font-size:0.78rem; color:var(--text-soft);">(optional)</small>
              </h3>
              <div class="pay-form-field">
                <label class="inv-field-label" for="order_id">Order</label>
                @if ($user->isAdminOrStaff())
                  <select id="order_id" name="order_id" class="inv-select">
                    <option value="">- Select a customer first -</option>
                  </select>
                @else
                  <select id="order_id" name="order_id" class="inv-select">
                    <option value="">- No specific order (use Reason below) -</option>
                    @foreach ($payableOrders as $o)
                      @php $remaining = round((float)$o->total_amount - $o->paidAmount(), 2); @endphp
                      <option value="{{ $o->id }}"
                              data-amount="{{ number_format((float)$o->total_amount, 2, '.', '') }}"
                              data-remaining="{{ number_format($remaining, 2, '.', '') }}"
                              data-number="{{ $o->order_number }}"
                              {{ (old('order_id') == $o->id) ? 'selected' : '' }}>
                        {{ $o->order_number }} - ₦{{ number_format($remaining, 2) }} remaining ({{ ucfirst($o->status) }})
                      </option>
                    @endforeach
                  </select>
                @endif
                @error('order_id')
                  <span class="inv-field-error">{{ $message }}</span>
                @enderror
              </div>
              <div id="orderSummary" style="display:none; margin-top:14px; padding:14px 16px; background:var(--surface); border:1px solid var(--border); border-radius:10px;">
                <div class="pay-summary-row">
                  <span>Order</span>
                  <strong id="summaryNumber">-</strong>
                </div>
                <div class="pay-summary-row">
                  <span>Order Total</span>
                  <strong id="summaryAmount">-</strong>
                </div>
                <div class="pay-summary-row pay-summary-total">
                  <span>Remaining Balance</span>
                  <strong id="summaryRemaining" style="color:var(--danger, #dc2626);">-</strong>
                </div>
              </div>
            </section>
          @endif

          <section class="card" style="margin-bottom: 16px;">
            <h3 class="ord-section-title" style="margin-bottom: 16px;">
              <i class="bi bi-credit-card"></i> Payment Details
            </h3>
            <div class="pay-form-grid">
              <div class="pay-form-field">
                <label class="inv-field-label" for="amount">Amount Paid (&#8358;) <span class="req">*</span></label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                       class="inv-field-input {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                       value="{{ old('amount', isset($preRemaining) ? number_format($preRemaining, 2, '.', '') : '') }}"
                       required />
                @error('amount') <span class="inv-field-error">{{ $message }}</span> @enderror
              </div>
              <div class="pay-form-field">
                <label class="inv-field-label" for="payment_method">Payment Method <span class="req">*</span></label>
                <select id="payment_method" name="payment_method"
                        class="inv-select {{ $errors->has('payment_method') ? 'is-invalid' : '' }}" required>
                  <option value="bank_transfer" {{ old('payment_method','bank_transfer')==='bank_transfer'?'selected':'' }}>Bank Transfer</option>
                  <option value="cash"          {{ old('payment_method')==='cash'?'selected':'' }}>Cash</option>
                  <option value="pos"           {{ old('payment_method')==='pos'?'selected':'' }}>POS</option>
                  <option value="mobile_money"  {{ old('payment_method')==='mobile_money'?'selected':'' }}>Mobile Money</option>
                </select>
                @error('payment_method') <span class="inv-field-error">{{ $message }}</span> @enderror
              </div>
              <div class="pay-form-field">
                <label class="inv-field-label" for="reference">Transaction Reference</label>
                <input type="text" id="reference" name="reference"
                       class="inv-field-input {{ $errors->has('reference') ? 'is-invalid' : '' }}"
                       value="{{ old('reference') }}" placeholder="e.g. bank teller no. or transaction ID" />
                @error('reference') <span class="inv-field-error">{{ $message }}</span> @enderror
              </div>
              <div class="pay-form-field">
                <label class="inv-field-label" for="proof">
                  Proof of Payment
                  <small style="font-weight:400; color:var(--text-soft);">(JPG, PNG or PDF - max 4 MB)</small>
                </label>
                <div class="pay-upload-area" id="uploadArea">
                  <input type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.pdf"
                         class="{{ $errors->has('proof') ? 'is-invalid' : '' }}" style="display:none;" />
                  <div class="pay-upload-placeholder" id="uploadPlaceholder">
                    <i class="bi bi-cloud-arrow-up" style="font-size:1.8rem;"></i>
                    <p>Click or drag to upload proof</p>
                    <small>JPG, PNG, PDF up to 4 MB</small>
                  </div>
                  <div class="pay-upload-preview" id="uploadPreview" style="display:none;">
                    <i class="bi bi-file-earmark-check" style="color:var(--primary);"></i>
                    <span id="uploadFileName"></span>
                    <button type="button" class="pay-upload-clear" id="clearUpload" title="Remove file">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                </div>
                @error('proof') <span class="inv-field-error">{{ $message }}</span> @enderror
              </div>
            </div>

            <div class="pay-form-field" style="margin-top: 14px;">
              <label class="inv-field-label" for="reason">
                Reason for Payment
                <span id="reasonReqMark" class="req">*</span>
                <small id="reasonHint" style="font-weight:400; color:var(--text-soft);">(required when no order is selected)</small>
              </label>
              <textarea id="reason" name="reason" rows="2"
                        class="inv-field-input {{ $errors->has('reason') ? 'is-invalid' : '' }}"
                        style="width:100%; resize:vertical;"
                        placeholder="e.g. Advance deposit, partial payment, other charge-">{{ old('reason') }}</textarea>
              @error('reason') <span class="inv-field-error">{{ $message }}</span> @enderror
            </div>

            <div class="pay-form-field" style="margin-top: 12px;">
              <label class="inv-field-label" for="notes">Additional Notes</label>
              <textarea id="notes" name="notes" rows="2" class="inv-field-input"
                        style="width:100%; resize:vertical;"
                        placeholder="Any extra information for the admin-">{{ old('notes') }}</textarea>
            </div>
          </section>

          <div class="ord-submit-row">
            @if ($order)
              <a href="{{ route('orders.show', $order) }}" class="ghost-btn">Cancel</a>
            @else
              <a href="{{ route('payments.index') }}" class="ghost-btn">Cancel</a>
            @endif
            <button type="submit" class="primary-btn">
              <i class="bi bi-send"></i> Submit Payment
            </button>
          </div>
        </form>

      </main>
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <script>
      const CY_AJAX_ORDERS_URL = '{{ route('ajax.customerOrders') }}';
      const CY_CSRF            = '{{ csrf_token() }}';
      const CY_IS_STAFF_ADMIN  = {{ $user->isAdminOrStaff() ? 'true' : 'false' }};
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
      var orderSelect   = document.getElementById('order_id');
      var amountInput   = document.getElementById('amount');
      var summary       = document.getElementById('orderSummary');
      var sumNumber     = document.getElementById('summaryNumber');
      var sumAmount     = document.getElementById('summaryAmount');
      var sumRemaining  = document.getElementById('summaryRemaining');
      var reasonInput   = document.getElementById('reason');
      var reasonReqMark = document.getElementById('reasonReqMark');
      var reasonHint    = document.getElementById('reasonHint');

      function populatePaymentOrders(orders) {
        if (!orderSelect) return;
        orderSelect.innerHTML = '<option value="">- No specific order (use Reason below) -</option>';
        if (orders && orders.length) {
          orders.forEach(function(o) {
            var opt = document.createElement('option');
            opt.value = o.id;
            opt.dataset.amount    = o.total_amount;
            opt.dataset.remaining = o.remaining;
            opt.dataset.number    = o.order_number;
            opt.textContent = o.order_number + ' - ₦' + o.remaining + ' remaining (' + o.status.charAt(0).toUpperCase() + o.status.slice(1) + ')';
            orderSelect.appendChild(opt);
          });
        }
        onOrderChange();
      }

      if (customerSelect) {
        customerSelect.addEventListener('change', function() {
          var cid = this.value;
          if (!cid) {
            if (orderSelect) orderSelect.innerHTML = '<option value="">- Select a customer first -</option>';
            if (summary) summary.style.display = 'none';
            return;
          }
          fetch(CY_AJAX_ORDERS_URL + '?customer_id=' + cid + '&filter=payable', {
            headers: { 'X-CSRF-TOKEN': CY_CSRF, 'Accept': 'application/json' }
          })
          .then(function(r) { return r.json(); })
          .then(function(orders) { populatePaymentOrders(orders); });
        });
      }

      function onOrderChange() {
        var opt = orderSelect ? orderSelect.options[orderSelect.selectedIndex] : null;
        if (opt && opt.value) {
          if (sumNumber)    sumNumber.textContent    = opt.dataset.number;
          if (sumAmount)    sumAmount.textContent    = '₦' + opt.dataset.amount;
          if (sumRemaining) sumRemaining.textContent = '₦' + (opt.dataset.remaining || opt.dataset.amount);
          if (summary)      summary.style.display   = '';
          var fillAmount = opt.dataset.remaining || opt.dataset.amount;
          if (amountInput && !amountInput.dataset.userEdited) amountInput.value = fillAmount;
          if (reasonInput)   reasonInput.required = false;
          if (reasonReqMark) reasonReqMark.style.display = 'none';
          if (reasonHint)    reasonHint.textContent = '(optional when an order is selected)';
        } else {
          if (summary) summary.style.display = 'none';
          if (reasonInput)   reasonInput.required = true;
          if (reasonReqMark) reasonReqMark.style.display = '';
          if (reasonHint)    reasonHint.textContent = '(required when no order is selected)';
        }
      }

      if (amountInput) {
        amountInput.addEventListener('input', function() { this.dataset.userEdited = '1'; });
      }
      if (orderSelect) {
        orderSelect.addEventListener('change', onOrderChange);
        onOrderChange();
      }

      var uploadArea  = document.getElementById('uploadArea');
      var fileInput   = document.getElementById('proof');
      var placeholder = document.getElementById('uploadPlaceholder');
      var preview     = document.getElementById('uploadPreview');
      var fileNameEl  = document.getElementById('uploadFileName');
      var clearBtn    = document.getElementById('clearUpload');

      function showPreview(name) { placeholder.style.display='none'; preview.style.display='flex'; fileNameEl.textContent=name; }
      function clearFile() { fileInput.value=''; placeholder.style.display=''; preview.style.display='none'; fileNameEl.textContent=''; }

      uploadArea.addEventListener('click', function(e) { if (e.target!==clearBtn && !clearBtn.contains(e.target)) fileInput.click(); });
      fileInput.addEventListener('change', function() { if (this.files.length) showPreview(this.files[0].name); });
      clearBtn.addEventListener('click', function(e) { e.stopPropagation(); clearFile(); });
      uploadArea.addEventListener('dragover', function(e) { e.preventDefault(); uploadArea.classList.add('drag-over'); });
      uploadArea.addEventListener('dragleave', function() { uploadArea.classList.remove('drag-over'); });
      uploadArea.addEventListener('drop', function(e) {
        e.preventDefault(); uploadArea.classList.remove('drag-over');
        var files=e.dataTransfer.files;
        if (files.length) { fileInput.files=files; showPreview(files[0].name); }
      });
    </script>
  </body>
</html>
