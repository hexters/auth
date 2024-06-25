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
        // Get current route 
        $currentRoute = is_file(base_path('routes/web.php')) ? file_get_contents(base_path('routes/web.php')) : null;

        $this->call('livewire:publish', [
            '--config'
        ]);

        $this->call('vendor:publish', ['--tag' => 'mary.config']);
        $this->call('volt:install');
        $this->call('mary:install');
        $this->call('view:clear');
        $this->call('livewire:layout', ['--force' => true]);

        $this->reset($currentRoute);
    }

    protected function reset($currentRoute = null)
    {
        // Reset route web
        $stub = file_get_contents(__DIR__ . '/stubs/web.stub');
        $content = $currentRoute ?? $stub;
        file_put_contents(base_path('routes/web.php'), $content);

        // Reset Livewire welcome
        if (file_exists($welcome = app_path('Livewire/Welcome.php'))) {
            @unlink($welcome);
        }

        // Reset welcome blade
        if (file_exists($welcome = resource_path('views/livewire/welcome.blade.php'))) {
            @unlink($welcome);
        }
    }
}
