<?php

namespace App\Services;

use App\Mail\GenericNotificationMail; // Make sure this Mailable exists
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send a generic email notification.
     *
     * @param string $recipientEmail
     * @param string $subject
     * @param string $messageBody (Can be HTML or Markdown)
     * @return array
     */
    public static function send(string $recipientEmail, string $subject, string $messageBody): array
    {
        try {
            Mail::to($recipientEmail)->send(new GenericNotificationMail($subject, $messageBody));
            Log::info("Email sent successfully to {$recipientEmail} with subject: '{$subject}'.");
            return ['success' => true];
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$recipientEmail} with subject: '{$subject}': " . $e->getMessage(), ['exception' => $e]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}