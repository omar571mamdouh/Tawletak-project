<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    private function identifier(Request $request): string
    {
        // Choose one: phone or email
        if ($request->filled('phone')) return $request->input('phone');
        return $request->input('email');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['nullable','email'],
            'phone' => ['nullable','string','min:6'],
        ]);

        if (!$request->filled('email') && !$request->filled('phone')) {
            return response()->json([
                'success' => false,
                'message' => 'Email or phone is required'
            ], 422);
        }

        $identifier = $this->identifier($request);

        // Make sure user exists
        $userQuery = User::query();
        $user = $request->filled('phone')
            ? $userQuery->where('phone', $identifier)->first()
            : $userQuery->where('email', $identifier)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $otp = (string) random_int(10000, 99999); // 5 digits
        $expiresAt = now()->addMinutes(5);

        // invalidate old
        PasswordOtp::where('identifier', $identifier)->delete();

        PasswordOtp::create([
            'identifier' => $identifier,
            'otp_hash' => Hash::make($otp),
            'expires_at' => $expiresAt,
        ]);

        // TODO: send OTP via SMS/Email
        // Example: Mail::to($user->email)->send(new OtpMail($otp));
        // Example SMS provider ...

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully'
        ]);
    }

    public function resendOtp(Request $request)
    {
        // نفس forgotPassword (ممكن تعمل reuse)
        return $this->forgotPassword($request);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['nullable','email'],
            'phone' => ['nullable','string','min:6'],
            'otp' => ['required','string','min:4','max:8'],
        ]);

        $identifier = $this->identifier($request);

        $record = PasswordOtp::where('identifier', $identifier)->latest()->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'OTP not found'], 404);
        }

        if (now()->gt($record->expires_at)) {
            return response()->json(['success' => false, 'message' => 'OTP expired'], 422);
        }

        if (!Hash::check($request->otp, $record->otp_hash)) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP'], 422);
        }

        $resetToken = Str::random(64);

        $record->update([
            'reset_token' => hash('sha256', $resetToken),
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified',
            'data' => [
                'reset_token' => $resetToken
            ]
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'reset_token' => ['required','string'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        $hashed = hash('sha256', $request->reset_token);

        $record = PasswordOtp::where('reset_token', $hashed)->latest()->first();

        if (!$record || !$record->verified_at) {
            return response()->json(['success' => false, 'message' => 'Invalid reset token'], 422);
        }

        // Optional: expire reset session after X minutes from verification
        if (Carbon::parse($record->verified_at)->addMinutes(10)->isPast()) {
            return response()->json(['success' => false, 'message' => 'Reset token expired'], 422);
        }

        // Find user by identifier
        $user = User::where('email', $record->identifier)
            ->orWhere('phone', $record->identifier)
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // cleanup
        PasswordOtp::where('identifier', $record->identifier)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }
}
