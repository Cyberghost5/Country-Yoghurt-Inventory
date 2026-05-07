<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create User - Country Yoghurt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
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
      <div class="lp-left">
        <div class="lp-brand" style="align-items: center; gap: 0.5rem;">
          <img src="{{ asset('assets/img/logo.png') }}" alt="App Logo" style="height: 48px; width: 48px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);" />
          <div>
            <h1>Country Yoghurt</h1>
            <p>Premium Dairy </p>
          </div>
        </div>

        <div class="lp-badge">
          <i class="bi bi-person-plus"></i>
          Role-Secured User Onboarding
        </div>

        <div class="lp-hero">
          <h2>Controlled.<br><span>Access Management.</span></h2>
          <p>
            Admins can create staff and customers.<br>
            Staff can create customers only.<br>
            Customers cannot create users.
          </p>
        </div>

        <div class="lp-stats">
          <div>
            <strong>Admin</strong>
            <span>Create staff + customer</span>
          </div>
          <div>
            <strong>Staff</strong>
            <span>Create customer only</span>
          </div>
          <div>
            <strong>Customer</strong>
            <span>No create access</span>
          </div>
        </div>
      </div>

      <div class="lp-right">
        <div class="lp-form-wrap">
          <a href="{{ route('dashboard') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to dashboard
          </a>

          <div class="lp-form-head" style="margin-top: 18px;">
            <h2>Create a new user</h2>
            <p>Only roles available to your account are listed below.</p>
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

          <form method="POST" action="{{ route('users.store') }}" class="lp-form" novalidate>
            @csrf

            <div class="form-group">
              <label for="name">Full Name</label>
              <div class="input-wrap">
                <i class="bi bi-person"></i>
                <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Enter full name" required />
              </div>
            </div>

            <div class="form-group">
              <label for="email">Email Address</label>
              <div class="input-wrap">
                <i class="bi bi-envelope"></i>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="user@countryyoghurt.ng" required />
              </div>
            </div>

            <div class="form-group">
              <label for="role">Role</label>
              <div class="input-wrap">
                <i class="bi bi-shield-lock"></i>
                <select id="role" name="role" style="width: 100%; border: none; background: transparent; padding: 13px 0; font: inherit; color: #2e342b;" required>
                  <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select role</option>
                  @foreach($creatableRoles as $role)
                    <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input id="password" type="password" name="password" placeholder="Minimum 8 characters" required />
              </div>
            </div>

            <div class="form-group">
              <label for="password_confirmation">Confirm Password</label>
              <div class="input-wrap">
                <i class="bi bi-lock-fill"></i>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Repeat password" required />
              </div>
            </div>

            <button type="submit" class="btn-signin">Create User</button>
          </form>
        </div>
      </div>
    </div>
  </body>
</html>
