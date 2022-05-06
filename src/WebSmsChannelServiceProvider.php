<?php

namespace Hebinet\Notifications;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use WebSms\AuthenticationMode;
use WebSms\Client;

class WebSmsChannelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ( ! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/websms.php' => config_path('websms.php'),
        ], 'config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/websms.php', 'websms');

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('websms', function () {
                if ($this->app['config']['websms.token'] !== null) {
                    return new Channels\WebSmsChannel(
                        new Client(
                            $this->app['config']['websms.gateway'],
                            $this->app['config']['websms.token'],
                            null,
                            AuthenticationMode::ACCESS_TOKEN
                        )
                    );
                }

                return new Channels\WebSmsChannel(
                    new Client(
                        $this->app['config']['websms.gateway'],
                        $this->app['config']['websms.username'],
                        $this->app['config']['websms.password']
                    )
                );
            });
        });
    }
}
