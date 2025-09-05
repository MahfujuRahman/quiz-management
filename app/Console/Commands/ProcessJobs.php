<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

class ProcessJobs extends Command
{
    protected $signature = 'queue:process-once';
    protected $description = 'Run queue worker only until jobs are done';

    public function handle()
    {
        while (Queue::size('quiz_submissions') > 0) {
            Artisan::call('queue:work', [
                '--queue' => 'quiz_submissions',
                '--tries' => 3,
                '--timeout' => 120,
                '--once' => true,
            ]);
        }


        $this->info('All jobs processed and worker stopped.');
    }
}
