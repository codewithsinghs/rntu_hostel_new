<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendPaymentDueNotifications; // Import your custom command

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     * Register your custom command here so Laravel knows about it.
     *
     * @var array
     */
    protected $commands = [
        SendPaymentDueNotifications::class, // Your payment due notifications command
    ];

    /**
     * Define the application's command schedule.
     * This is where you configure when your Artisan commands should run.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the 'notifications:send-payment-dues' command to run daily at 9:00 AM.
        // You can adjust '09:00' to any time that suits your needs (e.g., '07:30', '14:00').
        $schedule->command('notifications:send-payment-dues')->dailyAt('09:00');

        // You might also run other scheduled tasks here, for example:
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands'); // Loads commands from the 'Commands' directory.

        require base_path('routes/console.php'); // Includes console routes.
    }
}

