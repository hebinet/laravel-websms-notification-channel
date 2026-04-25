<?php

namespace Hebinet\Notifications;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use WebSms\AuthenticationMode;
use WebSms\Client;

class WebSmsChannelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/websms.php' => $this->app->configPath('websms.php'),
        ], 'websms');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/websms.php', 'websms');

        Notification::resolved(function(ChannelManager $service) {
            $service->extend('websms', static function() {
                if (Config::get('websms.token') !== null) {
                    return new Channels\WebSmsChannel(
                        new Client(
                            Config::get('websms.gateway'),
                            Config::get('websms.token'),
                            null,
                            AuthenticationMode::ACCESS_TOKEN
                        )
                    );
                }

                return new Channels\WebSmsChannel(
                    new Client(
                        Config::get('websms.gateway'),
                        Config::get('websms.username'),
                        Config::get('websms.password')
                    )
                );
            });
        });
    }
}
