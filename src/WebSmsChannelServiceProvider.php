<?php

namespace Hebinet\Notifications;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use WebSms\AuthenticationMode;
use WebSms\Client;

class WebSmsChannelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/websms.php' => config_path('websms.php'),
            ], 'config');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/websms.php', 'websms');

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('websms', function ($app) {
                if (!is_null($this->app['config']['websms.token'])) {
                    return new Channels\WebSmsChannel(
                        new Client($this->app['config']['websms.gateway'],
                            $this->app['config']['websms.token'],
                            null,
                            AuthenticationMode::ACCESS_TOKEN
                        )
                    );
                }

                return new Channels\WebSmsChannel(
                    new Client($this->app['config']['websms.gateway'],
                        $this->app['config']['websms.username'],
                        $this->app['config']['websms.password']
                    )
                );
            });
        });
    }
}
