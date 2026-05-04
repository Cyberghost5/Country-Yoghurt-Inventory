<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In - Country Yoghurt Inventory</title>
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
            <p>Premium Dairy &bull; Northern Nigeria</p>
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
          <div class="lp-form-head">
            <h2>Welcome back</h2>
            <p>Sign in to your account to manage inventory.</p>
          </div>

          @if ($errors->any())
            <div class="lp-error">
              <i class="bi bi-exclamation-circle"></i>
              {{ $errors->first() }}
            </div>
          @endif

          <form method="POST" action="{{ route('login.post') }}" class="lp-form" novalidate>
            @csrf

            <div class="form-group">
              <label for="email">Email Address</label>
              <div class="input-wrap">
                <i class="bi bi-envelope"></i>
                <input
                  id="email"
                  type="email"
                  name="email"
                  placeholder="Enter your email"
                  value="{{ old('email') }}"
                  autocomplete="email"
                  autofocus
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input
                  id="password"
                  type="password"
                  name="password"
                  placeholder="********"
                  autocomplete="current-password"
                  required
                />
                <button type="button" class="pw-toggle" aria-label="Toggle password visibility">
                  <i class="bi bi-eye" id="pwEyeIcon"></i>
                </button>
              </div>
            </div>

            <div class="form-row">
              <label class="check-label">
                <input type="checkbox" name="remember" id="remember" />
                <span class="custom-check"></span>
                Remember me for 30 days
              </label>
              <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-signin">
              Sign In to Dashboard
            </button>
          </form>

          <p class="lp-footer-note">
            This portal is restricted to authorized personnel only.<br>
            Country Yoghurt &copy; {{ date('Y') }} &middot; Bauchi, Nigeria
          </p>
        </div>{{-- /.lp-form-wrap --}}

      </div>{{-- /.lp-right --}}

    </div>{{-- /.login-shell --}}

    <script>
      // Password toggle
      const pwToggle = document.querySelector('.pw-toggle');
      const pwInput  = document.getElementById('password');
      const pwIcon   = document.getElementById('pwEyeIcon');
      pwToggle.addEventListener('click', () => {
        const isHidden = pwInput.type === 'password';
        pwInput.type     = isHidden ? 'text' : 'password';
        pwIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
      });

      // Image slider
      (function () {
        const slides = document.querySelectorAll('.lp-slide');
        const dots   = document.querySelectorAll('.lp-dot');
        let current  = 0;

        setInterval(() => {
          slides[current].classList.remove('lp-slide-active');
          // dots[current].classList.remove('lp-dot-active');
          current = (current + 1) % slides.length;
          slides[current].classList.add('lp-slide-active');
          // dots[current].classList.add('lp-dot-active');
        }, 4500);
      })();
    </script>
  </body>
</html>
