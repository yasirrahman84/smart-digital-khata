<?php

namespace App\Providers;

use App\Services\Sms\Drivers\HttpDriver;
use App\Services\Sms\Drivers\LogDriver;
use App\Services\Sms\Drivers\TwilioDriver;
use App\Services\Sms\SmsDriver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsDriver::class, function () {
            return match (config('services.sms.driver')) {
                'twilio' => new TwilioDriver(),
                'http' => new HttpDriver(),
                default => new LogDriver(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
