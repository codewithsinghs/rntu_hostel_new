<?php

namespace App\Http\Controllers;

use App\Models\LeaveNotification;
use App\Models\PaymentNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator; // Used for manual array pagination

class NotificationController extends Controller
{
    // Helper function for consistent API responses
    private function apiResponse($success, $message, $data = null, $status = 200, $errors = null)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data ?? null,
            'errors' => $errors ?? null
        ], $status);
    }

    /**
     * Helper to fetch and format notifications from a given model.
     * This makes the code for leave and payment notifications more DRY.
     *
     * @param string $modelClass 'App\Models\LeaveNotification' or 'App\Models\PaymentNotification'
     * @param int $residentId
     * @param bool $onlyUnread
     * @return array
     */
    private function fetchAndFormatNotifications(string $modelClass, int $residentId, $onlyUnread = false)
    {
        $notifications = $modelClass::where('resident_id', $residentId)
            ->with($modelClass === 'App\Models\LeaveNotification' ? ['leaveRequest', 'resident'] : ['payment', 'resident']);

        if ($onlyUnread) {
            $notifications->where('is_read', false);
        }

        $notifications = $notifications->get();
        $formattedNotifications = [];

        foreach ($notifications as $notification) {
            $resident = $notification->resident;
            $message = '';
            $sourceId = null;
            $sourceType = '';

            if ($modelClass === 'App\Models\LeaveNotification') {
                $leaveRequest = $notification->leaveRequest;
                if (!$leaveRequest) {
                    \Log::warning("LeaveRequest not found for LeaveNotification ID: {$notification->id}. Skipping.");
                    continue;
                }
                $sourceType = 'leave_notification';
                $sourceId = $notification->leave_request_id;
                $message = "Leave request from " . Carbon::parse($leaveRequest->from_date)->format('M d, Y') .
                           " to " . Carbon::parse($leaveRequest->to_date)->format('M d, Y') .
                           " for reason '{$leaveRequest->reason}' has been ";
                switch ($notification->notification_type) {
                    case 'hod_approved_leave_request': $message .= "HOD approved."; break;
                    case 'hod_denied_leave_request': $message .= "HOD denied."; break;
                    case 'admin_approved_leave_request': $message .= "Admin approved."; break;
                    case 'admin_denied_leave_request': $message .= "Admin denied."; break;
                    default: $message .= "updated."; break;
                }
            } elseif ($modelClass === 'App\Models\PaymentNotification') {
                $payment = $notification->payment;
                if (!$payment) {
                    \Log::warning("Payment not found for PaymentNotification ID: {$notification->id}. Skipping.");
                    continue;
                }
                $sourceType = 'payment_notification';
                $sourceId = $notification->payment_id;
                $message = "Your payment for amount INR " . ($payment->total_amount ?? 'N/A') .
                           " due on " . ($payment->due_date ? Carbon::parse($payment->due_date)->format('M d, Y') : 'N/A') . " ";
                switch ($notification->notification_type) {
                    case 'due_15_10_days_remaining': $message .= "is approaching (15-10 days reminder)."; break;
                    case 'due_10_5_days_remaining_1': $message .= "is due soon (10-5 days, 1st reminder)."; break;
                    case 'due_10_5_days_remaining_2': $message .= "is almost due (10-5 days, 2nd reminder)."; break;
                    case 'due_5_0_days_remaining_1': $message .= "is due in a few days (5-0 days, 1st reminder)."; break;
                    case 'due_5_0_days_remaining_2': $message .= "is due very soon (5-0 days, 2nd reminder)."; break;
                    case 'due_5_0_days_remaining_3': $message .= "is due tomorrow! (5-0 days, final reminder)."; break;
                    default: $message .= "status updated."; break;
                }
            }

            $formattedNotifications[] = [
                'id' => $notification->id,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'resident_id' => $resident->id,
                'resident_name' => $resident->name,
                'notification_type' => $notification->notification_type,
                'message' => $message,
                'is_read' => (bool)$notification->is_read,
                'timestamp' => Carbon::parse($notification->sent_at)->toISOString(),
                'sms_gateway_message_id' => $notification->sms_gateway_message_id,
            ];
        }
        return $formattedNotifications;
    }


    /**
     * Get paginated notifications (leave and payment) for a specific resident,
     * ordered from latest to oldest.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $residentId The ID of the resident.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaginatedResidentNotifications(Request $request, $residentId)
    {
        try {
            $leaveNotifications = $this->fetchAndFormatNotifications('App\Models\LeaveNotification', $residentId);
            $paymentNotifications = $this->fetchAndFormatNotifications('App\Models\PaymentNotification', $residentId);

            $allNotifications = array_merge($leaveNotifications, $paymentNotifications);

            // Sort all notifications by timestamp (latest to oldest)
            usort($allNotifications, function ($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            // Manual Pagination
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $offset = ($page * $perPage) - $perPage;

            $paginatedNotifications = array_slice($allNotifications, $offset, $perPage);

            $paginator = new LengthAwarePaginator(
                $paginatedNotifications,
                count($allNotifications),
                $perPage,
                $page,
                ['path' => $request->url()]
            );

            return $this->apiResponse(true, 'Notifications retrieved successfully.', $paginator->toArray());

        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve notifications.', null, 500, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get all notifications (leave and payment) for a specific resident, without pagination.
     *
     * @param int $residentId The ID of the resident.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllResidentNotifications($residentId)
    {
        try {
            $leaveNotifications = $this->fetchAndFormatNotifications('App\Models\LeaveNotification', $residentId);
            $paymentNotifications = $this->fetchAndFormatNotifications('App\Models\PaymentNotification', $residentId);

            $allNotifications = array_merge($leaveNotifications, $paymentNotifications);

            // Sort all notifications by timestamp (latest to oldest)
            usort($allNotifications, function ($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            return $this->apiResponse(true, 'All notifications retrieved successfully.', $allNotifications);

        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve all notifications.', null, 500, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get all UNREAD notifications (leave and payment) for a specific resident,
     * ordered from latest to oldest.
     *
     * @param int $residentId The ID of the resident.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadResidentNotifications($residentId)
    {
        try {
            $leaveNotifications = $this->fetchAndFormatNotifications('App\Models\LeaveNotification', $residentId, true); // Pass true for onlyUnread
            $paymentNotifications = $this->fetchAndFormatNotifications('App\Models\PaymentNotification', $residentId, true); // Pass true for onlyUnread

            $allNotifications = array_merge($leaveNotifications, $paymentNotifications);

            // Sort all notifications by timestamp (latest to oldest)
            usort($allNotifications, function ($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            return $this->apiResponse(true, 'Unread notifications retrieved successfully.', $allNotifications);

        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve unread notifications.', null, 500, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Fetches all payment notifications, ordered from newest to oldest.
     * This endpoint is separate from resident-specific notifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentNotifications()
    {
        try {
            $paymentNotifications = PaymentNotification::orderBy('sent_at', 'desc')->get();
            // You might want to format these as well, similar to fetchAndFormatNotifications,
            // depending on what information is useful for this global endpoint.
            // For now, returning raw data.

            return $this->apiResponse(true, 'Payment notifications retrieved successfully.', $paymentNotifications);

        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve payment notifications.', null, 500, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Mark a specific notification as read.
     * This endpoint needs to know which table the notification belongs to.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The ID of the notification record (from leave_notifications or payment_notifications table).
     * @return \Illuminate\Http\JsonResponse
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'source_type' => 'required|string|in:leave_notification,payment_notification',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $validator->errors());
        }

        try {
            if ($request->source_type === 'leave_notification') {
                $notification = LeaveNotification::findOrFail($id);
            } else { // payment_notification
                $notification = PaymentNotification::findOrFail($id);
            }

            $notification->is_read = 1;
            $notification->save();

            return $this->apiResponse(true, 'Notification marked as read successfully.');

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Notification not found.', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to mark notification as read.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
