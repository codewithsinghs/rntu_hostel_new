<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\PaymentNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
// Example: If using a custom SMS service class
// use App\Services\SmsService;

class SendPaymentDueNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:send-payment-dues';

    /**
     * The console command description.
     */
    protected $description = 'Sends payment due notifications to residents and parents based on payment due dates.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting payment due notifications...');
        Log::info('Payment due notification job started.');

        $today = Carbon::today();

        $payments = Payment::where('payment_status', 'Pending')
            ->whereNotNull('due_date')
            ->where('due_date', '>', $today)
            ->with('resident')
            ->get();

        $sentCount = 0;

        foreach ($payments as $payment) {
            if (!$payment->resident || empty($payment->resident->number)) {
                Log::warning("Skipping payment ID {$payment->id}: No associated resident or resident number found.");
                continue;
            }

            $dueDate = Carbon::parse($payment->due_date);
            $daysRemaining = $today->diffInDays($dueDate, false);

            $residentName = $payment->resident->name;
            $residentNumber = $payment->resident->number;
            $parentNumber = $payment->resident->parent_no;

            $messageBase = "Dear {$residentName}, your payment of INR {$payment->total_amount} is due on {$dueDate->format('M d, Y')}. ";

            // --- Notification Logic (Same as before) ---

            // Rule 1: 15 > 10 days from now (11-15 days remaining)
            if ($daysRemaining >= 11 && $daysRemaining <= 15) {
                $notificationType = 'due_15_10_days_remaining';
                if (!$this->hasNotificationBeenSent($payment->id, $notificationType)) {
                    $message = $messageBase . "Please make the payment soon to avoid late fees. (Reminder 1/3)";
                    $gatewayMessageId = $this->sendNotification($residentNumber, $message, $residentName);
                    if ($parentNumber) {
                        $this->sendNotification($parentNumber, $message, $residentName . "'s parent"); // Parent notification won't store ID here
                    }
                    $this->recordNotification($payment->id, $payment->resident->id, $notificationType, $gatewayMessageId); // Pass ID
                    $sentCount++;
                    Log::info("Sent 1st reminder for payment ID {$payment->id} (15-10 days tier). Days remaining: {$daysRemaining}");
                }
            }
            // Rule 2: 10 > 5 days from now (6-10 days remaining)
            else if ($daysRemaining >= 6 && $daysRemaining <= 10) {
                // First notification for this tier (e.g., send when 9 or 10 days remain)
                if ($daysRemaining === 10 || $daysRemaining === 9) {
                    $notificationType1 = 'due_10_5_days_remaining_1';
                    if (!$this->hasNotificationBeenSent($payment->id, $notificationType1)) {
                        $message = $messageBase . "This is your first reminder for this window. Please ensure timely payment. (Reminder 2/3)";
                        $gatewayMessageId = $this->sendNotification($residentNumber, $message, $residentName);
                        if ($parentNumber) {
                            $this->sendNotification($parentNumber, $message, $residentName . "'s parent");
                        }
                        $this->recordNotification($payment->id, $payment->resident->id, $notificationType1, $gatewayMessageId);
                        $sentCount++;
                        Log::info("Sent 1st reminder for payment ID {$payment->id} (10-5 days tier, 1 of 2). Days remaining: {$daysRemaining}");
                    }
                }

                // Second notification for this tier (e.g., send when 7 or 6 days remain)
                if ($daysRemaining === 7 || $daysRemaining === 6) {
                    $notificationType2 = 'due_10_5_days_remaining_2';
                    if (!$this->hasNotificationBeenSent($payment->id, $notificationType2)) {
                        $message = $messageBase . "Just a friendly reminder to complete your payment soon. (Reminder 3/3)";
                        $gatewayMessageId = $this->sendNotification($residentNumber, $message, $residentName);
                        if ($parentNumber) {
                            $this->sendNotification($parentNumber, $message, $residentName . "'s parent");
                        }
                        $this->recordNotification($payment->id, $payment->resident->id, $notificationType2, $gatewayMessageId);
                        $sentCount++;
                        Log::info("Sent 2nd reminder for payment ID {$payment->id} (10-5 days tier, 2 of 2). Days remaining: {$daysRemaining}");
                    }
                }
            }
            // Rule 3: 5 > 0 days from now (1-5 days remaining)
            else if ($daysRemaining >= 1 && $daysRemaining <= 5) {
                // First notification for this tier (e.g., send when 4 or 5 days remain)
                if ($daysRemaining === 5 || $daysRemaining === 4) {
                    $notificationType1 = 'due_5_0_days_remaining_1';
                    if (!$this->hasNotificationBeenSent($payment->id, $notificationType1)) {
                        $message = $messageBase . "Your payment is almost due! Please make it today. (Final Push 1/3)";
                        $gatewayMessageId = $this->sendNotification($residentNumber, $message, $residentName);
                        if ($parentNumber) {
                            $this->sendNotification($parentNumber, $message, $residentName . "'s parent");
                        }
                        $this->recordNotification($payment->id, $payment->resident->id, $notificationType1, $gatewayMessageId);
                        $sentCount++;
                        Log::info("Sent 1st reminder for payment ID {$payment->id} (5-0 days tier, 1 of 3). Days remaining: {$daysRemaining}");
                    }
                }

                // Second notification for this tier (e.g., send when 3 or 2 days remain)
                if ($daysRemaining === 3 || $daysRemaining === 2) {
                    $notificationType2 = 'due_5_0_days_remaining_2';
                    if (!$this->hasNotificationBeenSent($payment->id, $notificationType2)) {
                        $message = $messageBase . "Only a few days left to clear your payment. Avoid late fees. (Final Push 2/3)";
                        $gatewayMessageId = $this->sendNotification($residentNumber, $message, $residentName);
                        if ($parentNumber) {
                            $this->sendNotification($parentNumber, $message, $residentName . "'s parent");
                        }
                        $this->recordNotification($payment->id, $payment->resident->id, $notificationType2, $gatewayMessageId);
                        $sentCount++;
                        Log::info("Sent 2nd reminder for payment ID {$payment->id} (5-0 days tier, 2 of 3). Days remaining: {$daysRemaining}");
                    }
                }

                // Third notification for this tier (e.g., send when 1 day remains)
                if ($daysRemaining === 1) {
                    $notificationType3 = 'due_5_0_days_remaining_3';
                    if (!$this->hasNotificationBeenSent($payment->id, $notificationType3)) {
                        $message = $messageBase . "Final reminder: Your payment is due tomorrow! Please clear it immediately. (Final Push 3/3)";
                        $gatewayMessageId = $this->sendNotification($residentNumber, $message, $residentName);
                        if ($parentNumber) {
                            $this->sendNotification($parentNumber, $message, $residentName . "'s parent");
                        }
                        $this->recordNotification($payment->id, $payment->resident->id, $notificationType3, $gatewayMessageId);
                        $sentCount++;
                        Log::info("Sent 3rd reminder for payment ID {$payment->id} (5-0 days tier, 3 of 3). Days remaining: {$daysRemaining}");
                    }
                }
            }
        }

        $this->info("Payment due notifications finished. Total notifications sent: {$sentCount}");
        Log::info("Payment due notification job finished. Total notifications sent: {$sentCount}");
        return Command::SUCCESS;
    }

    /**
     * Checks if a specific notification type has already been sent for a given payment.
     *
     * @param int $paymentId The ID of the payment.
     * @param string $notificationType The unique identifier for the notification.
     * @return bool True if the notification has been sent, false otherwise.
     */
    protected function hasNotificationBeenSent(int $paymentId, string $notificationType): bool
    {
        return PaymentNotification::where('payment_id', $paymentId)
            ->where('notification_type', $notificationType)
            ->exists();
    }

    /**
     * Records a sent notification in the database.
     *
     * @param int $paymentId The ID of the payment.
     * @param int $residentId The ID of the resident.
     * @param string $notificationType The unique identifier for the notification.
     * @param string|null $smsGatewayMessageId Optional: The message ID returned by the SMS gateway.
     * @return void
     */
    protected function recordNotification(int $paymentId, int $residentId, string $notificationType, $smsGatewayMessageId = null): void
    {
        try {
            PaymentNotification::create([
                'payment_id' => $paymentId,
                'resident_id' => $residentId,
                'notification_type' => $notificationType,
                'sms_gateway_message_id' => $smsGatewayMessageId, // Store the ID here
                'is_read' => false, // Default to false
            ]);
            // FIX: Replaced ?? with ternary operator for PHP 5.x compatibility
            Log::info("Recorded notification for payment ID {$paymentId}, type: {$notificationType}, Gateway ID: " . ($smsGatewayMessageId !== null ? $smsGatewayMessageId : 'N/A') . ".");
        } catch (\Exception $e) {
            Log::error("Failed to record notification for payment ID {$paymentId}, type: {$notificationType}: " . $e->getMessage());
        }
    }

    /**
     * Function to send a notification (e.g., SMS).
     * IMPORTANT: Replace the dummy logic with your actual SMS gateway integration.
     *
     * @param string $to The recipient's phone number.
     * @param string $message The content of the notification.
     * @param string $recipientName A descriptive name for logging.
     * @return string|null The message ID returned by the SMS gateway on success, or null on failure.
     */
    protected function sendNotification(string $to, string $message, string $recipientName = 'Recipient')
    {
        // --- REPLACE THIS SECTION WITH YOUR ACTUAL SMS GATEWAY INTEGRATION ---
        // For demonstration, we'll just log and return a dummy ID.

        Log::info("SIMULATED SMS to {$recipientName} ({$to}): '{$message}'");
        $this->line("SIMULATED: Sending to {$recipientName} ({$to}): {$message}");

        try {
            // For testing, return a dummy message ID:
            $dummyGatewayMessageId = 'MSG_' . uniqid();
            Log::info("SIMULATED SMS SENT. Dummy Gateway ID: {$dummyGatewayMessageId}");
            return $dummyGatewayMessageId;
        } catch (\Exception $e) {
            Log::error("Failed to send SIMULATED SMS to {$recipientName} ({$to}): " . $e->getMessage());
            return null; // Return null if sending failed
        }
    }
}
