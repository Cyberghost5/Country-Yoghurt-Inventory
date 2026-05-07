<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SMS Broadcast - Country Yoghurt</title>
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
            <h2>SMS Broadcast</h2>
            <p>Send and track bulk SMS messages to users.</p>
          </div>
          <div class="top-actions">
            <a href="{{ route('admin.sms.create') }}" class="primary-btn">
              <i class="bi bi-send"></i> Compose SMS
            </a>
          </div>
        </header>

        {{-- ── Alerts ───────────────────────────────────────────────────── --}}
        @if (session('status'))
          <div class="lp-success" style="margin-bottom: 14px;">
            <i class="bi bi-check-circle"></i> {{ session('status') }}
          </div>
        @endif

        {{-- ── Stats strip ─────────────────────────────────────────────── --}}
        @php
          $totalLogs       = $logs->total();
          $totalSent       = \App\Models\SmsLog::sum('sent_count');
          $totalFailed     = \App\Models\SmsLog::sum('failed_count');
          $todayLogs       = \App\Models\SmsLog::whereDate('created_at', today())->count();
        @endphp
        <div class="sms-stats-strip">
          <div class="sms-stat-card">
            <span class="sms-stat-value">{{ number_format($totalLogs) }}</span>
            <span class="sms-stat-label">Total Campaigns</span>
          </div>
          <div class="sms-stat-card">
            <span class="sms-stat-value">{{ number_format($totalSent) }}</span>
            <span class="sms-stat-label">Messages Delivered</span>
          </div>
          <div class="sms-stat-card">
            <span class="sms-stat-value">{{ number_format($totalFailed) }}</span>
            <span class="sms-stat-label">Messages Failed</span>
          </div>
          <div class="sms-stat-card">
            <span class="sms-stat-value">{{ $todayLogs }}</span>
            <span class="sms-stat-label">Sent Today</span>
          </div>
          <div class="sms-stat-card sms-stat-balance">
            @if ($balance)
              <span class="sms-stat-value sms-balance-value">{{ $balance['formatted'] }}</span>
              <span class="sms-stat-label">
                <i class="bi bi-wallet2"></i> Wallet Balance
              </span>
            @else
              <span class="sms-stat-value" style="font-size: 1rem; color: var(--text-muted);">-</span>
              <span class="sms-stat-label">
                <i class="bi bi-wallet2"></i> Wallet Balance
              </span>
            @endif
          </div>
        </div>

        {{-- ── Log table ────────────────────────────────────────────────── --}}
        <section class="card table-card" style="margin-top: 18px;">
          @if ($logs->isEmpty())
            <div class="sms-empty-state">
              <i class="bi bi-chat-dots"></i>
              <p>No SMS campaigns yet.</p>
              <a href="{{ route('admin.sms.create') }}" class="primary-btn" style="margin-top: 10px;">
                Send your first SMS
              </a>
            </div>
          @else
            <div class="table-scroll">
              <table class="inv-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Date &amp; Time</th>
                    <th>Sent By</th>
                    <th>Recipients</th>
                    <th>Message Preview</th>
                    <th>Delivered</th>
                    <th>Failed</th>
                    <th>Status</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($logs as $log)
                    <tr>
                      <td class="table-muted">{{ $log->id }}</td>
                      <td>{{ $log->created_at->format('d M Y, g:ia') }}</td>
                      <td>{{ $log->sender?->name ?? '-' }}</td>
                      <td>
                        <span class="sms-type-badge sms-type-{{ $log->recipient_type }}">
                          {{ $log->recipientTypeLabel() }}
                        </span>
                        <span class="table-muted" style="margin-left: 4px;">({{ $log->recipient_count }})</span>
                      </td>
                      <td class="sms-msg-preview">{{ Str::limit($log->message, 60) }}</td>
                      <td class="text-success-muted"><strong>{{ $log->sent_count }}</strong></td>
                      <td class="{{ $log->failed_count > 0 ? 'text-danger-muted' : 'table-muted' }}">
                        {{ $log->failed_count }}
                      </td>
                      <td>
                        <span class="sms-status-badge sms-status-{{ $log->status }}">
                          {{ $log->statusLabel() }}
                        </span>
                      </td>
                      <td>
                        <a href="{{ route('admin.sms.show', $log) }}" class="view-link">
                          <i class="bi bi-eye"></i> View
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            {{-- Pagination --}}
            @if ($logs->hasPages())
              <div class="pag-wrap">
                {{ $logs->links() }}
              </div>
            @endif
          @endif
        </section>

      </main>
    </div>
  </body>
</html>
