<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\ArchivedTask;
use Carbon\Carbon;

class ArchiveCompletedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move completed tasks to archived_tasks table after 10 minutes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tasks = Task::where('status', Task::STATUS_COMPLETED)
            ->where('updated_at', '<=', Carbon::now()->subMinutes(Task::ARCHIVE_MINUTES))
            ->get();

        foreach ($tasks as $task) {
            ArchivedTask::create([
                'user_id' => $task->user_id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
            ]);
            $task->delete();
        }
        $this->info(count($tasks) . ' tasks archived successfully.');
    }
}
