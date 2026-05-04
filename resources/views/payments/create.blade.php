<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $order ? 'Pay for ' . $order->order_number : 'New Payment' }} � Country Yoghurt</title>
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
                For order <strong>{{ $order->order_number }}</strong> � ?{{ number_format($order->total_amount, 2) }}
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

          <section class="card" style="margin-bottom: 16px;">
            <h3 class="ord-section-title" style="margin-bottom: 16px;">
              <i class="bi bi-bag"></i> Link to Order
              <small style="font-weight:400; font-size:0.78rem; color:var(--text-soft);">(optional)</small>
            </h3>
            <div class="pay-form-field">
              <label class="inv-field-label" for="order_id">Order</label>
              <select id="order_id" name="order_id" class="inv-select">
                <option value="">� No specific order (use Reason below) �</option>
                @foreach ($payableOrders as $o)
                  <option value="{{ $o->id }}"
                          data-amount="{{ number_format($o->total_amount, 2, '.', '') }}"
                          data-number="{{ $o->order_number }}"
                          {{ (old('order_id', $order?->id) == $o->id) ? 'selected' : '' }}>
                    {{ $o->order_number }} � ?{{ number_format($o->total_amount, 2) }} ({{ ucfirst($o->status) }})
                  </option>
                @endforeach
              </select>
              @error('order_id')
                <span class="inv-field-error">{{ $message }}</span>
              @enderror
            </div>
            <div id="orderSummary" style="display:none; margin-top:14px; padding:14px 16px; background:var(--surface); border:1px solid var(--border); border-radius:10px;">
              <div class="pay-summary-row">
                <span>Order</span>
                <strong id="summaryNumber">�</strong>
              </div>
              <div class="pay-summary-row pay-summary-total">
                <span>Total Due</span>
                <strong id="summaryAmount">�</strong>
              </div>
            </div>
          </section>

          <section class="card" style="margin-bottom: 16px;">
            <h3 class="ord-section-title" style="margin-bottom: 16px;">
              <i class="bi bi-credit-card"></i> Payment Details
            </h3>
            <div class="pay-form-grid">
              <div class="pay-form-field">
                <label class="inv-field-label" for="amount">Amount Paid (?) <span class="req">*</span></label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                       class="inv-field-input {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                       value="{{ old('amount', $order ? number_format($order->total_amount, 2, '.', '') : '') }}"
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
                  <small style="font-weight:400; color:var(--text-soft);">(JPG, PNG or PDF � max 4 MB)</small>
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
                        placeholder="e.g. Advance deposit, partial payment, other charge�">{{ old('reason') }}</textarea>
              @error('reason') <span class="inv-field-error">{{ $message }}</span> @enderror
            </div>

            <div class="pay-form-field" style="margin-top: 12px;">
              <label class="inv-field-label" for="notes">Additional Notes</label>
              <textarea id="notes" name="notes" rows="2" class="inv-field-input"
                        style="width:100%; resize:vertical;"
                        placeholder="Any extra information for the admin�">{{ old('notes') }}</textarea>
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

      var orderSelect   = document.getElementById('order_id');
      var amountInput   = document.getElementById('amount');
      var summary       = document.getElementById('orderSummary');
      var sumNumber     = document.getElementById('summaryNumber');
      var sumAmount     = document.getElementById('summaryAmount');
      var reasonInput   = document.getElementById('reason');
      var reasonReqMark = document.getElementById('reasonReqMark');
      var reasonHint    = document.getElementById('reasonHint');

      function onOrderChange() {
        var opt = orderSelect ? orderSelect.options[orderSelect.selectedIndex] : null;
        if (opt && opt.value) {
          if (sumNumber) sumNumber.textContent = opt.dataset.number;
          if (sumAmount) sumAmount.textContent = '?' + opt.dataset.amount;
          if (summary)  summary.style.display  = '';
          if (amountInput && !amountInput.dataset.userEdited) amountInput.value = opt.dataset.amount;
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
