<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BulkSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    // ── Step 1: Show phone input form ─────────────────────────────────

    public function showForm()
    {
        return view('auth.forgot-password');
    }

    // ── Step 2: Send OTP ──────────────────────────────────────────────

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $phone      = $request->input('phone');
        $normalised = $this->normalisePhone($phone);

        $user = User::where('phone', $phone)->orWhere('phone', $normalised)->first();

        // Always show success to prevent user enumeration
        if (!$user) {
            return back()
                ->withInput($request->only('phone'))
                ->withErrors(['phone' => 'No account found with that phone number.']);
        }

        $storedPhone = $user->phone;

        // Generate 6-digit OTP
        $otp     = (string) random_int(100000, 999999);
        $expires = now()->addMinutes(10);

        // Store hashed OTP (upsert so repeat requests overwrite)
        DB::table('password_reset_otps')->updateOrInsert(
            ['phone' => $storedPhone],
            [
                'otp'        => Hash::make($otp),
                'expires_at' => $expires,
                'created_at' => now(),
            ]
        );

        // Send via SMS
        $message = "Your Country Yoghurt password reset OTP is: {$otp}. It expires in 10 minutes. Do not share it.";
        app(BulkSmsService::class)->send($storedPhone, $message);

        // Keep phone in session for the verify step
        $request->session()->put('otp_phone', $storedPhone);

        return redirect()->route('password.verify')
            ->with('status', 'A 6-digit OTP has been sent to ' . $this->maskPhone($storedPhone) . '.');
    }

    // ── Step 3: Show OTP verify form ──────────────────────────────────

    public function showVerify(Request $request)
    {
        return view('auth.verify-otp');
    }

    // ── Step 4: Verify OTP and issue reset token ───────────────────────

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $phone = $request->session()->get('otp_phone');

        if (!$phone) {
            return redirect()->route('password.request')
                ->withErrors(['phone' => 'Session expired. Please start again.']);
        }

        $record = DB::table('password_reset_otps')->where('phone', $phone)->first();

        if (!$record || now()->isAfter($record->expires_at)) {
            DB::table('password_reset_otps')->where('phone', $phone)->delete();
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if (!Hash::check($request->input('otp'), $record->otp)) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        // OTP valid — clean up and store a short-lived reset token in session
        DB::table('password_reset_otps')->where('phone', $phone)->delete();

        $resetToken = bin2hex(random_bytes(32));
        $request->session()->put('otp_reset_token', $resetToken);
        $request->session()->put('otp_reset_phone', $phone);
        $request->session()->put('otp_reset_expires', now()->addMinutes(15)->timestamp);
        $request->session()->forget('otp_phone');

        return redirect()->route('password.reset', ['token' => $resetToken]);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '234') && strlen($digits) === 13) return $digits;
        if (str_starts_with($digits, '0') && strlen($digits) === 11) return '234' . substr($digits, 1);
        if (strlen($digits) === 10) return '234' . $digits;

        return $digits;
    }

    private function maskPhone(string $phone): string
    {
        if (strlen($phone) < 6) return str_repeat('*', strlen($phone));
        return substr($phone, 0, 4) . str_repeat('*', strlen($phone) - 7) . substr($phone, -3);
    }
}

