<?php

namespace Smbear\Payment\Providers;

use Smbear\Payment\Payment;
use Illuminate\Support\ServiceProvider;
use Smbear\Payment\Providers\EventServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/payment.php' => config_path('payment.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/payment.php', 'payment'
        );

        $this->app->singleton('payment',function (){
            return new Payment();
        });
    }
}
