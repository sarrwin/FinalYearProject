<?php

namespace App\Console;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\AddAppointmentsToLogbook;
use App\Console\Commands\UpdateOverdueTasks;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        AddAppointmentsToLogbook::class,UpdateOverdueTasks::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
        Log::info('Test schedule is running.');
    })->everyMinute();
        Log::info('Schedule run triggered.');
        $schedule->command('appointments:add-to-logbook')->everyMinute();
        $schedule->command('appointments:notify-upcoming')->daily();
        $schedule->command('appointments:send-reminders')->daily();
        $schedule->command('tasks:mark-overdue')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
