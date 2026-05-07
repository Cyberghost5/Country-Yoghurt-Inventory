<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password - Country Yoghurt Inventory</title>
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

          <a href="{{ route('login') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to sign in
          </a>

          <div class="lp-form-head" style="margin-top: 18px;">
            <h2>Forgot your password?</h2>
            <p>Enter your registered phone number and we'll send you a 6-digit OTP code.</p>
          </div>

          @if (session('status'))
            <div class="lp-success">
              <i class="bi bi-check-circle"></i>
              {{ session('status') }}
            </div>
          @endif

          @if ($errors->any())
            <div class="lp-error">
              <i class="bi bi-exclamation-circle"></i>
              {{ $errors->first() }}
            </div>
          @endif

          <form method="POST" action="{{ route('password.email') }}" class="lp-form" novalidate>
            @csrf

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <div class="input-wrap">
                <i class="bi bi-phone"></i>
                <input
                  id="phone"
                  type="tel"
                  name="phone"
                  placeholder="e.g. 08012345678"
                  value="{{ old('phone') }}"
                  autocomplete="tel"
                  autofocus
                  required
                />
              </div>
            </div>

            <button type="submit" class="btn-signin">
              Send OTP Code
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
  </body>
</html>
