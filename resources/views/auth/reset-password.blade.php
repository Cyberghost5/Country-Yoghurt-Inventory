<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - Country Yoghurt Inventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    />
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
  </head>
  <body>
    <div class="login-shell">

      {{-- -- LEFT PANEL -- --}}
      <div class="lp-left">

        {{-- Sliding background images --}}
        <div class="lp-slider" aria-hidden="true">
          <div class="lp-slide lp-slide-active" style="background-image:url('{{ asset('assets/img/sliders/1.jpg') }}')"></div>
          <div class="lp-slide" style="background-image:url('{{ asset('assets/img/sliders/2.jpg') }}')"></div>
          <div class="lp-slide" style="background-image:url('{{ asset('assets/img/sliders/3.jpg') }}')"></div>
          <div class="lp-slide" style="background-image:url('{{ asset('assets/img/sliders/4.jpg') }}')"></div>
          <div class="lp-slider-overlay"></div>
        </div>

        <div class="lp-brand">
          <div class="lp-brand-icon"><img src="{{ asset('assets/img/logo.png') }}" alt="Country Yoghurt" /></div>
          <div>
            <h1>Country Yoghurt</h1>
            <p>Premium Dairy </p>
          </div>
        </div>

        <div class="lp-badge">
          <i class="bi bi-shield-check"></i>
          Secure Login Portal
        </div>

        <div class="lp-hero">
          <h2>Thick. Creamy.<br><span>Freshly Managed.</span></h2>
          <p>
            End-to-end inventory control for Nigeria's most<br>
            beloved premium yoghurt - from Bauchi to your<br>
            customers' doorsteps.
          </p>
        </div>

        <div class="lp-stats">
          <div>
            <strong>2,450+</strong>
            <span>Units In Stock</span>
          </div>
          <div>
            <strong>98%</strong>
            <span>Fulfillment Rate</span>
          </div>
          <div>
            <strong>12</strong>
            <span>Active Suppliers</span>
          </div>
        </div>
      </div>

      {{-- -- RIGHT PANEL -- --}}
      <div class="lp-right">
        <div class="lp-form-wrap">

          <a href="{{ route('login') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to sign in
          </a>

          <div class="lp-form-head" style="margin-top: 18px;">
            <h2>Set new password</h2>
            <p>Choose a strong password for your account.</p>
          </div>

          @if ($errors->any())
            <div class="lp-error">
              <i class="bi bi-exclamation-circle"></i>
              {{ $errors->first() }}
            </div>
          @endif

          <form method="POST" action="{{ route('password.update') }}" class="lp-form" novalidate>
            @csrf

            <input type="hidden" name="token" value="{{ $token }}" />

            <div class="form-group">
              <label for="password">New Password</label>
              <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input
                  id="password"
                  type="password"
                  name="password"
                  placeholder="Min. 8 characters"
                  autocomplete="new-password"
                  required
                />
                <button type="button" class="pw-toggle" data-target="password" aria-label="Toggle password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label for="password_confirmation">Confirm Password</label>
              <div class="input-wrap">
                <i class="bi bi-lock-fill"></i>
                <input
                  id="password_confirmation"
                  type="password"
                  name="password_confirmation"
                  placeholder="Repeat your new password"
                  autocomplete="new-password"
                  required
                />
                <button type="button" class="pw-toggle" data-target="password_confirmation" aria-label="Toggle password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn-signin">
              Reset Password
            </button>
          </form>

          <p class="lp-footer-note">
            This portal is restricted to authorized personnel only.<br>
            Country Yoghurt &copy; {{ date('Y') }} &middot; Bauchi, Nigeria <br>
            Powered by <a href="https://zeetechfoundation.org" target="_blank" rel="noopener noreferrer">Zee Tech Ventures</a>
          </p>
        </div>
      </div>

    </div>

    <script>
      document.querySelectorAll('.pw-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
          const input = document.getElementById(btn.dataset.target);
          const icon  = btn.querySelector('i');
          const isHidden = input.type === 'password';
          input.type = isHidden ? 'text' : 'password';
          icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
      });
    </script>
  </body>
</html>
