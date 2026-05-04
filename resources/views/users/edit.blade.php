<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit {{ ucfirst($targetUser->role) }} - Country Yoghurt</title>
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
            <h2>Edit {{ ucfirst($targetUser->role) }}</h2>
            <p>Editing <strong>{{ $targetUser->name }}</strong> &mdash; {{ $targetUser->email }}</p>
          </div>
          <div class="top-actions">
            <a href="javascript:history.back()" class="ghost-btn top-action-link">Back</a>
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

          {{-- Role badge --}}
          <div style="margin-bottom: 18px;">
            <span class="user-role-badge user-role-{{ $targetUser->role }}">{{ ucfirst($targetUser->role) }}</span>
          </div>

          <form method="POST" action="{{ route('users.update', $targetUser->id) }}" class="dashboard-form" novalidate>
            @csrf
            @method('PUT')

            <div class="form-grid two-cols">

              <label>
                Full Name
                <input type="text" name="name" value="{{ old('name', $targetUser->name) }}" required />
              </label>

              <label>
                Email
                <input type="email" name="email" value="{{ old('email', $targetUser->email) }}" required />
              </label>

              <label>
                Phone
                <input type="text" name="phone" value="{{ old('phone', $targetUser->phone) }}" required />
              </label>

              @if ($targetUser->role === 'customer')
                <label>
                  Shop Name
                  <input type="text" name="shop_name" value="{{ old('shop_name', $targetUser->shop_name) }}" required />
                </label>

                <label style="grid-column: 1 / -1;">
                  Address
                  <input type="text" name="address" value="{{ old('address', $targetUser->address) }}" required />
                </label>
              @endif

              <label>
                State
                <select id="state" name="state" required>
                  <option value="">Select State</option>
                  @foreach($states as $state)
                    <option value="{{ $state }}" {{ old('state', $targetUser->state) === $state ? 'selected' : '' }}>{{ $state }}</option>
                  @endforeach
                </select>
              </label>

              <label>
                LGA
                <select id="lga" name="lga" required>
                  <option value="">Select LGA</option>
                </select>
              </label>

              <label>
                New Password
                <input type="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current" />
              </label>

              <label>
                Confirm Password
                <input type="password" name="password_confirmation" autocomplete="new-password" />
              </label>

            </div>

            <div class="form-actions-row">
              <button type="submit" class="primary-btn">Save Changes</button>
            </div>
          </form>
        </section>
      </main>
    </div>

    <script id="lga-map-data" type="application/json">@json($lgaMap)</script>
    <script>
      window.CY_LGA_MAP = JSON.parse(document.getElementById('lga-map-data').textContent || '{}');
      window.CY_SELECTED_LGA = "{{ old('lga', $targetUser->lga ?? '') }}";
    </script>
    <script src="{{ asset('assets/js/location-dropdown.js') }}"></script>
    <script>
      window.CYPopulateLgaOptions('state', 'lga', window.CY_LGA_MAP, window.CY_SELECTED_LGA);
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
