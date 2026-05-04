{{--
  Shared mobile topbar partial.
  Requires: $user (for unread notification count)
--}}
@php $unread = auth()->user()?->unreadNotifications()->count() ?? 0; @endphp

@if (session('impersonating_admin_id'))
<div class="impersonate-banner">
  <i class="bi bi-person-fill-gear"></i>
  Impersonating <strong>{{ auth()->user()->name }}</strong>
  <form method="POST" action="{{ route('impersonate.stop') }}" style="display:inline;margin-left:10px;">
    @csrf
    <button type="submit" class="impersonate-stop-btn">Stop &amp; Return</button>
  </form>
</div>
@endif

{{-- Desktop-only bell button (fixed top-right, hidden on mobile) --}}
<a href="{{ route('notifications.index') }}" class="desktop-bell-btn notif-bell-btn" aria-label="Notifications">
  <i class="bi bi-bell"></i>
  @if ($unread > 0)
    <span class="notif-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
  @endif
</a>

<div class="mobile-topbar">
  <button class="hamburger" id="sidebarToggle" aria-label="Open navigation">
    <i class="bi bi-list"></i>
  </button>
  <span class="mobile-brand">Country Yoghurt</span>
  <a href="{{ route('notifications.index') }}" class="icon-btn mobile-icon-btn notif-bell-btn" aria-label="Notifications">
    <i class="bi bi-bell"></i>
    @if ($unread > 0)
      <span class="notif-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
    @endif
  </a>
</div>
