<?php

namespace App\Providers;

use App\Services\Contracts\EzApiServiceInterface;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\EzApiService;
use App\Services\OrderService;
use Illuminate\Support\ServiceProvider;

use App\Models\LocalOrder;
use App\Repositories\LocalOrderRepository;
use App\Models\EzOrder;
use App\Repositories\EzOrderRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        // to bind repository classes
        $this->app->bind(LocalOrderRepository::class, function ($app) {
            return new LocalOrderRepository(new LocalOrder());
        });
        $this->app->bind(EzOrderRepository::class, function ($app) {
            return new EzOrderRepository(new EzOrder());
        });

        // to bind interface to concrete implementation class
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(EzApiServiceInterface::class, EzApiService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
