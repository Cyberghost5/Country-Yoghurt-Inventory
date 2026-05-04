<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Notifications - Country Yoghurt</title>
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
            <h2>Notifications</h2>
            <p>
              @if ($unreadCount > 0)
                <span class="notif-unread-label">{{ $unreadCount }} unread</span>
              @else
                All caught up
              @endif
            </p>
          </div>
          @if ($notifications->total() > 0)
            <div class="top-actions">
              <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="ghost-btn">
                  <i class="bi bi-check2-all"></i> Mark All Read
                </button>
              </form>
            </div>
          @endif
        </header>

        {{-- Alerts --}}
        @if (session('status'))
          <div class="lp-success" style="margin-bottom: 14px;">
            <i class="bi bi-check-circle"></i> {{ session('status') }}
          </div>
        @endif

        {{-- Notification list --}}
        @forelse ($notifications as $notif)
          @php
            $data   = $notif->data;
            $isRead = !is_null($notif->read_at);
            $icon   = match($data['type'] ?? '') {
                'placed'     => 'bi-bag-plus',
                'approved'   => 'bi-check-circle',
                'rejected'   => 'bi-x-circle',
                'delivered'  => 'bi-truck',
                'submitted'  => 'bi-credit-card',
                'scheduled'  => 'bi-calendar-plus',
                default      => 'bi-bell',
            };
          @endphp
          <div class="notif-item {{ $isRead ? 'notif-read' : 'notif-unread' }}">
            <div class="notif-icon-wrap">
              <i class="bi {{ $icon }} notif-icon"></i>
            </div>
            <div class="notif-body">
              <p class="notif-message">{{ $data['message'] ?? 'New notification' }}</p>
              <small class="notif-time">{{ $notif->created_at->diffForHumans() }}</small>
            </div>
            <div class="notif-actions">
              @if (!$isRead)
                <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                  @csrf
                  <button type="submit" class="notif-read-btn" title="Mark read &amp; open">
                    <i class="bi bi-arrow-right-circle"></i>
                  </button>
                </form>
              @elseif (!empty($data['url']))
                <a href="{{ $data['url'] }}" class="notif-read-btn notif-link-btn" title="View">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
              @endif
            </div>
          </div>
        @empty
          <div class="card" style="text-align:center; padding:48px 24px; color:var(--text-soft);">
            <i class="bi bi-bell-slash" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
            <p style="margin:0; font-size:0.9rem;">No notifications yet.</p>
          </div>
        @endforelse

        @if ($notifications->hasPages())
          <div style="margin-top: 16px;">
            {{ $notifications->links() }}
          </div>
        @endif

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
