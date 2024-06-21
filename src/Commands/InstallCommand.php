<?php

namespace Hexters\Auth\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('livewire:publish', [
            '--config'
        ]);

        $this->call('livewire:layout');
        $this->call('vendor:publish', [ '--tag' => 'mary.config' ]);
        $this->call('volt:install');
        $this->call('mary:install');
        $this->call('view:clear');
    }
}
