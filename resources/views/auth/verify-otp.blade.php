<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify OTP - Country Yoghurt Inventory</title>
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
          <div class="lp-slide" style="background-image:url('{{ asset('assets/img/sliders/5.jpg') }}')"></div>
          <div class="lp-slide" style="background-image:url('{{ asset('assets/img/sliders/6.jpg') }}')"></div>
          <div class="lp-slide" style="background-image:url('{{ asset('assets/img/sliders/7.jpg') }}')"></div>
          <div class="lp-slide" style="background-image:url('{{ asset('assets/img/sliders/8.jpeg') }}')"></div>
          <div class="lp-slide" style="background-image:url('{{ asset('assets/img/sliders/9.jpeg') }}')"></div>
          <div class="lp-slide" style="background-image:url('{{ asset('assets/img/sliders/10.jpeg') }}')"></div>
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

          <a href="{{ route('password.request') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Back
          </a>

          <div class="lp-form-head" style="margin-top: 18px;">
            <h2>Enter your OTP</h2>
            @if (session('status'))
              <p>{{ session('status') }}</p>
            @else
              <p>Enter the 6-digit code sent to your phone number.</p>
            @endif
          </div>

          @if ($errors->any())
            <div class="lp-error">
              <i class="bi bi-exclamation-circle"></i>
              {{ $errors->first() }}
            </div>
          @endif

          <form method="POST" action="{{ route('password.verify.post') }}" class="lp-form" novalidate>
            @csrf

            <div class="form-group">
              <label for="otp">6-Digit OTP Code</label>
              <div class="input-wrap">
                <i class="bi bi-shield-lock"></i>
                <input
                  id="otp"
                  type="text"
                  name="otp"
                  placeholder="123456"
                  maxlength="6"
                  inputmode="numeric"
                  pattern="[0-9]{6}"
                  autocomplete="one-time-code"
                  autofocus
                  required
                  style="letter-spacing: 0.35em; font-size: 1.4rem; font-weight: 600; text-align: center;"
                />
              </div>
              <small style="color: #888; font-size: 0.78rem; margin-top: 4px; display: block;">
                The code expires in 10 minutes.
              </small>
            </div>

            <button type="submit" class="btn-signin">
              Verify Code
            </button>
          </form>

          <p style="text-align:center; font-size: 0.85rem; margin-top: 18px; color: #888;">
            Didn't receive a code?
            <a href="{{ route('password.request') }}" style="color: var(--primary, #2d6a4f); font-weight: 600;">
              Send again
            </a>
          </p>

          <p class="lp-footer-note">
            This portal is restricted to authorized personnel only.<br>
            Country Yoghurt &copy; {{ date('Y') }} &middot; Bauchi, Nigeria <br>
            Powered by <a href="https://zeetechfoundation.org" target="_blank" rel="noopener noreferrer">Zee Tech Ventures</a>
          </p>
        </div>
      </div>

    </div>
  </body>
</html>
