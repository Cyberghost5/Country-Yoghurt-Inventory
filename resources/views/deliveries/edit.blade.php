<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Delivery {{ $delivery->delivery_number }} - Country Yoghurt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <style>
      .dlv-customer-section { background:#fafaf8; border:1px solid #e5e0d6; border-radius:10px; padding:20px; margin-bottom:20px; }
      .dlv-customer-header  { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; }
      .dlv-customer-title   { font-weight:600; color:var(--text-main); }
      .dlv-items-table      { width:100%; border-collapse:collapse; margin-bottom:10px; }
      .dlv-items-table th   { font-size:0.78rem; font-weight:600; color:var(--text-soft); padding:6px 8px; border-bottom:1px solid #e5e0d6; text-align:left; }
      .dlv-items-table td   { padding:6px 8px; vertical-align:middle; }
      .dlv-items-table input{ width:100%; box-sizing:border-box; }
      .dlv-customer-total   { text-align:right; font-weight:600; color:var(--text-main); font-size:0.95rem; margin-top:8px; }
      .dlv-grand-total-bar  { background:#fffbf2; border:2px solid var(--accent); border-radius:8px; padding:14px 20px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; }
      .dlv-grand-label      { font-weight:600; font-size:1rem; }
      .dlv-grand-value      { font-size:1.25rem; font-weight:700; color:var(--accent); }
      .remove-btn           { background:none; border:none; color:#dc2626; cursor:pointer; padding:4px 6px; font-size:1rem; }
      .add-item-link        { font-size:0.82rem; color:var(--accent); cursor:pointer; background:none; border:none; padding:0; text-decoration:underline; }
      .add-customer-btn     { margin-bottom:16px; }
    </style>
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">@include('partials._sidebar')</aside>
      <main class="main-content">
        <header class="topbar">
          <div class="title-block">
            <h2>Edit {{ $delivery->delivery_number }}</h2>
            <p>Update the delivery details. Only pending deliveries can be edited.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('deliveries.show', $delivery) }}" class="ghost-btn"><i class="bi bi-arrow-left"></i> Back</a>
          </div>
        </header>

        @if ($errors->any())
          <div class="lp-error" style="margin-bottom:14px;">
            <ul style="margin:0; padding-left:18px;">
              @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
          </div>
        @endif
        @if (session('error'))
          <div class="lp-error" style="margin-bottom:14px;"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('deliveries.update', $delivery) }}" id="deliveryForm">
          @csrf
          @method('PUT')

          {{-- Top-level fields --}}
          <!-- <section class="card" style="padding:20px; margin-bottom:20px;">
            <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
              <div class="form-group">
                <label class="form-label">Date Created</label>
                <input type="date" name="scheduled_at" class="form-input" value="{{ old('scheduled_at', $delivery->scheduled_at?->format('Y-m-d')) }}" />
              </div>
              <div class="form-group">
                <label class="form-label">Notes <span style="color:#aaa;">(optional)</span></label>
                <input type="text" name="notes" class="form-input" placeholder="Any delivery notes…" value="{{ old('notes', $delivery->notes) }}" />
              </div>
            </div>
          </section> -->

          {{-- Customer allocations --}}
          <div id="customersWrapper"></div>

          <div class="dlv-grand-total-bar">
            <span class="dlv-grand-label">Grand Total</span>
            <span class="dlv-grand-value" id="grandTotal">&#8358;0.00</span>
          </div>

          <div style="display:flex; gap:12px;">
            <button type="button" id="addCustomerBtn" class="ghost-btn add-customer-btn">
              <i class="bi bi-plus-circle"></i> Add Customer
            </button>
            <button type="submit" class="primary-btn">
              <i class="bi bi-save"></i> Save Changes
            </button>
          </div>
        </form>
      </main>
    </div>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    @php
      $customersJson = $customers->map(function ($c) {
          return ['id' => $c->id, 'name' => $c->name, 'shop' => $c->shop_name ?? '', 'state' => $c->state ?? ''];
      })->values();

      $existingJson = $delivery->allocations->map(function ($a) {
          return [
              'customer_id'     => $a->customer_id,
              'customer_label'  => ($a->customer->name ?? '') . ($a->customer->shop_name ? ' (' . $a->customer->shop_name . ')' : '') . ($a->customer->state ? ' - ' . $a->customer->state : ''),
              'allocation_date' => $a->allocation_date?->format('Y-m-d') ?? '',
              'items'           => $a->items->map(function ($i) {
                  return ['product_name' => $i->product_name, 'unit_price' => $i->unit_price, 'quantity' => $i->quantity];
              })->values(),
          ];
      })->values();
    @endphp

    <script>
    (function() {
      /* Sidebar toggle */
      var sidebar  = document.getElementById('sidebar');
      var backdrop = document.getElementById('sidebarBackdrop');
      var toggle   = document.getElementById('sidebarToggle');
      var close    = document.getElementById('sidebarClose');
      function openSidebar()  { sidebar.classList.add('is-open'); backdrop.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
      function closeSidebar() { sidebar.classList.remove('is-open'); backdrop.classList.remove('is-open'); document.body.style.overflow = ''; }
      if (toggle)   toggle.addEventListener('click', openSidebar);
      if (close)    close.addEventListener('click', closeSidebar);
      if (backdrop) backdrop.addEventListener('click', closeSidebar);

      /* Customer data */
      var CUSTOMERS = @json($customersJson);

      /* Existing allocations pre-fill */
      var EXISTING = @json($existingJson);

      var customerIndex = 0;
      var usedIds = new Set();

      function getLabel(c) {
        return c.name + (c.shop ? ' (' + c.shop + ')' : '') + (c.state ? ' - ' + c.state : '');
      }

      function buildSearchableCustomer(ci) {
        return '<div class="customer-search-wrap" style="position:relative; margin-bottom:10px;">' +
          '<input type="text" class="form-input cust-search-input" placeholder="Search customer…" autocomplete="off" />' +
          '<input type="hidden" name="customers[' + ci + '][customer_id]" class="cust-id-hidden" required />' +
          '<div class="cust-dropdown" style="display:none; position:absolute; z-index:999; background:#fff; border:1px solid #ccc; border-radius:6px; width:100%; max-height:220px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,.12);"></div>' +
        '</div>';
      }

      function buildItemRow(ci, ii, prefill) {
        prefill = prefill || {};
        return '<tr data-item="' + ii + '">' +
          '<td><input type="text" name="customers[' + ci + '][items][' + ii + '][product_name]" class="form-input" placeholder="e.g. Yoghurt 500ml" required value="' + esc(prefill.product_name || '') + '" /></td>' +
          '<td><input type="number" name="customers[' + ci + '][items][' + ii + '][unit_price]" class="form-input item-price" placeholder="0.00" step="0.01" min="0.01" required value="' + (prefill.unit_price || '') + '" /></td>' +
          '<td><input type="number" name="customers[' + ci + '][items][' + ii + '][quantity]" class="form-input item-qty" placeholder="1" min="1" required value="' + (prefill.quantity || '') + '" /></td>' +
          '<td><input type="number" class="form-input item-subtotal" placeholder="0.00" readonly tabindex="-1" value="' + (prefill.unit_price && prefill.quantity ? (parseFloat(prefill.unit_price) * parseInt(prefill.quantity)).toFixed(2) : '') + '" /></td>' +
          '<td><button type="button" class="remove-btn remove-item-btn" title="Remove row"><i class="bi bi-trash3"></i></button></td>' +
          '</tr>';
      }

      function esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
      }

      function addCustomer(prefill) {
        prefill = prefill || {};
        var ci = customerIndex++;
        var items = prefill.items && prefill.items.length ? prefill.items : [{}];

        var section = document.createElement('div');
        section.className = 'dlv-customer-section';
        section.dataset.customer = ci;
        section.dataset.selectedId = prefill.customer_id ? String(prefill.customer_id) : '';

        var itemsHtml = '';
        items.forEach(function(item, idx) { itemsHtml += buildItemRow(ci, idx, item); });

        section.innerHTML =
          '<div class="dlv-customer-header">' +
            '<span class="dlv-customer-title">Customer ' + (ci + 1) + '</span>' +
            '<button type="button" class="remove-btn remove-customer-btn" title="Remove customer"><i class="bi bi-x-circle"></i> Remove</button>' +
          '</div>' +
          buildSearchableCustomer(ci) +
          '<div class="form-group" style="margin-bottom:10px;">' +
            '<label class="form-label" style="font-size:0.82rem;">Allocation Date <span style="color:#aaa;">(optional — defaults to scheduled date)</span></label>' +
            '<input type="date" name="customers[' + ci + '][allocation_date]" class="form-input" value="' + esc(prefill.allocation_date || '') + '" />' +
          '</div>' +
          '<table class="dlv-items-table" style="margin-top:14px;">' +
            '<thead><tr><th>Product Name</th><th>Unit Price (&#x20A6;)</th><th>Qty</th><th>Subtotal (&#x20A6;)</th><th></th></tr></thead>' +
            '<tbody class="items-body">' + itemsHtml + '</tbody>' +
          '</table>' +
          '<div style="display:flex; justify-content:space-between; align-items:center;">' +
            '<button type="button" class="add-item-link add-item-btn" data-ci="' + ci + '" data-ii="' + items.length + '"><i class="bi bi-plus"></i> Add row</button>' +
            '<div class="dlv-customer-total">Customer Total: <span class="customer-total-value">\u20A60.00</span></div>' +
          '</div>';

        document.getElementById('customersWrapper').appendChild(section);

        /* Pre-fill search input if editing existing */
        if (prefill.customer_id) {
          usedIds.add(parseInt(prefill.customer_id));
          var searchInput = section.querySelector('.cust-search-input');
          var hiddenInput = section.querySelector('.cust-id-hidden');
          searchInput.value = prefill.customer_label || '';
          hiddenInput.value = prefill.customer_id;
        }

        attachSectionEvents(section, ci, items.length);
        attachSearchable(section, ci);
        recalcCustomer(section);
        recalcGrand();
      }

      function attachSearchable(section, ci) {
        var input    = section.querySelector('.cust-search-input');
        var hidden   = section.querySelector('.cust-id-hidden');
        var dropdown = section.querySelector('.cust-dropdown');

        function renderDropdown(filter) {
          var selectedId = parseInt(section.dataset.selectedId) || 0;
          var filtered = CUSTOMERS.filter(function(c) {
            if (usedIds.has(c.id) && c.id !== selectedId) return false;
            if (!filter) return true;
            return getLabel(c).toLowerCase().indexOf(filter.toLowerCase()) !== -1;
          });
          if (!filtered.length) {
            dropdown.innerHTML = '<div style="padding:8px 12px; color:#888; font-size:0.85rem;">No customers found</div>';
          } else {
            dropdown.innerHTML = filtered.map(function(c) {
              return '<div class="cust-option" data-id="' + c.id + '" style="padding:8px 12px; cursor:pointer; font-size:0.88rem; border-bottom:1px solid #f0ede6;" ' +
                'onmouseover="this.style.background=\'#fef9ee\'" onmouseout="this.style.background=\'\'">' +
                getLabel(c) + '</div>';
            }).join('');
            dropdown.querySelectorAll('.cust-option').forEach(function(opt) {
              opt.addEventListener('mousedown', function(e) {
                e.preventDefault();
                var id = parseInt(opt.dataset.id);
                var c  = CUSTOMERS.find(function(x) { return x.id === id; });
                if (!c) return;
                var prev = parseInt(section.dataset.selectedId) || 0;
                if (prev && prev !== id) usedIds.delete(prev);
                usedIds.add(id);
                section.dataset.selectedId = id;
                hidden.value = id;
                input.value  = getLabel(c);
                dropdown.style.display = 'none';
              });
            });
          }
          dropdown.style.display = 'block';
        }

        input.addEventListener('focus', function() { renderDropdown(input.value); });
        input.addEventListener('input', function() { renderDropdown(input.value); hidden.value = ''; });
        input.addEventListener('blur', function() {
          setTimeout(function() { dropdown.style.display = 'none'; }, 150);
          if (!hidden.value) input.value = '';
        });
      }

      function attachSectionEvents(section, ci, nextIi) {
        section.querySelector('.remove-customer-btn').addEventListener('click', function() {
          var prev = parseInt(section.dataset.selectedId) || 0;
          if (prev) usedIds.delete(prev);
          section.remove();
          recalcGrand();
        });

        var addBtn = section.querySelector('.add-item-btn');
        addBtn.addEventListener('click', function() {
          var ii = parseInt(addBtn.dataset.ii);
          var tbody = section.querySelector('.items-body');
          tbody.insertAdjacentHTML('beforeend', buildItemRow(ci, ii));
          addBtn.dataset.ii = ii + 1;
          attachRowEvents(section);
          recalcGrand();
        });

        section.querySelector('.items-body').addEventListener('click', function(e) {
          var btn = e.target.closest('.remove-item-btn');
          if (!btn) return;
          var rows = section.querySelectorAll('.items-body tr');
          if (rows.length <= 1) return;
          btn.closest('tr').remove();
          recalcCustomer(section);
          recalcGrand();
        });

        attachRowEvents(section);
      }

      function attachRowEvents(section) {
        section.querySelectorAll('.items-body tr').forEach(function(row) {
          var price = row.querySelector('.item-price');
          var qty   = row.querySelector('.item-qty');
          var sub   = row.querySelector('.item-subtotal');
          if (price._bound) return;
          price._bound = true;
          function calcRow() {
            var p = parseFloat(price.value) || 0;
            var q = parseInt(qty.value) || 0;
            sub.value = (p * q).toFixed(2);
            recalcCustomer(section);
            recalcGrand();
          }
          price.addEventListener('input', calcRow);
          qty.addEventListener('input', calcRow);
        });
      }

      function recalcCustomer(section) {
        var total = 0;
        section.querySelectorAll('.item-subtotal').forEach(function(s) {
          total += parseFloat(s.value) || 0;
        });
        section.querySelector('.customer-total-value').textContent = '\u20A6' + total.toFixed(2);
      }

      function recalcGrand() {
        var grand = 0;
        document.querySelectorAll('.customer-total-value').forEach(function(el) {
          grand += parseFloat(el.textContent.replace('\u20A6','')) || 0;
        });
        document.getElementById('grandTotal').textContent = '\u20A6' + grand.toFixed(2);
      }

      document.getElementById('addCustomerBtn').addEventListener('click', function() { addCustomer(); });

      /* Pre-fill existing allocations */
      EXISTING.forEach(function(alloc) { addCustomer(alloc); });

      /* If no existing, start with one blank */
      if (!EXISTING.length) addCustomer();
    })();
    </script>
  </body>
</html>
