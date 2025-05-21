<?php

namespace App\Console\Commands;

use App\Services\LogRotationManager;
use Illuminate\Console\Command;

class RotateLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:rotate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate and clean up log files';

    /**
     * Execute the console command.
     */
    public function handle(LogRotationManager $manager)
    {
        $this->info('Starting log rotation...');
        
        try {
            $manager->handle();
            $this->info('Log rotation completed successfully.');
        } catch (\Throwable $e) {
            $this->error('Log rotation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
