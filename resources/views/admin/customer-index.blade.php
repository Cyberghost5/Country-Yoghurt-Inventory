<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Customers - Country Yoghurt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">
        @include('partials._sidebar')
      </aside>

      <main class="main-content">
        <header class="topbar"><div class="title-block"><h2>Customer Directory</h2><p>All customer records with shop and location details.</p></div></header>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.customers.index') }}" class="card" style="padding:16px 20px; margin-bottom:16px;">
          <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
            <div style="flex:1; min-width:180px;">
              <label style="font-size:0.78rem; color:var(--text-soft); display:block; margin-bottom:4px;">Search</label>
              <input type="text" name="search" value="{{ $search ?? '' }}"
                     placeholder="Name, shop, phone…"
                     style="width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:6px; font-size:0.88rem;" />
            </div>
            <div style="min-width:160px;">
              <label style="font-size:0.78rem; color:var(--text-soft); display:block; margin-bottom:4px;">State</label>
              <select name="state" style="width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:6px; font-size:0.88rem;">
                <option value="">All States</option>
                @foreach ($states as $s)
                  <option value="{{ $s }}" {{ ($state ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
              </select>
            </div>
            <div style="display:flex; align-items:center; gap:6px; padding-bottom:2px;">
              <input type="checkbox" name="debt" id="debtFilter" value="1" {{ !empty($debtOnly) ? 'checked' : '' }}
                     style="width:16px; height:16px; cursor:pointer;" />
              <label for="debtFilter" style="font-size:0.88rem; cursor:pointer; white-space:nowrap;">Has Debt</label>
            </div>
            <button type="submit" class="primary-btn" style="padding:8px 18px;">Filter</button>
            @if (!empty($search) || !empty($state) || !empty($debtOnly))
              <a href="{{ route('admin.customers.index') }}" class="ghost-btn" style="padding:8px 14px;">Clear</a>
            @endif
          </div>
        </form>

        <section class="card table-card">
          <div class="card-head"><div><h3>Customer List</h3><span>{{ $customers->count() }} customer account{{ $customers->count() === 1 ? '' : 's' }}</span></div></div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Shop</th>
                  <th>Phone</th>
                  <th>Address</th>
                  <th>LGA</th>
                  <th>State</th>
                  <th>Debt</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($customers as $row)
                  <tr>
                    <td>{{ $row->shop_name }}</td>
                    <td>{{ $row->phone }}</td>
                    <td>{{ $row->address }}</td>
                    <td>{{ $row->lga }}</td>
                    <td>{{ $row->state }}</td>
                    <td>
                      @if ($row->outstanding_debt > 0)
                        <span style="color:#c0392b;font-weight:600;">₦{{ number_format($row->outstanding_debt, 2) }}</span>
                      @else
                        <span style="color:#2a9d54;">₦0.00</span>
                      @endif
                    </td>
                    <td class="user-actions">
                      <a href="{{ route('customers.show', $row->id) }}" class="ua-btn ua-view"><i class="bi bi-eye"></i> View</a>
                      @if ($user->isAdmin())
                        <a href="{{ route('users.edit', $row->id) }}" class="ua-btn ua-edit"><i class="bi bi-pencil"></i> Edit</a>
                        <form method="POST" action="{{ route('users.impersonate', $row->id) }}" style="display:inline">
                          @csrf
                          <button type="submit" class="ua-btn ua-imp"><i class="bi bi-person-fill-gear"></i> Impersonate</button>
                        </form>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="8">No customer records found.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
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
