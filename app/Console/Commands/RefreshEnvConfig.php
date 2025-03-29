<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RefreshEnvConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:env-refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the .env variables in the application configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('config:clear');
        Artisan::call('config:cache');
        
        $this->info('Environment configuration refreshed successfully!');
        
        return Command::SUCCESS;
    }
} 