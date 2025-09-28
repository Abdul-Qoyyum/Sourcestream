<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('articles:aggregate business')
            ->hourly()
            ->withoutOverlapping();
        $schedule->command('articles:aggregate entertainment')
            ->hourly()
            ->withoutOverlapping();
        $schedule->command('articles:aggregate general')
            ->hourly()
            ->withoutOverlapping();
        $schedule->command('articles:aggregate health')
            ->hourly()
            ->withoutOverlapping();
        $schedule->command('articles:aggregate science')
            ->hourly()
            ->withoutOverlapping();
        $schedule->command('articles:aggregate sports')
            ->hourly()
            ->withoutOverlapping();
        $schedule->command('articles:aggregate technology')
            ->hourly()
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
