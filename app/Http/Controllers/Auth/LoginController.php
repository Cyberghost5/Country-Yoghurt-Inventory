<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $phone    = $request->input('phone');
        $remember = $request->boolean('remember');

        // Normalise phone so users can type 0XX or 234XX
        $normalised = $this->normalisePhone($phone);

        // Try both the raw input and the normalised form
        $user = User::where('phone', $phone)->orWhere('phone', $normalised)->first();

        if ($user && Auth::attempt(['phone' => $user->phone, 'password' => $request->input('password')], $remember)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()
            ->withInput($request->only('phone'))
            ->withErrors(['phone' => 'These credentials do not match our records.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '234') && strlen($digits) === 13) {
            return $digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '234' . substr($digits, 1);
        }
        if (strlen($digits) === 10) {
            return '234' . $digits;
        }

        return $digits;
    }
}

