<?php

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Sms\SmsSender::class, function (Application $app) {
            $config = $app->make('config')->get('sms');

            switch ($config['driver']) {
                case 'array':
                    return new \App\Services\Sms\ArraySender();
                    break;

                case 'log':
                    return new \App\Services\Sms\LogSender();
                    break;

                default:
                    throw new \InvalidArgumentException('Undefined SMS driver ' . $config['driver']);
                    break;
            }
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
