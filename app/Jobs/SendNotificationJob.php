<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\SmsService;
use App\Services\EmailService; // Ensure this is imported
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $smsMessage;
    protected string $emailSubject;
    protected string $emailBody; // This will hold the already rendered HTML/Markdown content
    protected array $channels;
    protected ?string $recipientEmail;
    protected ?string $recipientPhoneNumber;

    /**
     * Create a new job instance.
     *
     * @param string $smsMessage
     * @param string $emailSubject
     * @param string $emailBody // Now accepts the already rendered HTML/Markdown body
     * @param array $channels
     * @param string|null $recipientEmail
     * @param string|null $recipientPhoneNumber
     */
    public function __construct(
        string $smsMessage,
        string $emailSubject,
        string $emailBody, // Pass the rendered body
        array $channels,
        ?string $recipientEmail,
        ?string $recipientPhoneNumber
    ) {
        $this->smsMessage = $smsMessage;
        $this->emailSubject = $emailSubject;
        $this->emailBody = $emailBody; // Assign the rendered body
        $this->channels = $channels;
        $this->recipientEmail = $recipientEmail;
        $this->recipientPhoneNumber = $recipientPhoneNumber;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Attempting to send notification via job...");

        if (in_array('sms', $this->channels) && $this->recipientPhoneNumber && !empty($this->smsMessage)) {
            SmsService::send($this->recipientPhoneNumber, $this->smsMessage);
        } else {
            Log::info("SMS skipped: Invalid number, empty message, or not requested.");
        }

        // Use EmailService with the pre-rendered emailBody
        if (in_array('email', $this->channels) && $this->recipientEmail && !empty($this->emailSubject) && !empty($this->emailBody)) {
            EmailService::send($this->recipientEmail, $this->emailSubject, $this->emailBody);
        } else {
            Log::info("Email skipped: Invalid email, empty subject/body, or not requested.");
        }

        Log::info("Notification job completed.");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("SendNotificationJob failed miserably: " . $exception->getMessage(), [
            'exception' => $exception,
            'email' => $this->recipientEmail,
            'phone' => $this->recipientPhoneNumber,
            'channels' => $this->channels
        ]);
        // You might want to send an alert to an admin here for critical failures.
    }
}