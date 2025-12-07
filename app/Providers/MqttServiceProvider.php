<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MqttSubscriber;

class MqttServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('mqtt.subscriber', function ($app) {
            return new MqttSubscriber();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}