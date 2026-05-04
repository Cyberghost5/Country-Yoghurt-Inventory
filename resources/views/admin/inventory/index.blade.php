<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inventory - Country Yoghurt</title>
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

        {{-- Top bar --}}
        <header class="topbar">
          <div class="title-block">
            <h2>Inventory</h2>
            <p>Manage all products, stock levels and pricing.</p>
          </div>
          <div class="top-actions">
            <button class="primary-btn" id="openAddModal">
              <i class="bi bi-plus-lg"></i> Add Product
            </button>
          </div>
        </header>

        {{-- Flash message --}}
        @if (session('status'))
          <div class="lp-success" style="margin-bottom: 14px;">
            <i class="bi bi-check-circle"></i> {{ session('status') }}
          </div>
        @endif

        {{-- KPI cards --}}
        <section class="kpi-grid" style="margin-bottom: 16px;">
          <article class="stat-card">
            <div class="stat-top">
              <span class="mini-icon"><i class="bi bi-box-seam"></i></span>
              <span class="trend-pill">All</span>
            </div>
            <h4 class="stat-value">{{ $stats['total_products'] }}</h4>
            <p class="stat-unit">SKUs</p>
            <small class="stat-label">Total Products</small>
          </article>

          <article class="stat-card info">
            <div class="stat-top">
              <span class="mini-icon"><i class="bi bi-layers"></i></span>
              <span class="trend-pill">Live</span>
            </div>
            <h4 class="stat-value">{{ number_format($stats['total_units']) }}</h4>
            <p class="stat-unit">units</p>
            <small class="stat-label">Total Stock Units</small>
          </article>

          <article class="stat-card warn">
            <div class="stat-top">
              <span class="mini-icon"><i class="bi bi-exclamation-triangle"></i></span>
              <span class="trend-pill danger">Alert</span>
            </div>
            <h4 class="stat-value">{{ $stats['low_stock'] }}</h4>
            <p class="stat-unit">products</p>
            <small class="stat-label">Low Stock</small>
          </article>

          <article class="stat-card danger">
            <div class="stat-top">
              <span class="mini-icon"><i class="bi bi-x-circle"></i></span>
              <span class="trend-pill danger">Critical</span>
            </div>
            <h4 class="stat-value">{{ $stats['out_of_stock'] }}</h4>
            <p class="stat-unit">products</p>
            <small class="stat-label">Out of Stock</small>
          </article>
        </section>

        {{-- Filters --}}
        <section class="card inv-filter-bar">
          <form method="GET" action="{{ route('admin.inventory.index') }}" class="inv-filters">
            <label class="search-wrap inv-search" for="inv_search">
              <i class="bi bi-search search-icon"></i>
              <input id="inv_search" type="search" name="search"
                     placeholder="Search name, SKU, supplier…"
                     value="{{ request('search') }}" />
            </label>

            <select name="category" class="inv-select">
              <option value="">All Categories</option>
              <option value="yoghurt"      {{ request('category') === 'yoghurt'      ? 'selected' : '' }}>Yoghurt</option>
              <option value="accessories"  {{ request('category') === 'accessories'  ? 'selected' : '' }}>Accessories</option>
              <option value="packaging"    {{ request('category') === 'packaging'    ? 'selected' : '' }}>Packaging</option>
              <option value="others"       {{ request('category') === 'others'       ? 'selected' : '' }}>Others</option>
            </select>

            <select name="status" class="inv-select">
              <option value="">All Status</option>
              <option value="in_stock"     {{ request('status') === 'in_stock'     ? 'selected' : '' }}>In Stock</option>
              <option value="low_stock"    {{ request('status') === 'low_stock'    ? 'selected' : '' }}>Low Stock</option>
              <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
            </select>

            <button type="submit" class="ghost-btn">Apply</button>

            @if (request('search') || request('category') || request('status'))
              <a href="{{ route('admin.inventory.index') }}" class="ghost-btn">Clear</a>
            @endif
          </form>

          <span class="inv-count">{{ $products->count() }} product{{ $products->count() !== 1 ? 's' : '' }}</span>
        </section>

        {{-- Products table --}}
        <section class="card table-card">
          <div class="table-scroll">
            <table class="inv-table">
              <thead>
                <tr>
                  <th>SKU</th>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Flavor / Size</th>
                  <th>Unit</th>
                  <th>Cost (₦)</th>
                  <th>Price (₦)</th>
                  <th>Qty</th>
                  <th>Reorder</th>
                  <th>Status</th>
                  <th>Supplier</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($products as $product)
                  <tr>
                    <td><span class="inv-sku">{{ $product->sku ?? '-' }}</span></td>
                    <td>
                      <span class="inv-name">{{ $product->name }}</span>
                      @if ($product->notes)
                        <small class="inv-note">{{ Str::limit($product->notes, 40) }}</small>
                      @endif
                    </td>
                    <td><span class="inv-cat-badge inv-cat-{{ $product->category }}">{{ ucfirst($product->category) }}</span></td>
                    <td>
                      @if ($product->flavor || $product->size_label)
                        {{ $product->flavor ? ucfirst($product->flavor) : '' }}
                        {{ $product->flavor && $product->size_label ? ' · ' : '' }}
                        {{ $product->size_label ?? '' }}
                      @else
                        <span class="inv-empty">-</span>
                      @endif
                    </td>
                    <td>{{ ucfirst($product->unit) }}</td>
                    <td>{{ number_format($product->cost_price, 2) }}</td>
                    <td>{{ number_format($product->selling_price, 2) }}</td>
                    <td class="inv-qty {{ $product->stock_status }}">{{ $product->quantity }}</td>
                    <td>{{ $product->reorder_level }}</td>
                    <td>
                      @php
                        $statusLabels = [
                          'in_stock'     => ['In Stock',  'inv-status-ok'],
                          'low_stock'    => ['Low Stock', 'inv-status-warn'],
                          'out_of_stock' => ['Out of Stock', 'inv-status-danger'],
                        ];
                        [$label, $cls] = $statusLabels[$product->stock_status];
                      @endphp
                      <span class="inv-status-badge {{ $cls }}">{{ $label }}</span>
                    </td>
                    <td>{{ $product->supplier_name ?? '-' }}</td>
                    <td>
                      <div class="inv-actions">
                        {{-- Adjust stock --}}
                        <button class="inv-action-btn"
                                title="Adjust Stock"
                                onclick="openAdjust({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->quantity }})">
                          <i class="bi bi-plus-slash-minus"></i>
                        </button>

                        {{-- Edit --}}
                        <button class="inv-action-btn"
                                title="Edit"
                                onclick="openEdit({{ json_encode($product->toArray()) }})">
                          <i class="bi bi-pencil"></i>
                        </button>

                        {{-- Delete (admin only) --}}
                        @if ($user->role === 'admin')
                          <button class="inv-action-btn danger"
                                  title="Delete"
                                  onclick="openDelete({{ $product->id }}, '{{ addslashes($product->name) }}')">
                            <i class="bi bi-trash"></i>
                          </button>
                        @endif
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="12" class="inv-empty-row">
                      <i class="bi bi-inbox" style="font-size:1.4rem;"></i>
                      <p>No products found. Add your first product.</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>

      </main>
    </div>

    {{-- ═══════════════════════════════════════════════════
         MODAL: Add Product
    ════════════════════════════════════════════════════ --}}
    <div class="inv-modal-overlay" id="addModal">
      <div class="inv-modal">
        <div class="inv-modal-head">
          <h3><i class="bi bi-plus-circle"></i> Add Product</h3>
          <button class="inv-modal-close" onclick="closeModal('addModal')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form method="POST" action="{{ route('admin.inventory.store') }}" novalidate>
          @csrf
          @include('admin.inventory._product_form', ['product' => null])
          <div class="inv-modal-footer">
            <button type="button" class="ghost-btn" onclick="closeModal('addModal')">Cancel</button>
            <button type="submit" class="primary-btn">Save Product</button>
          </div>
        </form>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         MODAL: Edit Product
    ════════════════════════════════════════════════════ --}}
    <div class="inv-modal-overlay" id="editModal">
      <div class="inv-modal">
        <div class="inv-modal-head">
          <h3><i class="bi bi-pencil-square"></i> Edit Product</h3>
          <button class="inv-modal-close" onclick="closeModal('editModal')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form method="POST" id="editForm" novalidate>
          @csrf
          @method('PUT')
          @include('admin.inventory._product_form', ['product' => null, 'edit' => true])
          <div class="inv-modal-footer">
            <button type="button" class="ghost-btn" onclick="closeModal('editModal')">Cancel</button>
            <button type="submit" class="primary-btn">Update Product</button>
          </div>
        </form>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         MODAL: Adjust Stock
    ════════════════════════════════════════════════════ --}}
    <div class="inv-modal-overlay" id="adjustModal">
      <div class="inv-modal inv-modal-sm">
        <div class="inv-modal-head">
          <h3><i class="bi bi-plus-slash-minus"></i> Adjust Stock</h3>
          <button class="inv-modal-close" onclick="closeModal('adjustModal')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form method="POST" id="adjustForm" novalidate>
          @csrf
          <div class="inv-modal-body">
            <p class="inv-adjust-name" id="adjustProductName"></p>
            <p class="inv-adjust-current">Current quantity: <strong id="adjustCurrentQty"></strong></p>

            <label class="inv-field-label" for="adjustment">
              Adjustment (+ to add, – to remove)
            </label>
            <input type="number" name="adjustment" id="adjustment"
                   class="inv-field-input" placeholder="e.g. 50 or -10" required />

            <p class="inv-adjust-hint">
              <i class="bi bi-info-circle"></i>
              Enter a positive number to add stock or a negative number to remove stock.
            </p>
          </div>
          <div class="inv-modal-footer">
            <button type="button" class="ghost-btn" onclick="closeModal('adjustModal')">Cancel</button>
            <button type="submit" class="primary-btn">Apply Adjustment</button>
          </div>
        </form>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         MODAL: Delete Confirm
    ════════════════════════════════════════════════════ --}}
    <div class="inv-modal-overlay" id="deleteModal">
      <div class="inv-modal inv-modal-sm">
        <div class="inv-modal-head">
          <h3><i class="bi bi-trash" style="color:var(--danger)"></i> Delete Product</h3>
          <button class="inv-modal-close" onclick="closeModal('deleteModal')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="inv-modal-body">
          <p>You are about to permanently delete <strong id="deleteProductName"></strong>.</p>
          <p class="inv-adjust-hint" style="color:var(--danger)">
            <i class="bi bi-exclamation-triangle"></i> This action cannot be undone.
          </p>
        </div>

        <form method="POST" id="deleteForm">
          @csrf
          @method('DELETE')
          <div class="inv-modal-footer">
            <button type="button" class="ghost-btn" onclick="closeModal('deleteModal')">Cancel</button>
            <button type="submit" class="primary-btn" style="background:var(--danger)">Delete</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      /* ── Modal helpers ── */
      function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
      }
      function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = '';
      }

      // Close on backdrop click
      document.querySelectorAll('.inv-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
          if (e.target === overlay) closeModal(overlay.id);
        });
      });

      /* ── Add modal ── */
      document.getElementById('openAddModal').addEventListener('click', () => openModal('addModal'));

      /* ── Edit modal ── */
      function openEdit(product) {
        const form = document.getElementById('editForm');
        form.action = '/admin/inventory/' + product.id;

        const fields = ['name','sku','category','flavor','size_label','unit',
                        'cost_price','selling_price','quantity','reorder_level',
                        'supplier_name','notes'];
        fields.forEach(field => {
          const el = form.querySelector('[name="' + field + '"]');
          if (!el) return;
          el.value = product[field] ?? '';
        });

        openModal('editModal');
      }

      /* ── Adjust modal ── */
      function openAdjust(id, name, currentQty) {
        document.getElementById('adjustProductName').textContent = name;
        document.getElementById('adjustCurrentQty').textContent = currentQty;
        document.getElementById('adjustForm').action = '/admin/inventory/' + id + '/adjust';
        document.getElementById('adjustment').value = '';
        openModal('adjustModal');
      }

      /* ── Delete modal ── */
      function openDelete(id, name) {
        document.getElementById('deleteProductName').textContent = name;
        document.getElementById('deleteForm').action = '/admin/inventory/' + id;
        openModal('deleteModal');
      }
    </script>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <script>
      (function() {
        var sidebar = document.getElementById('sidebar');
        var backdrop = document.getElementById('sidebarBackdrop');
        var toggle = document.getElementById('sidebarToggle');
        var close = document.getElementById('sidebarClose');
        function openSidebar() { sidebar.classList.add('is-open'); backdrop.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
        function closeSidebar() { sidebar.classList.remove('is-open'); backdrop.classList.remove('is-open'); document.body.style.overflow = ''; }
        if (toggle) toggle.addEventListener('click', openSidebar);
        if (close) close.addEventListener('click', closeSidebar);
        if (backdrop) backdrop.addEventListener('click', closeSidebar);
      })();
    </script>
  </body>
</html>
