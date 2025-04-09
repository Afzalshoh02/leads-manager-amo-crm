<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\AmoCrmServiceInterface;
use App\Contracts\AmoTokenRepositoryInterface;
use App\Repositories\AmoTokenRepository;
use App\Services\AmoCrmService;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//        $this->app->singleton(AmoCrmServiceInterface::class, function ($app) {
//            return new AmoCrmService($app->make(AmoTokenRepositoryInterface::class));
//        });
//
//        $this->app->singleton(AmoTokenRepositoryInterface::class, AmoTokenRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
