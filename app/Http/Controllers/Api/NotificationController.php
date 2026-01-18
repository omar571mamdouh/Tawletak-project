<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\NotificationType;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    /**
     * Send notification to a specific customer
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToCustomer(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'type' => ['required', 'string', Rule::in([
                'reservation_created',
                'reservation_confirmed',
                'reservation_cancelled',
                'offer_created',
                'offer_redeemed',
                'system_alert',
            ])],
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'data' => 'nullable|array',
            'send_push' => 'nullable|boolean',
        ]);

        try {
            // Convert type string to enum
            $notificationType = NotificationType::from($validated['type']);

            // Send notification
            $notification = NotificationService::notifyCustomer(
                customerId: $validated['customer_id'],
                type: $notificationType,
                title: $validated['title'],
                message: $validated['message'],
                data: $validated['data'] ?? [],
                sendPush: $validated['send_push'] ?? true
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'data' => [
                    'notification_id' => $notification->id,
                    'customer_id' => $notification->recipient_id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'sent_at' => $notification->sent_at,
                ],
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Failed to send customer notification', [
                'customer_id' => $validated['customer_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send notification to multiple customers
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToMultipleCustomers(Request $request)
    {
        $validated = $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'required|integer|exists:customers,id',
            'type' => ['required', 'string', Rule::in([
                'reservation_created',
                'reservation_confirmed',
                'reservation_cancelled',
                'offer_created',
                'offer_redeemed',
                'system_alert',
            ])],
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'data' => 'nullable|array',
            'send_push' => 'nullable|boolean',
        ]);

        try {
            $notificationType = NotificationType::from($validated['type']);
            $results = [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($validated['customer_ids'] as $customerId) {
                try {
                    $notification = NotificationService::notifyCustomer(
                        customerId: $customerId,
                        type: $notificationType,
                        title: $validated['title'],
                        message: $validated['message'],
                        data: $validated['data'] ?? [],
                        sendPush: $validated['send_push'] ?? true
                    );

                    $results[] = [
                        'customer_id' => $customerId,
                        'status' => 'success',
                        'notification_id' => $notification->id,
                    ];
                    $successCount++;

                } catch (\Throwable $e) {
                    $results[] = [
                        'customer_id' => $customerId,
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ];
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Sent to {$successCount} customers, {$failedCount} failed",
                'summary' => [
                    'total' => count($validated['customer_ids']),
                    'success' => $successCount,
                    'failed' => $failedCount,
                ],
                'results' => $results,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Failed to send bulk customer notifications', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send notification to all customers
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToAllCustomers(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in([
                'offer_created',
                'system_alert',
            ])],
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'data' => 'nullable|array',
            'send_push' => 'nullable|boolean',
        ]);

        try {
            $notificationType = NotificationType::from($validated['type']);
            
            // Get all customer IDs
            $customerIds = \App\Models\Customer::pluck('id')->toArray();

            if (empty($customerIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No customers found',
                ], 404);
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($customerIds as $customerId) {
                try {
                    NotificationService::notifyCustomer(
                        customerId: $customerId,
                        type: $notificationType,
                        title: $validated['title'],
                        message: $validated['message'],
                        data: $validated['data'] ?? [],
                        sendPush: $validated['send_push'] ?? true
                    );
                    $successCount++;

                } catch (\Throwable $e) {
                    Log::error('Failed to send notification to customer', [
                        'customer_id' => $customerId,
                        'error' => $e->getMessage(),
                    ]);
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Broadcast sent to {$successCount} customers, {$failedCount} failed",
                'summary' => [
                    'total' => count($customerIds),
                    'success' => $successCount,
                    'failed' => $failedCount,
                ],
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Failed to broadcast to all customers', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to broadcast notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer notifications list
     * 
     * @param Request $request
     * @param int $customerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerNotifications(Request $request, int $customerId)
    {
        try {
            $notifications = \App\Models\Notification::where('recipient_type', 'customer')
                ->where('recipient_id', $customerId)
                ->orderByDesc('sent_at')
                ->paginate($request->input('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $notifications,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as read
     * 
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(int $notificationId)
    {
        try {
            $success = NotificationService::markAsRead($notificationId);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread count for customer
     * 
     * @param int $customerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount(int $customerId)
    {
        try {
            $count = NotificationService::getUnreadCount('customer', $customerId);

            return response()->json([
                'success' => true,
                'data' => [
                    'customer_id' => $customerId,
                    'unread_count' => $count,
                ],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}