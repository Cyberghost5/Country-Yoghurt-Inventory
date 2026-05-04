<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Staff - Country Yoghurt</title>
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
        <header class="topbar"><div class="title-block"><h2>Staff Directory</h2><p>All staff records grouped by state.</p></div></header>

        <section class="card table-card">
          <div class="card-head"><div><h3>Staff List</h3><span>{{ $staff->count() }} staff accounts</span></div></div>
          <div class="table-scroll">
            <table>
              <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>State</th><th>LGA</th><th>Joined</th><th>Actions</th></tr></thead>
              <tbody>
                @forelse($staff as $row)
                  <tr>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->email }}</td>
                    <td>{{ $row->phone }}</td>
                    <td>{{ $row->state }}</td>
                    <td>{{ $row->lga }}</td>
                    <td>{{ optional($row->created_at)->format('d M Y') }}</td>
                    <td class="user-actions">
                      <a href="{{ route('users.edit', $row->id) }}" class="ua-btn ua-edit"><i class="bi bi-pencil"></i> Edit</a>
                      <form method="POST" action="{{ route('users.impersonate', $row->id) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="ua-btn ua-imp"><i class="bi bi-person-fill-gear"></i> Impersonate</button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7">No staff records found.</td></tr>
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
