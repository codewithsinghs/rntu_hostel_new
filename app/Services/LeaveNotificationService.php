<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveNotification;
use Illuminate\Support\Facades\Log;
// Import your SMS sending service/facade here if you have one (e.g., use Twilio\Rest\Client as TwilioClient;)

class LeaveNotificationService
{
    /**
     * Sends a notification about a leave request's status change.
     *
     * @param LeaveRequest $leaveRequest The leave request instance.
     * @param string $approver The entity that approved/denied (e.g., 'HOD', 'Admin').
     * @param string $status The new status ('approved', 'denied').
     * @return void
     */
    public function sendLeaveStatusNotification(LeaveRequest $leaveRequest, string $approver, string $status): void
    {
        // Ensure resident relationship is loaded to access contact numbers
        if (!$leaveRequest->relationLoaded('resident')) {
            $leaveRequest->load('resident');
        }

        $resident = $leaveRequest->resident;

        if (!$resident) {
            Log::warning("Leave notification skipped: No resident found for leave request ID {$leaveRequest->id}.");
            return;
        }

        $residentName = $resident->name;
        $fromDate = \Carbon\Carbon::parse($leaveRequest->from_date)->format('M d, Y');
        $toDate = \Carbon\Carbon::parse($leaveRequest->to_date)->format('M d, Y');
        $reason = $leaveRequest->reason;

        // Determine the notification type to prevent duplicates
        $notificationType = strtolower($approver) . '_' . strtolower($status) . '_leave_request';

        // Check if this specific notification has already been sent
        if ($this->hasNotificationBeenSent($leaveRequest->id, $notificationType)) {
            Log::info("Leave notification '{$notificationType}' already sent for Leave Request ID {$leaveRequest->id}. Skipping.");
            return;
        }

        $message = "Dear {$residentName}, your leave request from {$fromDate} to {$toDate} for reason '{$reason}' has been {$status} by {$approver}.";

        $recipients = [];
        if (!empty($resident->number)) {
            $recipients['resident'] = $resident->number;
        }
        if (!empty($resident->parent_no)) {
            $recipients['parent'] = $resident->parent_no;
        }
        if (!empty($resident->guardian_no)) {
            $recipients['guardian'] = $resident->guardian_no;
        }

        if (empty($recipients)) {
            Log::warning("Leave notification skipped: No contact numbers found for resident ID {$resident->id} for leave request ID {$leaveRequest->id}.");
            return;
        }

        foreach ($recipients as $type => $number) {
            $recipientName = $residentName . ($type === 'resident' ? '' : "'s {$type}");
            $gatewayMessageId = $this->sendSmsNotification($number, $message, $recipientName);
            // Record notification for each recipient sent to, using the same type
            $this->recordNotification($leaveRequest->id, $resident->id, $notificationType, $gatewayMessageId);
        }

        Log::info("Successfully processed leave notification for Leave Request ID {$leaveRequest->id} (Status: {$status} by {$approver}).");
    }

    /**
     * Checks if a specific notification type has already been sent for a given leave request.
     *
     * @param int $leaveRequestId The ID of the leave request.
     * @param string $notificationType The unique identifier for the notification.
     * @return bool True if the notification has been sent, false otherwise.
     */
    private function hasNotificationBeenSent(int $leaveRequestId, string $notificationType): bool
    {
        return LeaveNotification::where('leave_request_id', $leaveRequestId)
            ->where('notification_type', $notificationType)
            ->exists();
    }

    /**
     * Records a sent notification in the database.
     *
     * @param int $leaveRequestId The ID of the leave request.
     * @param int $residentId The ID of the resident.
     * @param string $notificationType The unique identifier for the notification.
     * @param string|null $smsGatewayMessageId Optional: The message ID returned by the SMS gateway.
     * @return void
     */
    private function recordNotification(int $leaveRequestId, int $residentId, string $notificationType, $smsGatewayMessageId = null): void
    {
        try {
            LeaveNotification::create([
                'leave_request_id' => $leaveRequestId,
                'resident_id' => $residentId,
                'notification_type' => $notificationType,
                'sms_gateway_message_id' => $smsGatewayMessageId,
                'is_read' => false, // Default to false
            ]);
            Log::info("Recorded leave notification for Request ID {$leaveRequestId}, type: {$notificationType}, Gateway ID: " . ($smsGatewayMessageId !== null ? $smsGatewayMessageId : 'N/A') . ".");
        } catch (\Exception $e) {
            Log::error("Failed to record leave notification for Request ID {$leaveRequestId}, type: {$notificationType}: " . $e->getMessage());
        }
    }

    /**
     * Dummy function to simulate sending an SMS notification.
     * IMPORTANT: Replace this with your actual SMS gateway integration.
     *
     * @param string $to The recipient's phone number.
     * @param string $message The content of the notification.
     * @param string $recipientName A descriptive name for logging.
     * @return string|null The message ID returned by the SMS gateway on success, or null on failure.
     */
    private function sendSmsNotification(string $to, string $message, string $recipientName = 'Recipient')
    {
        // --- REPLACE THIS SECTION WITH YOUR ACTUAL SMS GATEWAY INTEGRATION ---
        // For demonstration, we'll just log and return a dummy ID.

        Log::info("SIMULATED SMS to {$recipientName} ({$to}): '{$message}'");

        try {
            // Example using a hypothetical SmsService facade:
            // $response = SmsService::send($to, $message);
            // return $response->message_id;

            // For testing, return a dummy message ID:
            $dummyGatewayMessageId = 'LVE_MSG_' . uniqid();
            Log::info("SIMULATED SMS SENT. Dummy Gateway ID: {$dummyGatewayMessageId}");
            return $dummyGatewayMessageId;
        } catch (\Exception $e) {
            Log::error("Failed to send SIMULATED SMS to {$recipientName} ({$to}): " . $e->getMessage());
            return null;
        }
    }
}
