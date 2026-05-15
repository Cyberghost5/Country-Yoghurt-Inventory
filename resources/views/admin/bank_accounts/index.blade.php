<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bank Accounts - Country Yoghurt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <style>
      .ba-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; margin-top: 20px; }
      .ba-card { background: #fff; border: 1px solid #e5e0d6; border-radius: 10px; padding: 18px 20px; position: relative; }
      .ba-card-state { font-size: 0.75rem; font-weight: 600; color: var(--accent); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
      .ba-card-bank  { font-size: 1rem; font-weight: 600; color: var(--text-main); margin-bottom: 2px; }
      .ba-card-acct  { font-size: 0.88rem; color: var(--text-soft); }
      .ba-card-num   { font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-top: 6px; letter-spacing: .05em; }
      .ba-delete-btn { position: absolute; top: 12px; right: 12px; background: none; border: none; color: #dc2626; cursor: pointer; font-size: 1rem; padding: 4px 6px; border-radius: 6px; }
      .ba-delete-btn:hover { background: #fef2f2; }
      .ba-form-card  { background: #fffbf2; border: 2px dashed var(--accent); border-radius: 10px; padding: 22px 24px; margin-bottom: 4px; }
      .ba-form-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
      @media (max-width: 600px) { .ba-form-grid { grid-template-columns: 1fr; } }
    </style>
  </head>
  <body>
    @include('partials._mobile_topbar')
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">@include('partials._sidebar')</aside>
      <main class="main-content">

        <header class="topbar">
          <div class="title-block">
            <h2>Bank Accounts</h2>
            <p>Manage official bank account details assigned to each staff member.</p>
          </div>
        </header>

        @if (session('status'))
          <div class="lp-success" style="margin-bottom:14px;"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
        @endif
        @if ($errors->any())
          <div class="lp-error" style="margin-bottom:14px;">
            <ul style="margin:0; padding-left:18px;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        {{-- Add / Update form --}}
        <section class="ba-form-card">
          <h3 style="margin:0 0 16px; font-size:1rem; color:var(--text-main);">
            <i class="bi bi-plus-circle"></i> Add / Update Staff Bank Account
          </h3>
          <form method="POST" action="{{ route('admin.bank_accounts.store') }}">
            @csrf
            <div class="ba-form-grid">
              <div class="form-group">
                <label class="form-label">Staff Member <span style="color:#dc2626;">*</span></label>
                <select name="staff_id" class="form-input" required>
                  <option value="">- Select staff -</option>
                  @foreach ($staff as $s)
                    <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>
                      {{ $s->name }}
                      @php $covered = $s->staffStates(); @endphp
                      @if (count($covered)) ({{ implode(', ', $covered) }}) @endif
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Bank Name <span style="color:#dc2626;">*</span></label>
                <input type="text" name="bank_name" class="form-input" placeholder="e.g. Access Bank" value="{{ old('bank_name') }}" required />
              </div>
              <div class="form-group">
                <label class="form-label">Account Name <span style="color:#dc2626;">*</span></label>
                <input type="text" name="account_name" class="form-input" placeholder="Country Yoghurt Ltd" value="{{ old('account_name') }}" required />
              </div>
              <div class="form-group">
                <label class="form-label">Account Number <span style="color:#dc2626;">*</span></label>
                <input type="text" name="account_number" class="form-input" placeholder="0123456789" value="{{ old('account_number') }}" maxlength="20" required />
              </div>
            </div>
            <div style="margin-top:14px;">
              <button type="submit" class="primary-btn"><i class="bi bi-save"></i> Save Details</button>
            </div>
          </form>
        </section>

        {{-- Existing accounts grid --}}
        @if ($accounts->isEmpty())
          <p style="color:var(--text-soft); margin-top:24px;">No bank accounts have been added yet.</p>
        @else
          <div class="ba-grid">
            @foreach ($accounts as $acct)
              <div class="ba-card">
                <div class="ba-card-state">
                  <i class="bi bi-person"></i> {{ optional($acct->staff)->name ?? 'Unassigned' }}
                  @if ($acct->staff)
                    @php $covered = $acct->staff->staffStates(); @endphp
                    @if (count($covered))
                      &mdash; <span style="font-weight:400;">{{ implode(', ', $covered) }}</span>
                    @endif
                  @endif
                </div>
                <div class="ba-card-bank">{{ $acct->bank_name }}</div>
                <div class="ba-card-acct">{{ $acct->account_name }}</div>
                <div class="ba-card-num">{{ $acct->account_number }}</div>
                <form method="POST" action="{{ route('admin.bank_accounts.destroy', $acct) }}"
                      onsubmit="return confirm('Remove bank details for {{ optional($acct->staff)->name ?? 'this staff' }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="ba-delete-btn" title="Remove"><i class="bi bi-trash3"></i></button>
                </form>
              </div>
            @endforeach
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
