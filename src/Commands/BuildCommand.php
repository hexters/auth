<?php

namespace Hexters\Auth\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

use function Laravel\Prompts\{select, text, warning};


class BuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:auth 
                                    { name? : Name of authentication page } 
                                    { --guard= : Select existing guard }
                                    { --prefix= : Prefix for auth page }
                                    { --layout= : Orientation auth layout already in sidebar or navbar }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new auth page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        if (empty($name)) {
            $name = text('Page Name', 'Name of auth page', '', true);
        }

        $name = Str::of($name)->studly();
        $lowerName = $name->lower();

        $guard = $this->option('guard');
        $guards = array_keys(config('auth.guards'));
        if (empty($guard)) {
            $guard = select('Guard', $guards);
        }
        
        $prefix = $this->option('prefix');
        if (empty($prefix)) {
            $prefix = text('Prefix Page (optional)', $lowerName, $lowerName, false);
        }

        $layout = $this->option('layout');
        if (empty($layout)) {
            $layout = select('Layout Auth Page', [
                'sidebar' => 'With Sidebar',
                'navbar' => 'With Navbar'
            ], default: 'With Sidebar', validate: ['required', 'in:sidebar,navbar']);
        }

        if(! in_array($guard, $guards)) {
            warning('Incorrect guard format see auth.php config file!');
            exit();
        }

        if(! in_array($layout, ['sidebar', 'navbar'])) {
            warning('The layout format you entered is incorrect! Select sidebar or navbar');
            exit();
        }

        $layout = strtolower($layout);

        $this->setStub($name, $lowerName, $prefix, $lowerName, $guard, $layout);
    }

    protected function setStub($name, $lowerName, $prefix, $as, $guard, $layout)
    {
        $replaces = [
            '{{ name }}' => $name,
            '{{ lowerName }}' => $lowerName,
            '{{ prefix }}' => $prefix,
            '{{ as }}' => Route::has('login') ? "{$as}." : '',
            '{{ guard }}' => $guard,
        ];

        // Blade
        /// Login
        $login = Str::of(file_get_contents(__DIR__ . '/stubs/blade/login.stub'))->replace(array_keys($replaces), array_values($replaces));
        $this->call("make:volt", ['name' => "auth/{$lowerName}/login"]);
        file_put_contents(resource_path("views/livewire/auth/{$lowerName}/login.blade.php"), $login);

        // Register
        $register = Str::of(file_get_contents(__DIR__ . '/stubs/blade/register.stub'))->replace(array_keys($replaces), array_values($replaces));
        $this->call("make:volt", ['name' => "auth/{$lowerName}/register"]);
        file_put_contents(resource_path("views/livewire/auth/{$lowerName}/register.blade.php"), $register);

        // Forgot Password
        $forgotPassword = Str::of(file_get_contents(__DIR__ . '/stubs/blade/forgot-password.stub'))->replace(array_keys($replaces), array_values($replaces));
        $this->call("make:volt", ['name' => "auth/{$lowerName}/forgot-password"]);
        file_put_contents(resource_path("views/livewire/auth/{$lowerName}/forgot-password.blade.php"), $forgotPassword);

        // Reset Password
        $resetPassword = Str::of(file_get_contents(__DIR__ . '/stubs/blade/reset-password.stub'))->replace(array_keys($replaces), array_values($replaces));
        $this->call("make:volt", ['name' => "auth/{$lowerName}/reset-password"]);
        file_put_contents(resource_path("views/livewire/auth/{$lowerName}/reset-password.blade.php"), $resetPassword);

        $verfiyEmail = Str::of(file_get_contents(__DIR__ . '/stubs/blade/verify-email.stub'))->replace(array_keys($replaces), array_values($replaces));
        $this->call("make:volt", ['name' => "auth/{$lowerName}/verify-email"]);
        file_put_contents(resource_path("views/livewire/auth/{$lowerName}/verify-email.blade.php"), $verfiyEmail);


        // Route
        $route = Str::of(file_get_contents(__DIR__ . '/stubs/route.stub'))->replace(array_keys($replaces), array_values($replaces));
        $routePath = base_path('routes/auth');
        if (!is_dir($routePath)) {
            File::makeDirectory($routePath);
        }
        file_put_contents("{$routePath}/{$lowerName}.php", $route);

        // Form
        $form = Str::of(file_get_contents(__DIR__ . '/stubs/forms/login.form.stub'))->replace(array_keys($replaces), array_values($replaces));
        $loginForm = "{$name}LoginForm";
        $this->call("livewire:form", ['name' => "Auth/{$loginForm}"]);
        file_put_contents(app_path("Livewire/Forms/Auth/{$loginForm}.php"), $form);

        // Home Page
        $welcome = Str::of(file_get_contents(__DIR__ . '/stubs/blade/welcome.stub'))->replace(array_keys($replaces), array_values($replaces));
        $this->call("make:volt", ['name' => "{$lowerName}/welcome"]);
        file_put_contents(resource_path("views/livewire/{$lowerName}/welcome.blade.php"), $welcome);

        // Middleware
        $guest = Str::of(file_get_contents(__DIR__ . '/stubs/middlewares/guest.stub'))->replace(array_keys($replaces), array_values($replaces));
        $this->call('make:middleware', ['name' => $mid = "{$name}GuestMiddleware"]);
        file_put_contents(app_path("Http/Middleware/{$mid}.php"), $guest);


        $auth = Str::of(file_get_contents(__DIR__ . '/stubs/middlewares/auth.stub'))->replace(array_keys($replaces), array_values($replaces));
        $this->call('make:middleware', ['name' => $mid = "{$name}AuthMiddleware"]);
        file_put_contents(app_path("Http/Middleware/{$mid}.php"), $auth);

        $verified = Str::of(file_get_contents(__DIR__ . '/stubs/middlewares/verified.stub'))->replace(array_keys($replaces), array_values($replaces));
        $this->call('make:middleware', ['name' => $mid = "{$name}VerifiedMiddleware"]);
        file_put_contents(app_path("Http/Middleware/{$mid}.php"), $verified);

        // Layout
        $authLayout = Str::of(file_get_contents(__DIR__ . "/stubs/blade/layouts/auth.{$layout}.stub"))->replace(array_keys($replaces), array_values($replaces));
        $dir = resource_path("views/components/layouts/{$lowerName}");
        if (!is_dir($dir)) {
            File::makeDirectory($dir);
        }
        file_put_contents("{$dir}/auth.blade.php", $authLayout);

        $guestLayout = Str::of(file_get_contents(__DIR__ . '/stubs/blade/layouts/guest.stub'))->replace(array_keys($replaces), array_values($replaces));
        $dir = resource_path("views/components/layouts/{$lowerName}");
        if (!is_dir($dir)) {
            File::makeDirectory($dir);
        }
        file_put_contents("{$dir}/guest.blade.php", $guestLayout);

        // Controller
        $verifyController = Str::of(file_get_contents(__DIR__ . '/stubs/controllers/verify.stub'))->replace(array_keys($replaces), array_values($replaces));
        file_put_contents(app_path("Http/Controllers/{$name}VerifyEmailController.php"), $verifyController);
    }
}
