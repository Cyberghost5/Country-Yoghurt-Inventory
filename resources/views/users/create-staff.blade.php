<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Staff - Country Yoghurt</title>
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
        <header class="topbar">
          <div class="title-block">
            <h2>Add Staff</h2>
            <p>Create staff accounts using the standard dashboard workflow.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('dashboard') }}" class="ghost-btn top-action-link">Back to Dashboard</a>
          </div>
        </header>

        <section class="card form-card">
          @if (session('status'))
            <div class="lp-success">
              <i class="bi bi-check-circle"></i>
              {{ session('status') }}
            </div>
          @endif

          @if ($errors->any())
            <div class="lp-error">
              <i class="bi bi-exclamation-circle"></i>
              {{ $errors->first() }}
            </div>
          @endif

          <form method="POST" action="{{ route('users.store.staff') }}" class="dashboard-form" novalidate>
            @csrf

            <div class="form-grid two-cols">
              <label>
                Full Name
                <input type="text" name="name" value="{{ old('name') }}" required />
              </label>

              <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required />
              </label>

              <label>
                Phone
                <input type="text" name="phone" value="{{ old('phone') }}" required />
              </label>

              <label style="grid-column: 1 / -1;">
                Covered States
                <div style="display:flex; flex-wrap:wrap; gap:8px 20px; margin-top:8px; padding:12px; background:#fafaf8; border:1px solid #e5e0d6; border-radius:8px; max-height:200px; overflow-y:auto;">
                  @foreach($states as $st)
                    <label style="display:flex;align-items:center;gap:6px;font-weight:400;cursor:pointer;min-width:150px;">
                      <input type="checkbox" name="states[]" value="{{ $st }}" class="state-cb" {{ in_array($st, old('states', [])) ? 'checked' : '' }}>
                      {{ $st }}
                    </label>
                  @endforeach
                </div>
                @error('states') <span style="color:#dc2626;font-size:0.8rem;">{{ $message }}</span> @enderror
              </label>

              <div style="grid-column: 1 / -1;" id="lgaSection">
                <span style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Covered LGAs <span style="color:var(--text-soft); font-weight:400;">(optional — leave blank for all LGAs in selected states)</span></span>
                <div id="lgaContainer" style="display:flex; flex-wrap:wrap; gap:8px 20px; padding:12px; background:#fafaf8; border:1px solid #e5e0d6; border-radius:8px; max-height:250px; overflow-y:auto;">
                  <span style="color:var(--text-soft); font-size:0.85rem;">Select at least one state above to see LGAs.</span>
                </div>
              </div>

              <label>
                Password
                <input type="password" name="password" required />
              </label>

              <label>
                Confirm Password
                <input type="password" name="password_confirmation" required />
              </label>
            </div>

            <div class="form-actions-row">
              <button type="submit" class="primary-btn">Create Staff</button>
            </div>
          </form>
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

      var LGA_MAP = @json($lgaMap);
      var OLD_LGAS = @json(old('lgas', []));

      function rebuildLgas() {
        var checkedStates = Array.from(document.querySelectorAll('.state-cb:checked')).map(function(cb) { return cb.value; });
        var container = document.getElementById('lgaContainer');
        if (checkedStates.length === 0) {
          container.innerHTML = '<span style="color:var(--text-soft); font-size:0.85rem;">Select at least one state above to see LGAs.</span>';
          return;
        }
        var lgas = [];
        checkedStates.forEach(function(st) { if (LGA_MAP[st]) lgas = lgas.concat(LGA_MAP[st]); });
        lgas = lgas.filter(function(v, i, a) { return a.indexOf(v) === i; }).sort();
        container.innerHTML = lgas.map(function(lga) {
          var checked = OLD_LGAS.indexOf(lga) !== -1 ? ' checked' : '';
          return '<label style="display:flex;align-items:center;gap:6px;font-weight:400;cursor:pointer;min-width:200px;">' +
            '<input type="checkbox" name="lgas[]" value="' + lga + '"' + checked + '> ' + lga + '</label>';
        }).join('');
      }

      document.querySelectorAll('.state-cb').forEach(function(cb) {
        cb.addEventListener('change', rebuildLgas);
      });
      rebuildLgas();
    </script>
  </body>
</html>
