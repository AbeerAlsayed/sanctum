<?php

namespace YourName\SanctumInstaller;

use Illuminate\Support\ServiceProvider;
use YourName\SanctumInstaller\Console\InstallSanctumCommand;

class SanctumInstallerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            InstallSanctumCommand::class,
        ]);
    }
}
