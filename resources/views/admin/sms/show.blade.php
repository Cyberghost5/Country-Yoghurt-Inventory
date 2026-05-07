<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SMS #{{ $smsLog->id }} - Country Yoghurt</title>
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

        {{-- ── Print header ─────────────────────────────────────────────── --}}
        <div class="print-header">
          <img src="{{ asset('assets/img/logo.png') }}" alt="Country Yoghurt" class="print-logo" />
          <div class="print-company-info">
            <h2>Country Yoghurt</h2>
            <p>SMS Broadcast Report &mdash; Printed {{ now()->format('d M Y, g:ia') }}</p>
          </div>
        </div>

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <header class="topbar no-print">
          <div class="title-block">
            <h2>SMS #{{ $smsLog->id }}</h2>
            <p>Sent {{ $smsLog->created_at->format('d M Y, g:ia') }}</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('admin.sms.index') }}" class="ghost-btn">
              <i class="bi bi-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="ghost-btn">
              <i class="bi bi-printer"></i> Print
            </button>
          </div>
        </header>

        {{-- ── Summary cards ────────────────────────────────────────────── --}}
        <div class="sms-detail-meta">

          <div class="ord-meta-grid" style="margin-bottom: 0;">
            <div class="ord-meta-item">
              <span class="ord-meta-label">Sent By</span>
              <span class="ord-meta-value">{{ $smsLog->sender?->name ?? '-' }}</span>
            </div>
            <div class="ord-meta-item">
              <span class="ord-meta-label">Date &amp; Time</span>
              <span class="ord-meta-value">{{ $smsLog->created_at->format('d M Y, g:ia') }}</span>
            </div>
            <div class="ord-meta-item">
              <span class="ord-meta-label">Recipient Group</span>
              <span class="ord-meta-value">{{ $smsLog->recipientTypeLabel() }}</span>
            </div>
            <div class="ord-meta-item">
              <span class="ord-meta-label">Total Recipients</span>
              <span class="ord-meta-value">{{ number_format($smsLog->recipient_count) }}</span>
            </div>
            <div class="ord-meta-item">
              <span class="ord-meta-label">Delivered</span>
              <span class="ord-meta-value" style="color: var(--success);">
                <i class="bi bi-check-circle-fill"></i> {{ number_format($smsLog->sent_count) }}
              </span>
            </div>
            <div class="ord-meta-item">
              <span class="ord-meta-label">Failed</span>
              <span class="ord-meta-value" style="color: {{ $smsLog->failed_count > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">
                @if ($smsLog->failed_count > 0)
                  <i class="bi bi-x-circle-fill"></i>
                @endif
                {{ number_format($smsLog->failed_count) }}
              </span>
            </div>
          </div>

          {{-- Status badge --}}
          <div style="margin-top: 14px;">
            <span class="sms-status-badge sms-status-{{ $smsLog->status }} sms-status-lg">
              {{ $smsLog->statusLabel() }}
            </span>
          </div>

          {{-- Message body --}}
          <div class="sms-message-body" style="margin-top: 16px;">
            <p class="ord-meta-label">Message</p>
            <div class="sms-message-text">{{ $smsLog->message }}</div>
          </div>

        </div>

        {{-- ── Recipient table ──────────────────────────────────────────── --}}
        <section class="card table-card" style="margin-top: 18px;">
          <div class="sms-recipients-header">
            <h3>Recipients</h3>
            <div class="sms-rcpt-filter no-print" id="rcptFilterWrap">
              <button type="button" class="sms-rcpt-filter-btn active" data-filter="all">
                All ({{ $smsLog->recipient_count }})
              </button>
              <button type="button" class="sms-rcpt-filter-btn" data-filter="sent">
                Delivered ({{ $smsLog->sent_count }})
              </button>
              <button type="button" class="sms-rcpt-filter-btn" data-filter="failed">
                Failed ({{ $smsLog->failed_count }})
              </button>
            </div>
          </div>

          <div class="table-scroll">
            <table class="inv-table" id="recipientsTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Phone</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($smsLog->recipients as $rcpt)
                  <tr data-status="{{ $rcpt->status }}">
                    <td class="table-muted">{{ $loop->iteration }}</td>
                    <td>{{ $rcpt->name }}</td>
                    <td class="table-muted">{{ $rcpt->phone }}</td>
                    <td>
                      @if ($rcpt->status === 'sent')
                        <span class="sms-status-badge sms-status-completed">
                          <i class="bi bi-check-circle-fill"></i> Delivered
                        </span>
                      @else
                        <span class="sms-status-badge sms-status-failed">
                          <i class="bi bi-x-circle-fill"></i> Failed
                        </span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </section>

      </main>
    </div>

    <script>
      document.getElementById('rcptFilterWrap').addEventListener('click', function (e) {
        const btn = e.target.closest('.sms-rcpt-filter-btn');
        if (!btn) return;

        document.querySelectorAll('.sms-rcpt-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const filter = btn.dataset.filter;
        document.querySelectorAll('#recipientsTable tbody tr').forEach(function (row) {
          row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
        });
      });
    </script>
  </body>
</html>
