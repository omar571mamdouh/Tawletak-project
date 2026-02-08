<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordOtp;
use App\Models\RestaurantStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffPasswordController extends Controller
{
    private function identifier(Request $request): string
    {
        if ($request->filled('phone')) return (string) $request->input('phone');
        return (string) $request->input('email');
    }

    private function staffByIdentifier(Request $request, string $identifier): ?RestaurantStaff
    {
        $q = RestaurantStaff::query()->where('is_active', true);

        return $request->filled('phone')
            ? $q->where('phone', $identifier)->first()
            : $q->where('email', $identifier)->first();
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

        $staff = $this->staffByIdentifier($request, $identifier);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        $otp = (string) random_int(10000, 99999); // 5 digits
        $expiresAt = now()->addMinutes(5);

        // invalidate old OTP for this identifier
        PasswordOtp::where('identifier', $identifier)->delete();

        PasswordOtp::create([
            'identifier' => $identifier,
            'otp_hash' => Hash::make($otp),
            'expires_at' => $expiresAt,
        ]);

        // TODO: send OTP via SMS/Email
        // SMS: send to $staff->phone
        // Email: send to $staff->email

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'data' => [
        'otp' => $otp, // للتست فقط
        'expires_at' => $expiresAt,
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
            'email' => ['nullable','email'],
            'phone' => ['nullable','string','min:6'],
            'otp'   => ['required','string','min:4','max:8'],
        ]);

        if (!$request->filled('email') && !$request->filled('phone')) {
            return response()->json([
                'success' => false,
                'message' => 'Email or phone is required'
            ], 422);
        }

        $identifier = $this->identifier($request);

        $record = PasswordOtp::where('identifier', $identifier)->latest()->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'OTP not found'
            ], 404);
        }

        if (now()->gt($record->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired'
            ], 422);
        }

        if (!Hash::check($request->otp, $record->otp_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 422);
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
            'password'    => ['required','string','min:8','confirmed'],
        ]);

        $hashedToken = hash('sha256', $request->reset_token);

        $record = PasswordOtp::where('reset_token', $hashedToken)->latest()->first();

        if (!$record || !$record->verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset token'
            ], 422);
        }

        // expire reset window 10 minutes after verify
        if (Carbon::parse($record->verified_at)->addMinutes(10)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Reset token expired'
            ], 422);
        }

        // Find staff by identifier (email or phone)
        $staff = RestaurantStaff::where('email', $record->identifier)
            ->orWhere('phone', $record->identifier)
            ->where('is_active', true)
            ->first();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        $staff->update([
            'password_hash' => Hash::make($request->password),
        ]);

        // cleanup OTP records for identifier
        PasswordOtp::where('identifier', $record->identifier)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }
}
