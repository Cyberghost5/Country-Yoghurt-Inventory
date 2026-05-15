<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Accounts - Country Yoghurt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <style>
      .ba-hero {
        background: linear-gradient(135deg, #fffbf0 0%, #fff8e7 100%);
        border: 2px solid var(--accent);
        border-radius: 14px;
        padding: 28px 28px 24px;
        margin-bottom: 24px;
        display: flex;
        gap: 18px;
        align-items: flex-start;
      }
      .ba-hero-icon {
        background: var(--accent);
        color: #fff;
        border-radius: 50%;
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
      }
      .ba-hero-state {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 4px;
      }
      .ba-hero-bank  { font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 2px; }
      .ba-hero-name  { font-size: 0.92rem; color: var(--text-soft); margin-bottom: 10px; }
      .ba-hero-num   {
        font-size: 1.55rem;
        font-weight: 700;
        color: var(--text-main);
        letter-spacing: .1em;
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .copy-btn {
        background: none;
        border: 1px solid #d4c9b4;
        border-radius: 6px;
        cursor: pointer;
        padding: 4px 8px;
        font-size: 0.8rem;
        color: var(--accent);
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: background .15s;
      }
      .copy-btn:hover  { background: #fef3d0; }
      .copy-btn.copied { color: #16a34a; border-color: #86efac; }
      .ba-warning {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 10px;
        padding: 14px 18px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 24px;
        font-size: 0.88rem;
        color: #7c5000;
        line-height: 1.6;
      }
      .ba-all-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 12px;
      }
      .ba-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 14px;
      }
      .ba-card {
        background: #fff;
        border: 1px solid #e5e0d6;
        border-radius: 10px;
        padding: 16px 18px;
      }
      .ba-card.is-mine {
        border-color: var(--accent);
        background: #fffbf2;
      }
      .ba-card-state { font-size: 0.72rem; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
      .ba-card-bank  { font-size: 0.95rem; font-weight: 600; color: var(--text-main); }
      .ba-card-acct  { font-size: 0.83rem; color: var(--text-soft); margin-bottom: 4px; }
      .ba-card-num   { font-size: 1rem; font-weight: 700; color: var(--text-main); letter-spacing: .06em; }
      .ba-none {
        background: #f5f3ef;
        border-radius: 10px;
        padding: 24px;
        text-align: center;
        color: var(--text-soft);
        font-size: 0.92rem;
      }
    </style>
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">@include('partials._sidebar')</aside>
      <main class="main-content">

        <header class="topbar">
          <div class="title-block">
            <h2>Payment Accounts</h2>
            <p>Official bank accounts for Country Yoghurt payments.</p>
          </div>
        </header>

        {{-- Warning banner --}}
        <div class="ba-warning">
          <i class="bi bi-shield-exclamation" style="font-size:1.2rem; flex-shrink:0; margin-top:1px;"></i>
          <div>
            <strong>Always verify before paying.</strong> Country Yoghurt will never ask you to send money to a
            personal or unofficial account. Only transfer funds to the accounts listed on this page.
            If anyone contacts you with different account details, <strong>do not pay</strong> - report it to us immediately.
          </div>
        </div>

        {{-- Assigned staff's account --}}
        @if ($account)
          <div class="ba-hero">
            <div class="ba-hero-icon"><i class="bi bi-bank"></i></div>
            <div style="flex:1; min-width:0;">
              <div class="ba-hero-state">
                <i class="bi bi-person-fill"></i>
                {{ optional($staffMember)->name ?? 'Your Area' }}
                @if ($staffMember)
                  @php $covered = $staffMember->staffStates(); @endphp
                  @if (count($covered))
                    &mdash; {{ implode(', ', $covered) }}
                  @endif
                @endif
              </div>
              <div class="ba-hero-bank">{{ $account->bank_name }}</div>
              <div class="ba-hero-name">{{ $account->account_name }}</div>
              <div class="ba-hero-num">
                <span id="heroAcctNum">{{ $account->account_number }}</span>
                <button type="button" class="copy-btn" id="copyHeroBtn" onclick="copyAcct('heroAcctNum','copyHeroBtn')">
                  <i class="bi bi-copy"></i> Copy
                </button>
              </div>
            </div>
          </div>
        @else
          <div class="ba-none" style="margin-bottom:24px;">
            <i class="bi bi-info-circle" style="font-size:1.4rem; display:block; margin-bottom:8px;"></i>
            No bank account has been set up for your area yet.
            Please contact us directly to confirm payment details.
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

      function copyAcct(spanId, btnId) {
        var text = document.getElementById(spanId).textContent.trim();
        navigator.clipboard.writeText(text).then(function() {
          var btn = document.getElementById(btnId);
          btn.classList.add('copied');
          btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
          setTimeout(function() {
            btn.classList.remove('copied');
            btn.innerHTML = '<i class="bi bi-copy"></i> Copy';
          }, 2000);
        });
      }
    </script>
  </body>
</html>
