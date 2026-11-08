<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;
use Modules\Catalog\Services\BookService;
use Modules\Membership\Services\MemberService;
use Modules\Shared\Contracts\BookServiceInterface;
use Modules\Shared\Contracts\MemberServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding contract Shared Kernel ke implementasi masing-masing context.
        $this->app->bind(
            BookServiceInterface::class,
            BookService::class,
        );

        $this->app->bind(
            MemberServiceInterface::class,
            MemberService::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Model di namespace Modules\* tetap memakai factory di Database\Factories.
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Database\\Factories\\' . class_basename($modelName) . 'Factory',
        );
    }
}
