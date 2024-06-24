# Hexters/Auth
Hexters/Auth is a Laravel package that allows you to create multiple authentication pages, similar to Laravel Breeze. The package is supported by [Livewire Volt](https://livewire.laravel.com) and [TailwindCSS](https://tailwindcss.com). For the components, this package uses [maryUI](https://mary-ui.com).

## Installation

Add the package repository by running the following command:
```bash
composer require hexters/auth
```

Install the package by running:
```bash
php artisan auth:install
```

To create a login page, simply follow this command:
```bash
php artisan make:auth
```

And last build assets
```bash
npm run build
```