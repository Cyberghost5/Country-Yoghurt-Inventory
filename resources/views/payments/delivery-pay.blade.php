<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pay Delivery {{ $allocation->delivery->delivery_number }} - Country Yoghurt</title>
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
            <h2>Submit Delivery Payment</h2>
            <p>For <strong>{{ $allocation->delivery->delivery_number }}</strong> — {{ $allocation->customer->name ?? '—' }}
              @if ($allocation->customer->shop_name)
                <span style="color:var(--text-soft);">({{ $allocation->customer->shop_name }})</span>
              @endif
            </p>
          </div>
          <div class="top-actions">
            <a href="{{ route('deliveries.show', $allocation->delivery_id) }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> Back to Delivery
            </a>
          </div>
        </header>

        @if ($errors->any())
          <div class="lp-error" style="margin-bottom:14px;">
            <i class="bi bi-exclamation-circle"></i>
            <ul style="margin:4px 0 0 16px; padding:0;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Delivery / Allocation Summary --}}
        @php
          $paid      = $allocation->paidAmount();
          $remaining = $allocation->remainingAmount();
        @endphp
        <section class="card" style="margin-bottom:16px; padding:16px 20px;">
          <h3 class="ord-section-title" style="margin-bottom:14px;">
            <i class="bi bi-truck"></i> Delivery Summary
          </h3>
          <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px,1fr)); gap:12px 24px; margin-bottom:16px;">
            <div>
              <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Delivery #</p>
              <p style="font-weight:600; margin:0;">{{ $allocation->delivery->delivery_number }}</p>
            </div>
            <div>
              <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Customer</p>
              <p style="font-weight:600; margin:0;">{{ $allocation->customer->name ?? '—' }}</p>
            </div>
            <div>
              <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Delivery Status</p>
              <p style="font-weight:600; margin:0;">{{ ucfirst($allocation->delivery->status) }}</p>
            </div>
            <div>
              <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Allocation Total</p>
              <p style="font-weight:600; margin:0;">&#8358;{{ number_format($allocation->total_amount, 2) }}</p>
            </div>
            <div>
              <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Already Paid</p>
              <p style="font-weight:600; color:#16a34a; margin:0;">&#8358;{{ number_format($paid, 2) }}</p>
            </div>
            <div>
              <p style="font-size:0.75rem; color:var(--text-soft); margin:0 0 2px;">Remaining Balance</p>
              <p style="font-weight:700; color:#dc2626; margin:0;">&#8358;{{ number_format($remaining, 2) }}</p>
            </div>
          </div>

          {{-- Items breakdown --}}
          <table style="width:100%; border-collapse:collapse; font-size:0.86rem;">
            <thead>
              <tr style="border-bottom:1px solid var(--border);">
                <th style="padding:5px 8px; text-align:left; font-weight:600; color:var(--text-soft);">Product</th>
                <th style="padding:5px 8px; text-align:right; font-weight:600; color:var(--text-soft);">Unit Price</th>
                <th style="padding:5px 8px; text-align:right; font-weight:600; color:var(--text-soft);">Qty</th>
                <th style="padding:5px 8px; text-align:right; font-weight:600; color:var(--text-soft);">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($allocation->items as $item)
                <tr style="border-bottom:1px solid #f0ece4;">
                  <td style="padding:6px 8px;">{{ $item->product_name }}</td>
                  <td style="padding:6px 8px; text-align:right;">&#8358;{{ number_format($item->unit_price, 2) }}</td>
                  <td style="padding:6px 8px; text-align:right;">{{ $item->quantity }}</td>
                  <td style="padding:6px 8px; text-align:right;">&#8358;{{ number_format($item->subtotal, 2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>

          {{-- Prior payments --}}
          @if ($allocation->payments->count())
            <div style="margin-top:14px; padding-top:12px; border-top:1px solid var(--border);">
              <p style="font-size:0.8rem; font-weight:600; color:var(--text-soft); margin:0 0 6px;">Prior Payments</p>
              @foreach ($allocation->payments as $pay)
                <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px dashed #e5e0d6; font-size:0.82rem;">
                  <span>{{ $pay->payment_number }} &mdash; {{ ucfirst(str_replace('_',' ', $pay->payment_method)) }}</span>
                  <span>
                    &#8358;{{ number_format($pay->amount, 2) }}
                    <span class="status-badge {{ $pay->status === 'approved' ? 'status-delivered' : ($pay->status === 'rejected' ? 'status-rejected' : 'status-pending') }}" style="font-size:0.7rem; padding:1px 6px; margin-left:6px;">{{ ucfirst($pay->status) }}</span>
                  </span>
                </div>
              @endforeach
            </div>
          @endif
        </section>

        {{-- Payment Form --}}
        <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="payment_type" value="delivery" />
          <input type="hidden" name="delivery_allocation_id" value="{{ $allocation->id }}" />

          <section class="card" style="margin-bottom:16px;">
            <h3 class="ord-section-title" style="margin-bottom:16px;">
              <i class="bi bi-credit-card"></i> Payment Details
            </h3>
            <div class="pay-form-grid">

              <div class="pay-form-field">
                <label class="inv-field-label" for="amount">
                  Amount Paid (&#8358;) <span class="req">*</span>
                </label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                       max="{{ number_format($remaining, 2, '.', '') }}"
                       class="inv-field-input {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                       value="{{ old('amount', number_format($remaining, 2, '.', '')) }}"
                       required />
                @error('amount') <span class="inv-field-error">{{ $message }}</span> @enderror
              </div>

              <div class="pay-form-field">
                <label class="inv-field-label" for="payment_method">
                  Payment Method <span class="req">*</span>
                </label>
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
                  <small style="font-weight:400; color:var(--text-soft);">(JPG, PNG or PDF &ndash; max 4 MB)</small>
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

            <div class="pay-form-field" style="margin-top:14px;">
              <label class="inv-field-label" for="notes">Additional Notes</label>
              <textarea id="notes" name="notes" rows="2" class="inv-field-input"
                        style="width:100%; resize:vertical;"
                        placeholder="Any extra information for the admin">{{ old('notes') }}</textarea>
            </div>
          </section>

          <div class="ord-submit-row">
            <a href="{{ route('deliveries.show', $allocation->delivery_id) }}" class="ghost-btn">Cancel</a>
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

      var uploadArea  = document.getElementById('uploadArea');
      var fileInput   = document.getElementById('proof');
      var placeholder = document.getElementById('uploadPlaceholder');
      var preview     = document.getElementById('uploadPreview');
      var fileNameEl  = document.getElementById('uploadFileName');
      var clearBtn    = document.getElementById('clearUpload');

      function showPreview(name) { placeholder.style.display='none'; preview.style.display='flex'; fileNameEl.textContent=name; }
      function clearFile()       { fileInput.value=''; placeholder.style.display=''; preview.style.display='none'; fileNameEl.textContent=''; }

      uploadArea.addEventListener('click', function(e) { if (e.target!==clearBtn && !clearBtn.contains(e.target)) fileInput.click(); });
      fileInput.addEventListener('change', function() { if (this.files.length) showPreview(this.files[0].name); });
      clearBtn.addEventListener('click', function(e) { e.stopPropagation(); clearFile(); });
      uploadArea.addEventListener('dragover', function(e) { e.preventDefault(); uploadArea.classList.add('drag-over'); });
      uploadArea.addEventListener('dragleave', function() { uploadArea.classList.remove('drag-over'); });
      uploadArea.addEventListener('drop', function(e) {
        e.preventDefault(); uploadArea.classList.remove('drag-over');
        var files = e.dataTransfer.files;
        if (files.length) { fileInput.files = files; showPreview(files[0].name); }
      });
    </script>
  </body>
</html>
