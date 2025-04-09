<?php

namespace YourName\SanctumInstaller\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallApiCommand extends Command
{
    protected $signature = 'install:api';
    protected $description = 'Create the api.php routes file for the project';

    public function handle()
    {
        $this->info('📦 Creating api.php routes file...');

        $routesPath = base_path('routes/api.php');
        if (!File::exists($routesPath)) {
            File::put($routesPath, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n");
            $this->info('🧱 Created api.php routes file successfully.');
        } else {
            $this->info('🛑 api.php routes file already exists.');
        }
    }
}
