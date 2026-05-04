<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Compose SMS - Country Yoghurt</title>
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

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <header class="topbar">
          <div class="title-block">
            <h2>Compose SMS</h2>
            <p>Send a broadcast message to selected users.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('admin.sms.index') }}" class="sec-btn">
              <i class="bi bi-arrow-left"></i> Back to History
            </a>
          </div>
        </header>

        {{-- ── Alerts ───────────────────────────────────────────────────── --}}
        @if ($errors->any())
          <div class="lp-error" style="margin-bottom: 14px;">
            <i class="bi bi-exclamation-circle"></i>
            @foreach ($errors->all() as $error)
              <span>{{ $error }}</span>
            @endforeach
          </div>
        @endif

        {{-- ── Form ────────────────────────────────────────────────────── --}}
        <form method="POST" action="{{ route('admin.sms.send') }}" id="smsForm">
          @csrf

          <div class="sms-compose-grid">

            {{-- Left: Recipient Selection --}}
            <div class="sms-compose-left">

              {{-- Recipient type --}}
              <section class="card" style="padding: 24px;">
                <h3 class="sms-section-title">
                  <i class="bi bi-people"></i> Recipient Group
                </h3>

                <div class="sms-radio-group">

                  <label class="sms-radio-card {{ old('recipient_type', 'all') === 'all' ? 'selected' : '' }}">
                    <input type="radio" name="recipient_type" value="all"
                           {{ old('recipient_type', 'all') === 'all' ? 'checked' : '' }} />
                    <span class="sms-radio-icon"><i class="bi bi-globe"></i></span>
                    <span class="sms-radio-text">
                      <strong>All Users</strong>
                      <small>Everyone with a phone number</small>
                    </span>
                  </label>

                  <label class="sms-radio-card {{ old('recipient_type') === 'customers' ? 'selected' : '' }}">
                    <input type="radio" name="recipient_type" value="customers"
                           {{ old('recipient_type') === 'customers' ? 'checked' : '' }} />
                    <span class="sms-radio-icon"><i class="bi bi-shop"></i></span>
                    <span class="sms-radio-text">
                      <strong>All Customers</strong>
                      <small>Customer accounts only</small>
                    </span>
                  </label>

                  <label class="sms-radio-card {{ old('recipient_type') === 'staff' ? 'selected' : '' }}">
                    <input type="radio" name="recipient_type" value="staff"
                           {{ old('recipient_type') === 'staff' ? 'checked' : '' }} />
                    <span class="sms-radio-icon"><i class="bi bi-person-badge"></i></span>
                    <span class="sms-radio-text">
                      <strong>Staff &amp; Admins</strong>
                      <small>Internal team members</small>
                    </span>
                  </label>

                  <label class="sms-radio-card {{ old('recipient_type') === 'custom' ? 'selected' : '' }}">
                    <input type="radio" name="recipient_type" value="custom"
                           {{ old('recipient_type') === 'custom' ? 'checked' : '' }} />
                    <span class="sms-radio-icon"><i class="bi bi-ui-checks"></i></span>
                    <span class="sms-radio-text">
                      <strong>Select Individuals</strong>
                      <small>Pick specific users</small>
                    </span>
                  </label>

                </div>

                {{-- Recipient count preview --}}
                <div class="sms-count-preview" id="recipientPreview">
                  <i class="bi bi-info-circle"></i>
                  <span id="recipientCountText">Calculating…</span>
                </div>
              </section>

              {{-- Custom user selector --}}
              <section class="card sms-custom-panel" id="customPanel"
                       style="{{ old('recipient_type') === 'custom' ? '' : 'display:none;' }} padding: 24px;">
                <h3 class="sms-section-title">
                  <i class="bi bi-ui-checks"></i> Select Users
                </h3>

                <div class="sms-user-search-wrap">
                  <i class="bi bi-search sms-search-icon"></i>
                  <input type="text" id="userSearch" placeholder="Search by name or role…"
                         class="sms-user-search" autocomplete="off" />
                </div>

                <div class="sms-select-actions">
                  <button type="button" id="selectAll" class="sms-sel-btn">Select all</button>
                  <button type="button" id="deselectAll" class="sms-sel-btn">Deselect all</button>
                  <span class="sms-sel-count" id="selectedCount">0 selected</span>
                </div>

                <div class="sms-user-list" id="userList">
                  @forelse ($users as $u)
                    <label class="sms-user-item"
                           data-name="{{ strtolower($u->name) }}"
                           data-role="{{ $u->role }}">
                      <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                             {{ is_array(old('user_ids')) && in_array($u->id, old('user_ids')) ? 'checked' : '' }} />
                      <span class="sms-user-avatar">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                      </span>
                      <span class="sms-user-info">
                        <strong>{{ $u->name }}</strong>
                        <small>{{ ucfirst($u->role) }} &middot; {{ $u->phone }}</small>
                      </span>
                    </label>
                  @empty
                    <p class="table-muted" style="padding: 12px 0;">No users with phone numbers registered.</p>
                  @endforelse
                </div>
              </section>

            </div>

            {{-- Right: Message --}}
            <div class="sms-compose-right">
              <section class="card" style="padding: 24px;">
                <h3 class="sms-section-title">
                  <i class="bi bi-chat-text"></i> Message
                </h3>

                <textarea name="message" id="smsMessage" class="sms-textarea"
                          placeholder="Type your message here…"
                          maxlength="918">{{ old('message') }}</textarea>

                <div class="sms-char-meta">
                  <span><span id="charCount">0</span> characters</span>
                  <span class="sms-sms-count">
                    <i class="bi bi-envelope"></i>
                    <span id="smsCount">1</span> SMS unit<span id="smsPlural"></span>
                  </span>
                </div>

                <div class="sms-char-bar">
                  <div class="sms-char-fill" id="charFill"></div>
                </div>

                <p class="sms-hint">
                  Standard SMS = 160 chars. Longer messages use multiple units and cost more.
                </p>

                @if ($balance)
                  <div class="sms-balance-pill">
                    <i class="bi bi-wallet2"></i>
                    Wallet balance: <strong>{{ $balance['formatted'] }}</strong>
                  </div>
                @endif

                <div class="sms-send-bar">
                  <button type="submit" class="primary-btn sms-send-btn" id="sendBtn">
                    <i class="bi bi-send"></i>
                    <span id="sendBtnText">Send SMS</span>
                  </button>
                </div>
              </section>

              {{-- Preview card --}}
              <section class="card sms-preview-card" style="padding: 24px;">
                <h3 class="sms-section-title">
                  <i class="bi bi-phone"></i> Preview
                </h3>
                <div class="sms-phone-mock">
                  <div class="sms-bubble" id="previewBubble">
                    <em class="table-muted">Your message will appear here…</em>
                  </div>
                </div>
              </section>

            </div>
          </div>
        </form>

      </main>
    </div>

    <script>
      // ── Recipient counts per group ─────────────────────────────────────────
      const groupCounts = {
        all:       {{ $users->count() }},
        customers: {{ $users->where('role', 'customer')->count() }},
        staff:     {{ $users->whereIn('role', ['staff', 'admin'])->count() }},
        custom:    0,
      };

      function updateRecipientPreview() {
        const type = document.querySelector('input[name="recipient_type"]:checked')?.value || 'all';
        let count;
        if (type === 'custom') {
          count = document.querySelectorAll('#userList input[type="checkbox"]:checked').length;
        } else {
          count = groupCounts[type] ?? 0;
        }
        const noun  = count === 1 ? 'recipient' : 'recipients';
        document.getElementById('recipientCountText').textContent =
          count + ' ' + noun + ' will receive this message';
        document.getElementById('sendBtnText').textContent =
          'Send SMS (' + count + ')';
      }

      // ── Radio card styling ─────────────────────────────────────────────────
      document.querySelectorAll('.sms-radio-card input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
          document.querySelectorAll('.sms-radio-card').forEach(c => c.classList.remove('selected'));
          this.closest('.sms-radio-card').classList.add('selected');

          const customPanel = document.getElementById('customPanel');
          if (this.value === 'custom') {
            customPanel.style.display = '';
          } else {
            customPanel.style.display = 'none';
          }
          updateRecipientPreview();
        });
      });

      // ── Custom selector ────────────────────────────────────────────────────
      function updateSelectedCount() {
        const n = document.querySelectorAll('#userList input[type="checkbox"]:checked').length;
        document.getElementById('selectedCount').textContent = n + ' selected';
        updateRecipientPreview();
      }

      document.getElementById('userList').addEventListener('change', updateSelectedCount);

      document.getElementById('selectAll').addEventListener('click', function () {
        document.querySelectorAll('#userList .sms-user-item:not([style*="display: none"]) input[type="checkbox"]')
          .forEach(cb => { cb.checked = true; });
        updateSelectedCount();
      });

      document.getElementById('deselectAll').addEventListener('click', function () {
        document.querySelectorAll('#userList input[type="checkbox"]')
          .forEach(cb => { cb.checked = false; });
        updateSelectedCount();
      });

      document.getElementById('userSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('#userList .sms-user-item').forEach(function (item) {
          const match = item.dataset.name.includes(q) || item.dataset.role.includes(q) || q === '';
          item.style.display = match ? '' : 'none';
        });
      });

      // ── Character counter & preview ────────────────────────────────────────
      const msgArea   = document.getElementById('smsMessage');
      const charCount = document.getElementById('charCount');
      const smsCount  = document.getElementById('smsCount');
      const smsPlural = document.getElementById('smsPlural');
      const charFill  = document.getElementById('charFill');
      const preview   = document.getElementById('previewBubble');

      function updateMessage() {
        const len  = msgArea.value.length;
        const sms  = len === 0 ? 1 : Math.ceil(len / 160);
        charCount.textContent = len;
        smsCount.textContent  = sms;
        smsPlural.textContent = sms === 1 ? '' : 's';
        charFill.style.width  = Math.min((len % 160) / 160 * 100, 100) + '%';
        preview.innerHTML = msgArea.value
          ? msgArea.value.replace(/\n/g, '<br>')
          : '<em class="table-muted">Your message will appear here…</em>';
      }

      msgArea.addEventListener('input', updateMessage);

      // ── Init ───────────────────────────────────────────────────────────────
      updateMessage();
      updateRecipientPreview();
      updateSelectedCount();

      // ── Prevent double-submit ──────────────────────────────────────────────
      document.getElementById('smsForm').addEventListener('submit', function () {
        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending…';
      });
    </script>
  </body>
</html>
