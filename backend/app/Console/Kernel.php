<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ResetEmailConsumer::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Đăng ký các lệnh schedule nếu cần
    }

    protected function commands()
    {
    $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
