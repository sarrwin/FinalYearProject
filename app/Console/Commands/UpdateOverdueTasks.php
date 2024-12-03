<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskOverdueNotification;
use Illuminate\Support\Facades\Log;
class UpdateOverdueTasks extends Command
{
    protected $signature = 'tasks:mark-overdue';
    protected $description = 'Mark overdue tasks and notify project members';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $today = Carbon::today();
        Log::info("MarkOverdueTasks command started.");

        $overdueTasks = Task::where('status', 'todo')
            ->whereDate('due_date', '<', $today)
            ->get();

        if ($overdueTasks->isEmpty()) {
            Log::info("No overdue tasks found.");
            return;
        }

        foreach ($overdueTasks as $task) {
            // Update task status to overdue
            $task->status = 'overdue';
            $task->save();

            Log::info("Task '{$task->title}' marked as overdue.");

            // Get project members (supervisor and students)
            $project = $task->project;
            $recipients = $project->students->merge([$project->supervisor]);

            // Send email notifications
            foreach ($recipients as $recipient) {
                Log::info("Sending email to {$recipient->email} about overdue task '{$task->title}'.");

                Mail::send('emails.overdue_task', ['task' => $task, 'recipient' => $recipient], function ($message) use ($recipient) {
                    $message->to($recipient->email);
                    $message->subject('Task Overdue Notification');
                });
            }
        }

        Log::info("MarkOverdueTasks command finished.");
    }
}
