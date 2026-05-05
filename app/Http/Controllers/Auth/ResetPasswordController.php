<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function showForm(Request $request, string $token)
    {
        $sessionToken   = $request->session()->get('otp_reset_token');
        $sessionExpires = $request->session()->get('otp_reset_expires');

        if (!$sessionToken || $token !== $sessionToken || now()->timestamp > (int) $sessionExpires) {
            return redirect()->route('password.request')
                ->withErrors(['phone' => 'This reset link has expired. Please start again.']);
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $token          = $request->input('token');
        $sessionToken   = $request->session()->get('otp_reset_token');
        $sessionExpires = $request->session()->get('otp_reset_expires');
        $phone          = $request->session()->get('otp_reset_phone');

        if (!$sessionToken || $token !== $sessionToken || now()->timestamp > (int) $sessionExpires || !$phone) {
            return redirect()->route('password.request')
                ->withErrors(['phone' => 'This reset link has expired. Please start again.']);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['phone' => 'Account not found.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        $request->session()->forget(['otp_reset_token', 'otp_reset_phone', 'otp_reset_expires']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Password reset successfully. Welcome back!');
    }
}
