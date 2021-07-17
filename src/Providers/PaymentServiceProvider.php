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

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('payment',function (){
            return new Payment();
        });
    }
}
