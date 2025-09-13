<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class StartQueueWorker
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(JobQueued $event)
    {
        Log::info('🚀 JobQueued event fired for queue: ' . $event->job->getQueue());

        Artisan::call('queue:process-once');
        // Run queue worker in the background until queue is empty
        // exec('php artisan queue:process-once > /dev/null 2>&1 &');
        // exec('nohup php ' . base_path('artisan') . ' queue:process-once > /dev/null 2>&1 &');

    }
}
