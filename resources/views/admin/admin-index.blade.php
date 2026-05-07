<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Admins - Country Yoghurt</title>
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
            <h2>Admin Directory</h2>
            <p>All administrator accounts on the system.</p>
          </div>
          <div class="topbar-actions">
            @if ($user->role === 'super_admin')
              <a href="{{ route('users.create.admin') }}" class="primary-btn">
                <i class="bi bi-shield-plus"></i> Add Admin
              </a>
            @endif
          </div>
        </header>

        @if (session('status'))
          <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <section class="card table-card">
          <div class="card-head">
            <div>
              <h3>Admin List</h3>
              <span>{{ $admins->count() }} admin {{ Str::plural('account', $admins->count()) }}</span>
            </div>
          </div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>State</th>
                  <th>LGA</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($admins as $row)
                  <tr>
                    <td>
                      {{ $row->name }}
                      @if ($row->id === $user->id)
                        <span style="font-size:0.72rem;background:#fef3c7;color:#92400e;padding:1px 7px;border-radius:20px;margin-left:5px;font-weight:600;">You</span>
                      @endif
                    </td>
                    <td>{{ $row->email }}</td>
                    <td>{{ $row->phone ?? '-' }}</td>
                    <td>{{ $row->state ?? '-' }}</td>
                    <td>{{ $row->lga ?? '-' }}</td>
                    <td>{{ optional($row->created_at)->format('d M Y') }}</td>
                    <td class="user-actions">
                      <a href="{{ route('users.edit', $row->id) }}" class="ua-btn ua-edit"><i class="bi bi-pencil"></i> Edit</a>
                      @if ($row->id !== $user->id)
                        <form method="POST" action="{{ route('users.impersonate', $row->id) }}" style="display:inline">
                          @csrf
                          <button type="submit" class="ua-btn ua-imp"><i class="bi bi-person-fill-gear"></i> Impersonate</button>
                        </form>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" style="text-align:center;color:#888;padding:28px 0;">No admin records found.</td></tr>
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
