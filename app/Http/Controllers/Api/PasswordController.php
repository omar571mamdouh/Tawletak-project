<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Customer;

class PasswordController extends Controller
{
    private function emailIdentifier(Request $request): string
    {
        return strtolower(trim((string) $request->input('email')));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $this->emailIdentifier($request);

        $customer = Customer::query()
            ->where('email', $email)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $otp = (string) random_int(10000, 99999); // 5 digits
        $expiresAt = now()->addMinutes(5);

        // invalidate old
        PasswordOtp::where('identifier', $email)->delete();

        PasswordOtp::create([
            'identifier' => $email,
            'otp_hash' => Hash::make($otp),
            'expires_at' => $expiresAt,
        ]);

        // TODO: send OTP via Email

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'data' => [
                'otp' => $otp, // للتست فقط
                'expires_at' => $expiresAt->toISOString(),
            ]
        ]);
    }

    public function resendOtp(Request $request)
    {
        return $this->forgotPassword($request);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'min:4', 'max:8'],
        ]);

        $email = $this->emailIdentifier($request);

        $record = PasswordOtp::where('identifier', $email)->latest()->first();

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
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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

        $customer = Customer::where('email', $record->identifier)->first();

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $customer->update([
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
