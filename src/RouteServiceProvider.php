<?php

namespace Hexters\Auth;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->routes(function () {
            if (is_dir($routes = base_path('routes/auth'))) {
                collect(File::allFiles($routes))
                    ->each(function ($route) {
                        Route::middleware('web')->group($route->getPathName());
                    });
            }
        });
    }
}
