<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notify admin user
     * 
     * @param int $adminId Admin ID
     * @param NotificationType $type Notification type
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @param bool $sendPush Whether to send push notification
     * @return Notification Created notification instance
     */
    public static function notifyAdmin(
        int $adminId,
        NotificationType $type,
        string $title,
        string $message,
        array $data = [],
        bool $sendPush = true
    ): Notification {
        return self::createAndSendNotification(
            recipientType: 'admin',
            recipientId: $adminId,
            type: $type,
            title: $title,
            message: $message,
            data: $data,
            sendPush: $sendPush
        );
    }

    /**
     * Notify customer user
     * 
     * @param int $customerId Customer ID
     * @param NotificationType $type Notification type
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @param bool $sendPush Whether to send push notification
     * @return Notification Created notification instance
     */
    public static function notifyCustomer(
        int $customerId,
        NotificationType $type,
        string $title,
        string $message,
        array $data = [],
        bool $sendPush = true
    ): Notification {
        return self::createAndSendNotification(
            recipientType: 'customer',
            recipientId: $customerId,
            type: $type,
            title: $title,
            message: $message,
            data: $data,
            sendPush: $sendPush
        );
    }

    /**
     * Create notification and send push notification
     * 
     * @param string $recipientType Recipient type (admin/customer)
     * @param int $recipientId Recipient ID
     * @param NotificationType $type Notification type
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @param bool $sendPush Whether to send push notification
     * @return Notification Created notification instance
     */
    protected static function createAndSendNotification(
        string $recipientType,
        int $recipientId,
        NotificationType $type,
        string $title,
        string $message,
        array $data = [],
        bool $sendPush = true
    ): Notification {
        // Create notification record in database
        $notification = Notification::create([
            'recipient_type' => $recipientType,
            'recipient_id'   => $recipientId,
            'type'           => $type->value,
            'title'          => $title,
            'message'        => $message,
            'data_json'      => $data,
            'is_read'        => false,
            'sent_at'        => now(),
        ]);

        Log::info('Notification created', [
            'id' => $notification->id,
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'type' => $type->value
        ]);

        // Send push notification if enabled
        if ($sendPush) {
            try {
                // Prepare push data
                $pushData = array_merge([
                    'notification_id' => (string) $notification->id,
                    'type' => $type->value,
                ], $data);

                // Send based on recipient type
                $result = match($recipientType) {
                    'admin' => FcmService::sendToAdmin($recipientId, $title, $message, $pushData),
                    'customer' => FcmService::sendToCustomer($recipientId, $title, $message, $pushData),
                    default => ['sent' => 0, 'failed' => 0, 'total' => 0]
                };

                Log::info('Push notification sent', [
                    'notification_id' => $notification->id,
                    'result' => $result
                ]);

            } catch (\Throwable $e) {
                Log::error('Failed to send push notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $notification;
    }

    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public static function markAsRead(int $notificationId): bool
    {
        $updated = Notification::where('id', $notificationId)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        if ($updated) {
            Log::info('Notification marked as read', ['id' => $notificationId]);
        }

        return (bool) $updated;
    }

    /**
     * Mark all notifications as read for a recipient
     * 
     * @param string $recipientType Recipient type (admin/customer)
     * @param int $recipientId Recipient ID
     * @return int Number of notifications marked as read
     */
    public static function markAllAsRead(string $recipientType, int $recipientId): int
    {
        $count = Notification::where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        Log::info('All notifications marked as read', [
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'count' => $count
        ]);

        return $count;
    }

    /**
     * Get unread count for recipient
     * 
     * @param string $recipientType Recipient type (admin/customer)
     * @param int $recipientId Recipient ID
     * @return int Unread notifications count
     */
    public static function getUnreadCount(string $recipientType, int $recipientId): int
    {
        return Notification::where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Delete old read notifications (cleanup)
     * 
     * @param int $daysOld Number of days to keep
     * @return int Number of notifications deleted
     */
    public static function cleanupOldNotifications(int $daysOld = 30): int
    {
        $count = Notification::where('is_read', true)
            ->where('read_at', '<', now()->subDays($daysOld))
            ->delete();

        Log::info('Old notifications cleaned up', [
            'count' => $count,
            'days_old' => $daysOld
        ]);

        return $count;
    }

    // ====================================
    // Helper Methods for Common Scenarios
    // ====================================

    /**
     * Notify about new reservation (for admin)
     */
    public static function notifyNewReservation(int $adminId, array $reservationData): Notification
    {
        return self::notifyAdmin(
            adminId: $adminId,
            type: NotificationType::ReservationCreated,
            title: 'حجز جديد',
            message: "حجز جديد من {$reservationData['customer_name']} لـ {$reservationData['party_size']} أشخاص",
            data: $reservationData,
            sendPush: true
        );
    }

    /**
     * Notify customer about reservation confirmation
     */
    public static function notifyReservationConfirmed(int $customerId, array $reservationData): Notification
    {
        return self::notifyCustomer(
            customerId: $customerId,
            type: NotificationType::ReservationConfirmed,
            title: 'تم تأكيد الحجز',
            message: "تم تأكيد حجزك في {$reservationData['restaurant_name']} بتاريخ {$reservationData['date']}",
            data: $reservationData,
            sendPush: true
        );
    }

    /**
     * Notify customer about reservation cancellation
     */
    public static function notifyReservationCancelled(int $customerId, array $reservationData): Notification
    {
        return self::notifyCustomer(
            customerId: $customerId,
            type: NotificationType::ReservationCancelled,
            title: 'تم إلغاء الحجز',
            message: "تم إلغاء حجزك في {$reservationData['restaurant_name']}",
            data: $reservationData,
            sendPush: true
        );
    }

    /**
     * Notify customer about new offer
     */
    public static function notifyNewOffer(int $customerId, array $offerData): Notification
    {
        return self::notifyCustomer(
            customerId: $customerId,
            type: NotificationType::OfferCreated,
            title: 'عرض جديد',
            message: $offerData['title'],
            data: $offerData,
            sendPush: true
        );
    }
}