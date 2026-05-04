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
            <p>Select products and quantities. Your order will be reviewed by the admin.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('orders.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
          </div>
        </header>

        @if ($products->isEmpty())
          <div class="card" style="padding: 32px; text-align: center; color: var(--text-soft);">
            <i class="bi bi-box-seam" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
            <p>No products are currently available. Please check back later.</p>
          </div>
        @else

        <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
          @csrf

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
                    <select name="items[0][product_id]" class="inv-select product-select" required>
                      <option value="">- Select product -</option>
                      @foreach ($products as $product)
                        <option value="{{ $product->id }}"
                                data-price="{{ $product->selling_price }}"
                                data-unit="{{ $product->unit }}"
                                data-stock="{{ $product->quantity }}">
                          {{ $product->name }} ({{ ucfirst($product->unit) }})
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="ord-item-field ord-item-qty">
                    <label>Qty</label>
                    <input type="number" name="items[0][quantity]"
                           class="inv-field-input qty-input" min="1" value="1" required />
                  </div>

                  <div class="ord-item-field ord-item-price">
                    <label>Unit Price (₦)</label>
                    <input type="text" class="inv-field-input price-display" readonly placeholder="-" />
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
                <div class="ord-stock-hint"></div>
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
        @endif

      </main>
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- Products JSON for JS price lookups (JSON_HEX_TAG prevents </script> injection) --}}
    @if (!$products->isEmpty())
    @php
      $productsJson = json_encode(
        $products->keyBy('id')->map(fn($p) => [
          'name'  => $p->name,
          'price' => (float) $p->selling_price,
          'unit'  => $p->unit,
          'stock' => $p->quantity,
        ])->all(),
        JSON_HEX_TAG
      );
    @endphp
    <script>
      const CY_PRODUCTS = {!! $productsJson !!};
    </script>
    @endif

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
      let rowIndex = 0;

      function buildOptionsHTML(selectedId) {
        let html = '<option value="">- Select product -</option>';
        for (const [id, p] of Object.entries(CY_PRODUCTS)) {
          const sel = String(selectedId) === String(id) ? ' selected' : '';
          html += `<option value="${id}" data-price="${p.price}" data-unit="${p.unit}" data-stock="${p.stock}"${sel}>${p.name} (${p.unit.charAt(0).toUpperCase() + p.unit.slice(1)})</option>`;
        }
        return html;
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
              <select name="items[${rowIndex}][product_id]" class="inv-select product-select" required>
                ${buildOptionsHTML(null)}
              </select>
            </div>
            <div class="ord-item-field ord-item-qty">
              <label>Qty</label>
              <input type="number" name="items[${rowIndex}][quantity]" class="inv-field-input qty-input" min="1" value="1" required />
            </div>
            <div class="ord-item-field ord-item-price">
              <label>Unit Price (₦)</label>
              <input type="text" class="inv-field-input price-display" readonly placeholder="-" />
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
          <div class="ord-stock-hint"></div>`;
        document.getElementById('orderRows').appendChild(row);
        attachRowEvents(row);
        recalcTotal();
      }

      function updateSubmitBtn() {
        const rows = document.querySelectorAll('.ord-item-row');
        let allOk = true;
        rows.forEach(function(r) {
          if (r.dataset.stockOk === 'false') allOk = false;
        });
        const btn = document.getElementById('submitBtn');
        if (btn) {
          btn.disabled = !allOk;
          btn.style.opacity = allOk ? '' : '0.55';
          btn.style.cursor  = allOk ? '' : 'not-allowed';
        }
      }

      function attachRowEvents(row) {
        const select    = row.querySelector('.product-select');
        const qtyInput  = row.querySelector('.qty-input');
        const priceEl   = row.querySelector('.price-display');
        const subEl     = row.querySelector('.subtotal-display');
        const hintEl    = row.querySelector('.ord-stock-hint');
        const removeBtn = row.querySelector('.remove-row-btn');
        let   stockTimer = null;

        function setHint(msg, cls) {
          hintEl.textContent = msg;
          hintEl.className   = 'ord-stock-hint' + (cls ? ' ' + cls : '');
        }

        function checkStock() {
          const productId = select.value;
          const qty = parseInt(qtyInput.value) || 0;

          if (!productId || qty < 1) {
            setHint('', '');
            row.dataset.stockOk = 'true';
            updateSubmitBtn();
            return;
          }

          clearTimeout(stockTimer);
          stockTimer = setTimeout(function() {
            setHint('Checking availability…', 'checking');

            fetch(`/orders/stock-check?product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(qty)}`, {
              headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
              if (data.available) {
                setHint('\u2713 ' + data.stock + ' in stock', 'ok');
                row.dataset.stockOk = 'true';
              } else {
                setHint('\u2717 Only ' + data.stock + ' available - reduce quantity', 'err');
                row.dataset.stockOk = 'false';
              }
              updateSubmitBtn();
            })
            .catch(function() {
              setHint('', '');
              row.dataset.stockOk = 'true';
              updateSubmitBtn();
            });
          }, 400);
        }

        function updateRow() {
          const opt   = select.options[select.selectedIndex];
          const price = opt && opt.value ? parseFloat(opt.dataset.price || 0) : 0;
          const qty   = parseInt(qtyInput.value) || 0;
          const sub   = price * qty;
          priceEl.value = price > 0 ? formatNum(price) : '';
          subEl.value   = sub > 0   ? formatNum(sub)   : '';
          recalcTotal();
          checkStock();
        }

        select.addEventListener('change', updateRow);
        qtyInput.addEventListener('input', updateRow);

        removeBtn.addEventListener('click', function() {
          const allRows = document.querySelectorAll('.ord-item-row');
          if (allRows.length <= 1) return;
          row.remove();
          recalcTotal();
          updateSubmitBtn();
        });
      }

      function recalcTotal() {
        const rows = document.querySelectorAll('.ord-item-row');
        let total = 0;
        let count = 0;
        rows.forEach(function(row) {
          const sub = parseFloat(row.querySelector('.subtotal-display').value.replace(/,/g, '')) || 0;
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
        addRowBtn.addEventListener('click', addRow);
      }
    </script>
  </body>
</html>
