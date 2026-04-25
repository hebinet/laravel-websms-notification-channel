<?php

use Hebinet\Tests\Notifications\Stubs\ConfigStub;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Facade;

uses()->beforeAll(function () {
    $app = Container::getInstance() ?: new Container();
    Container::setInstance($app);
    Facade::setFacadeApplication($app);

    $app->singleton('events', fn ($app) => new Dispatcher($app));
    $app->singleton('config', fn () => new ConfigStub());
    $app->singleton('cache', fn () => new class {
        public function refreshEventDispatcher(): void {}
    });
})->in('Unit');
