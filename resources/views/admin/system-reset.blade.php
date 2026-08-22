<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>System Reset - Country Yoghurt</title>
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
            <h2>System Reset / Wipe</h2>
            <p>Perform a complete cleanup of the application database.</p>
          </div>
        </header>

        {{-- Error Alerts --}}
        @if ($errors->any())
          <div class="lp-error" style="margin-bottom: 14px; background:#fef2f2; border:1px solid #ef4444; color:#991b1b; padding:12px 16px; border-radius:8px; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-exclamation-circle-fill" style="color:#ef4444;"></i>
            @foreach ($errors->all() as $error)
              <span>{{ $error }}</span>
            @endforeach
          </div>
        @endif

        <div style="max-width: 600px; margin: 0 auto; padding-top: 20px;">
          <section class="card" style="border: 2px solid var(--danger, #dc2626); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);">
            <div style="background: var(--danger, #dc2626); padding: 16px 20px; color: white; display: flex; align-items: center; gap: 10px;">
              <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.5rem;"></i>
              <h3 style="margin: 0; font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.5px;">CRITICAL SYSTEM WARNING</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.system.reset.perform') }}" style="padding: 24px;" onsubmit="return confirm('WARNING: Are you absolutely sure? This will delete all products, bank accounts, orders, payments, deliveries, and all user accounts except yours. This action is final and cannot be reversed.');">
              @csrf

              <p style="font-size: 0.92rem; color: var(--text-main); margin-top: 0; margin-bottom: 12px; line-height: 1.6;">
                Initiating a system reset will <strong>permanently delete</strong> all transactional and operational data in the database:
              </p>

              <ul style="font-size: 0.86rem; color: var(--text-soft); padding-left: 20px; margin-bottom: 20px; line-height: 1.7;">
                <li>All products, inventories, and unit specifications.</li>
                <li>All customer order records and order item lists.</li>
                <li>All payment logs, uploaded proof assets, and transactions.</li>
                <li>All delivery dispatch runs, customer allocations, and allocation items.</li>
                <li>All registered staff, administrator, and customer accounts.</li>
                <li><strong>Your super admin account will remain untouched</strong> to allow you to log back in.</li>
              </ul>

              <div class="pay-form-field" style="margin-bottom: 24px;">
                <label class="inv-field-label" for="reset_code" style="font-weight: 600; color: var(--text-main); margin-bottom: 6px; display: block;">
                  Enter Security Reset Code <span class="req">*</span>
                </label>
                <input type="password" id="reset_code" name="reset_code" required
                       class="inv-field-input" placeholder="Enter the APP_RESET_CODE"
                       style="width: 100%; height: 42px; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 0.95rem;" />
              </div>

              <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <a href="{{ route('dashboard') }}" class="ghost-btn" style="padding: 10px 20px;">Cancel</a>
                <button type="submit" class="primary-btn" 
                        style="background: var(--danger, #dc2626); border-color: var(--danger, #dc2626); padding: 10px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                  <i class="bi bi-trash-fill"></i> Wipe &amp; Reset System
                </button>
              </div>
            </form>
          </section>
        </div>

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
    </script>
  </body>
</html>
