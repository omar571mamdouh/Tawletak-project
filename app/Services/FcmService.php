<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmService
{
    /**
     * Send notification to a single FCM token
     * 
     * @param string $token FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return bool Success status
     */
    public static function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $messaging = Firebase::messaging();

            // FCM data must be strings
            $data = array_map(
                fn($v) => is_scalar($v) ? (string)$v : json_encode($v), 
                $data
            );

            // Create notification
            $notification = FcmNotification::create($title, $body);

            // Build message
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);

            // Send message
            $messaging->send($message);

            Log::info('FCM notification sent successfully', [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title
            ]);

            return true;

        } catch (MessagingException $e) {
            Log::error('FCM messaging error', [
                'token' => substr($token, 0, 20) . '...',
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            // If token is invalid, remove it
            if (self::isInvalidTokenError($e)) {
                DeviceToken::where('token', $token)->delete();
                Log::info('Invalid FCM token removed from database', [
                    'token' => substr($token, 0, 20) . '...'
                ]);
            }

            return false;

        } catch (\Throwable $e) {
            Log::error('Unexpected FCM error', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 20) . '...'
            ]);

            return false;
        }
    }

    /**
     * Send notification to admin user
     * 
     * @param int $adminId Admin user ID
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @return array Results with sent/failed counts
     */
    public static function sendToAdmin(int $adminId, string $title, string $body, array $data = []): array
    {
        return self::sendToOwner('admin', $adminId, $title, $body, $data);
    }

    /**
     * Send notification to customer user
     * 
     * @param int $customerId Customer user ID
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @return array Results with sent/failed counts
     */
    public static function sendToCustomer(int $customerId, string $title, string $body, array $data = []): array
    {
        return self::sendToOwner('customer', $customerId, $title, $body, $data);
    }

    /**
     * Send notification to owner (generic method)
     * 
     * @param string $ownerType Owner type (admin/customer)
     * @param int $ownerId Owner ID
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @return array Results with sent/failed counts
     */
    protected static function sendToOwner(
        string $ownerType,
        int $ownerId,
        string $title,
        string $body,
        array $data = []
    ): array {
        $tokens = DeviceToken::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::info('No FCM tokens found for user', [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId
            ]);

            return ['sent' => 0, 'failed' => 0, 'total' => 0];
        }

        $sent = 0;
        $failed = 0;

        foreach ($tokens as $token) {
            $success = self::sendToToken($token, $title, $body, $data);
            $success ? $sent++ : $failed++;
        }

        Log::info('FCM batch send completed', [
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'total' => count($tokens),
            'sent' => $sent,
            'failed' => $failed
        ]);

        return [
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($tokens)
        ];
    }

    /**
     * Send to multiple tokens (broadcast)
     * 
     * @param array $tokens Array of FCM tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @return array Results with sent/failed counts
     */
    public static function sendToMultipleTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = []
    ): array {
        if (empty($tokens)) {
            return ['sent' => 0, 'failed' => 0, 'total' => 0];
        }

        $sent = 0;
        $failed = 0;

        foreach ($tokens as $token) {
            $success = self::sendToToken($token, $title, $body, $data);
            $success ? $sent++ : $failed++;
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($tokens)
        ];
    }

    /**
     * Check if error indicates invalid token
     * 
     * @param MessagingException $e Exception from Firebase
     * @return bool True if token is invalid
     */
    protected static function isInvalidTokenError(MessagingException $e): bool
    {
        $errorMessage = strtolower($e->getMessage());

        // Common error messages for invalid tokens
        return str_contains($errorMessage, 'not found') ||
               str_contains($errorMessage, 'invalid') ||
               str_contains($errorMessage, 'unregistered') ||
               str_contains($errorMessage, 'registration token') ||
               in_array($e->getCode(), [404, 400]);
    }

    /**
     * Clean up expired tokens (run via scheduled task)
     * 
     * @param int $daysInactive Number of days of inactivity
     * @return int Number of tokens deleted
     */
    public static function cleanupExpiredTokens(int $daysInactive = 90): int
    {
        $deletedCount = DeviceToken::query()
            ->where('updated_at', '<', now()->subDays($daysInactive))
            ->delete();

        Log::info('Cleaned up expired FCM tokens', [
            'count' => $deletedCount,
            'days_inactive' => $daysInactive
        ]);

        return $deletedCount;
    }
}