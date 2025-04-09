<?php

namespace YourName\SanctumInstaller\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallSanctumCommand extends Command
{
    protected $signature = 'sanctum:install';
    protected $description = 'Install Sanctum and setup basic auth for API';

    public function handle()
    {
        $this->info('📦 Installing Sanctum...');
        exec('composer require laravel/sanctum');

        $this->info('🧱 Publishing Sanctum config...');
        $this->call('vendor:publish', ['--provider' => 'Laravel\\Sanctum\\SanctumServiceProvider']);
        $this->call('migrate');

        $this->info('🛠 Creating AuthController...');
        $controllerPath = app_path('Http/Controllers/AuthController.php');
        if (!File::exists($controllerPath)) {
            File::ensureDirectoryExists(dirname($controllerPath));
            File::put($controllerPath, $this->getAuthControllerContent());
        }

        // استخدام أمر install:api لإنشاء ملف api.php
        $this->info('🔧 Running install:api to create routes/api.php...');
        $this->call('install:api');

        $this->info('📁 Updating bootstrap/app.php...');
        $this->updateAppFile();

        $this->info('✅ Sanctum installation and setup completed!');
    }

    protected function updateAppFile()
    {
        $appFile = base_path('bootstrap/app.php');
        if (File::exists($appFile)) {
            $content = File::get($appFile);
            if (!str_contains($content, 'withRouting')) {
                $updatedContent = str_replace(
                    '->create();',
                    '->withRouting(web: __DIR__.\'/../routes/web.php\', commands: __DIR__.\'/../routes/console.php\', health: \'/up\', api: __DIR__.\'/../routes/api.php\')' . PHP_EOL . '->create();',
                    $content
                );
                File::put($appFile, $updatedContent);
                $this->info('🔧 Updated bootstrap/app.php to include routes/api.php');
            } else {
                $this->info('bootstrap/app.php already includes routes/api.php');
            }
        } else {
            $this->error("File 'bootstrap/app.php' not found!");
        }
    }

    protected function getAuthControllerContent()
    {
        return <<<PHP
<?php

namespace App\\Http\\Controllers;

use App\\Models\\User;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Hash;

class AuthController extends Controller
{
    public function register(Request \$request)
    {
        \$validatedData = \$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        \$user = User::create([
            'name' => \$validatedData['name'],
            'email' => \$validatedData['email'],
            'password' => Hash::make(\$validatedData['password']),
        ]);

        return response()->json([
            'name' => \$user->name,
            'email' => \$user->email,
        ]);
    }

    public function login(Request \$request)
    {
        \$user = User::where('email', \$request->email)->first();
        if (!\$user || !Hash::check(\$request->password, \$user->password)) {
            return response()->json([
                'message' => ['Username or password incorrect'],
            ]);
        }

        \$user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User logged in successfully',
            'name' => \$user->name,
            'token' => \$user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function logout(Request \$request)
    {
        \$request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully'
        ]);
    }
}
PHP;
    }
}
