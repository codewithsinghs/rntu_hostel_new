<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View; // For rendering Blade views for email content
use Carbon\Carbon; // For handling dates/times

class NotificationService
{
    /**
     * Dispatches a notification based on the event type and provided data.
     *
     * @param string $eventType - A unique identifier for the notification (e.g., 'PAYMENT_REMINDER', 'LEAVE_APPROVAL')
     * @param array $data - Associative array of data to populate the notification (e.g., ['student_name' => 'John Doe', 'amount' => 5000])
     * @param string|array $recipients - Email address, phone number, or an array like ['email' => 'a@b.com', 'phone' => '+911234567890']
     * @param array $channels - Array of channels to send through (e.g., ['sms'], ['email'], ['sms', 'email'])
     * @param \Carbon\Carbon|null $sendAt - Optional: Carbon instance for scheduling the notification in the future.
     * @return array
     */
    public static function dispatch(
        string $eventType,
        array $data,
        string|array $recipients,
        array $channels = ['sms', 'email'],
        ?\Carbon\Carbon $sendAt = null
    ): array {
        // Normalize recipients to an array
        $recipientEmail = is_array($recipients) ? ($recipients['email'] ?? null) : null;
        $recipientPhoneNumber = is_array($recipients) ? ($recipients['phone'] ?? null) : null;
        if (is_string($recipients)) { // Assume string is email if it contains '@', else phone
            if (str_contains($recipients, '@')) {
                $recipientEmail = $recipients;
            } else {
                $recipientPhoneNumber = $recipients;
            }
        }

        // Prepare content based on event type
        list($smsMessage, $emailSubject, $emailBody) = self::prepareNotificationContent($eventType, $data);

        if (empty($smsMessage) && empty($emailSubject) && empty($emailBody)) {
             Log::warning("Notification content is empty for event '{$eventType}'. Not dispatching.");
             return ['success' => false, 'message' => "No content generated for this event type."];
        }

        // Dispatch the job
        $job = new SendNotificationJob(
            $smsMessage,
            $emailSubject,
            $emailBody, // Pass the rendered email body
            $channels,
            $recipientEmail,
            $recipientPhoneNumber
        );

        if ($sendAt && $sendAt->isFuture()) {
            dispatch($job)->delay($sendAt);
            Log::info("Notification for event '{$eventType}' scheduled for {$sendAt->format('Y-m-d H:i:s')}.");
            return ['success' => true, 'message' => 'Notification scheduled.'];
        } else {
            dispatch($job); // Dispatch immediately
            Log::info("Notification for event '{$eventType}' dispatched immediately.");
            return ['success' => true, 'message' => 'Notification dispatched.'];
        }
    }

    /**
     * Determines the SMS message, email subject, and email body based on the event type and data.
     * This method is where you define the content for each notification type.
     *
     * @param string $eventType
     * @param array $data
     * @return array [smsMessage, emailSubject, emailBody]
     */
    protected static function prepareNotificationContent(string $eventType, array $data): array
    {
        $smsMessage = '';
        $emailSubject = '';
        $emailBody = ''; // This will hold the rendered HTML content

        // Safely get data with default values
        $studentName = $data['student_name'] ?? 'Resident';
        $parentName = $data['parent_name'] ?? 'Parent';
        $amount = $data['amount'] ?? 'N/A';
        $dueDate = isset($data['due_date']) ? Carbon::parse($data['due_date'])->format('D, d M Y') : 'N/A';
        $leaveStartDate = isset($data['leave_start_date']) ? Carbon::parse($data['leave_start_date'])->format('D, d M Y') : 'N/A';
        $leaveEndDate = isset($data['leave_end_date']) ? Carbon::parse($data['leave_end_date'])->format('D, d M Y') : 'N/A';
        $reason = $data['reason'] ?? 'not specified';
        $status = $data['status'] ?? 'pending'; // 'approved', 'denied' for resident
        $hostelName = config('app.name', 'Hostel Management'); // Get from config

        switch ($eventType) {
            case 'DAILY_PAYMENT_REMINDER':
                $smsMessage = "Dear {$studentName}, your hostel fee of INR {$amount} is due by {$dueDate}. Please pay promptly. - {$hostelName}";
                $emailSubject = "Daily Payment Reminder: Hostel Fees Due";
                $emailBody = View::make('emails.daily-payment-reminder', compact('studentName', 'amount', 'dueDate', 'hostelName'))->render();
                break;

            case 'PARENT_DAILY_PAYMENT_REMINDER':
                $smsMessage = "Dear {$parentName}, your ward {$studentName}'s hostel fee of INR {$amount} is due by {$dueDate}. Please facilitate payment. - {$hostelName}";
                $emailSubject = "Reminder for Your Ward's Hostel Fees";
                $emailBody = View::make('emails.parent-daily-payment-reminder', compact('parentName', 'studentName', 'amount', 'dueDate', 'hostelName'))->render();
                break;

            case 'LEAVE_APPROVAL_STATUS':
                // Note: The 'status' here should be the final admin decision ('approved' or 'denied')
                $smsMessage = "Dear {$studentName}, your leave request from {$leaveStartDate} to {$leaveEndDate} has been {$status}. - {$hostelName}";
                $emailSubject = "Your Hostel Leave Request Status: " . ucfirst($status);
                $emailBody = View::make('emails.leave-approval-status', compact('studentName', 'leaveStartDate', 'leaveEndDate', 'reason', 'status', 'hostelName'))->render();
                break;

            // Add more specific event types here as needed
            case 'ROOM_ALLOCATION':
                $roomNumber = $data['room_number'] ?? 'N/A';
                $smsMessage = "Dear {$studentName}, your room has been allocated. Room No: {$roomNumber}. - {$hostelName}";
                $emailSubject = "Room Allocation Confirmation: Your Room Number is {$roomNumber}";
                $emailBody = View::make('emails.room-allocation', compact('studentName', 'roomNumber', 'hostelName'))->render();
                break;

            default:
                Log::warning("Unhandled notification event type: {$eventType}. Using generic message.");
                $smsMessage = $data['message'] ?? "A new update from {$hostelName}.";
                $emailSubject = $data['subject'] ?? "Notification from {$hostelName}";
                // Render a generic template for unhandled types
                $emailBody = View::make('emails.generic-notification-default', compact('data', 'hostelName'))->render();
                break;
        }

        return [$smsMessage, $emailSubject, $emailBody];
    }
}