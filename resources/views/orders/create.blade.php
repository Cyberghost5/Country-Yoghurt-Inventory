<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Place Order - Country Yoghurt</title>
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
            <h2>Place Order</h2>
            <p>Enter products and quantities. Orders are approved immediately on submission.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('orders.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
          </div>
        </header>

        <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
          @csrf

          {{-- ── Customer selector (staff / admin only) ── --}}
          @if ($user->isAdminOrStaff())
          <section class="card" style="margin-bottom: 16px;" id="customerSection">
            <h3 class="ord-section-title"><i class="bi bi-person"></i> Customer</h3>
            <div class="pay-form-field">
              <label class="inv-field-label" for="customer_id">
                Placing order on behalf of <span class="req">*</span>
              </label>
              <select id="customer_id" name="customer_id" class="inv-select {{ $errors->has('customer_id') ? 'is-invalid' : '' }}" required>
                <option value="">- Select customer -</option>
                @foreach ($customers as $c)
                  <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}{{ $c->shop_name ? ' - ' . $c->shop_name : '' }} ({{ $c->state }})
                  </option>
                @endforeach
              </select>
              @error('customer_id')
                <span class="inv-field-error">{{ $message }}</span>
              @enderror
            </div>
          </section>
          @endif

          {{-- Validation errors --}}
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

          {{-- ── Product rows ── --}}
          <section class="card" style="margin-bottom: 16px;">
            <div class="ord-items-header">
              <h3 class="ord-section-title"><i class="bi bi-cart3"></i> Order Items</h3>
              <button type="button" class="ghost-btn" id="addRowBtn">
                <i class="bi bi-plus-lg"></i> Add Product
              </button>
            </div>

            <div id="orderRows">
              {{-- First row always visible --}}
              <div class="ord-item-row" data-row="0">
                <div class="ord-item-grid">
                  <div class="ord-item-field ord-item-product">
                    <label>Product</label>
                    <select name="items[0][product_name]" class="inv-field-input product-name-select" required>
                      <option value="">- Select product -</option>
                    </select>
                  </div>

                  <div class="ord-item-field ord-item-price">
                    <label>Unit Price (₦)</label>
                    <input type="number" name="items[0][unit_price]" class="inv-field-input price-input"
                           min="0.01" step="0.01" placeholder="0.00" readonly style="background:#f5f3ef; cursor:default;" />
                  </div>

                  <div class="ord-item-field ord-item-qty">
                    <label>Qty</label>
                    <input type="number" name="items[0][quantity]"
                           class="inv-field-input qty-input" min="1" value="1" required />
                  </div>

                  <div class="ord-item-field ord-item-subtotal">
                    <label>Subtotal (₦)</label>
                    <input type="text" class="inv-field-input subtotal-display" readonly placeholder="-" />
                  </div>

                  <div class="ord-item-field ord-item-remove">
                    <label>&nbsp;</label>
                    <button type="button" class="inv-action-btn danger remove-row-btn" title="Remove row">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </section>

          {{-- ── Notes ── --}}
          <section class="card" style="margin-bottom: 16px;">
            <h3 class="ord-section-title"><i class="bi bi-chat-left-text"></i> Notes (optional)</h3>
            <textarea name="notes" rows="3" class="inv-field-input"
                      placeholder="Any special instructions or delivery notes…"
                      style="width:100%; resize:vertical;">{{ old('notes') }}</textarea>
          </section>

          {{-- ── Order summary ── --}}
          <section class="card ord-summary-card">
            <div class="ord-summary-row">
              <span class="ord-summary-label">Items</span>
              <span class="ord-summary-value" id="summaryCount">0</span>
            </div>
            <div class="ord-summary-row ord-summary-total">
              <span class="ord-summary-label">Total Amount</span>
              <span class="ord-summary-value" id="summaryTotal">₦0.00</span>
            </div>
            <div class="ord-submit-row">
              <a href="{{ route('orders.index') }}" class="ghost-btn">Cancel</a>
              <button type="submit" class="primary-btn" id="submitBtn">
                <i class="bi bi-send"></i> Submit Order
              </button>
            </div>
          </section>
        </form>

      </main>
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    @php
      $productsJson = $products->map(fn ($p) => [
          'id'    => $p->id,
          'name'  => $p->name,
          'unit'  => $p->unit,
          'price' => (float) $p->selling_price,
      ])->values();
    @endphp

    <script>
      /* ── Sidebar toggle ── */
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

      /* ── Order form logic ── */
      var PRODUCTS = @json($productsJson);

      function buildProductOptions(selected, excludeNames) {
        selected     = selected || '';
        excludeNames = excludeNames || new Set();
        var opts = '<option value="">\u2014 Select product \u2014</option>';
        PRODUCTS.forEach(function(p) {
          if (excludeNames.has(p.name) && p.name !== selected) return;
          var isSel = p.name === selected ? ' selected' : '';
          opts += '<option value="' + p.name + '" data-price="' + p.price + '"' + isSel + '>' +
                  p.name + ' (' + p.unit.charAt(0).toUpperCase() + p.unit.slice(1) + ')' +
                  '</option>';
        });
        return opts;
      }

      let rowIndex = 0;
      var usedProducts = {}; // { rowIndex: productName }

      function refreshAllOrderSelects() {
        var allUsed = new Set(Object.values(usedProducts));
        document.querySelectorAll('.ord-item-row').forEach(function(r) {
          var sel = r.querySelector('.product-name-select');
          if (!sel) return;
          var ri  = parseInt(r.dataset.row);
          var cur = usedProducts[ri] || '';
          sel.innerHTML = buildProductOptions(cur, allUsed);
        });
      }

      function addRow() {
        rowIndex++;
        const row = document.createElement('div');
        row.className = 'ord-item-row';
        row.dataset.row = rowIndex;
        row.innerHTML = `
          <div class="ord-item-grid">
            <div class="ord-item-field ord-item-product">
              <label>Product</label>
              <select name="items[${rowIndex}][product_name]" class="inv-field-input product-name-select" required>${buildProductOptions()}</select>
            </div>
            <div class="ord-item-field ord-item-price">
              <label>Unit Price (₦)</label>
              <input type="number" name="items[${rowIndex}][unit_price]" class="inv-field-input price-input" min="0.01" step="0.01" placeholder="0.00" readonly style="background:#f5f3ef; cursor:default;" />
            </div>
            <div class="ord-item-field ord-item-qty">
              <label>Qty</label>
              <input type="number" name="items[${rowIndex}][quantity]" class="inv-field-input qty-input" min="1" value="1" required />
            </div>
            <div class="ord-item-field ord-item-subtotal">
              <label>Subtotal (₦)</label>
              <input type="text" class="inv-field-input subtotal-display" readonly placeholder="-" />
            </div>
            <div class="ord-item-field ord-item-remove">
              <label>&nbsp;</label>
              <button type="button" class="inv-action-btn danger remove-row-btn" title="Remove row">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>`;
        document.getElementById('orderRows').appendChild(row);
        attachRowEvents(row);
        recalcTotal();
      }

      function attachRowEvents(row) {
        const priceInput = row.querySelector('.price-input');
        const qtyInput   = row.querySelector('.qty-input');
        const subEl      = row.querySelector('.subtotal-display');
        const removeBtn  = row.querySelector('.remove-row-btn');

        function updateRow() {
          const price = parseFloat(priceInput.value) || 0;
          const qty   = parseInt(qtyInput.value) || 0;
          const sub   = price * qty;
          subEl.value = sub > 0 ? formatNum(sub) : '';
          recalcTotal();
        }

        qtyInput.addEventListener('input', updateRow);

        removeBtn.addEventListener('click', function() {
          if (document.querySelectorAll('.ord-item-row').length <= 1) return;
          var ri = parseInt(row.dataset.row);
          delete usedProducts[ri];
          row.remove();
          recalcTotal();
          refreshAllOrderSelects();
        });

        const productSel = row.querySelector('.product-name-select');
        if (productSel) {
          productSel.addEventListener('change', function() {
            var ri      = parseInt(row.dataset.row);
            var opt     = productSel.options[productSel.selectedIndex];
            var newName = opt.value;
            // Free old selection, register new
            delete usedProducts[ri];
            if (newName) usedProducts[ri] = newName;
            // Set canonical price from option
            var price = parseFloat(opt.dataset.price) || 0;
            priceInput.value = price > 0 ? price.toFixed(2) : '';
            updateRow();
            refreshAllOrderSelects();
          });
        }
      }

      function recalcTotal() {
        const rows = document.querySelectorAll('.ord-item-row');
        let total = 0, count = 0;
        rows.forEach(function(r) {
          const sub = parseFloat(r.querySelector('.subtotal-display').value.replace(/,/g, '')) || 0;
          if (sub > 0) { total += sub; count++; }
        });
        document.getElementById('summaryCount').textContent = count + ' item' + (count !== 1 ? 's' : '');
        document.getElementById('summaryTotal').textContent = '\u20a6' + formatNum(total);
      }

      function formatNum(n) {
        return Number(n).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }

      // Only initialise order form logic when the form is present
      const addRowBtn = document.getElementById('addRowBtn');
      if (addRowBtn) {
        document.querySelectorAll('.ord-item-row').forEach(attachRowEvents);
        refreshAllOrderSelects();
        addRowBtn.addEventListener('click', addRow);
      }
    </script>
  </body>
</html>
