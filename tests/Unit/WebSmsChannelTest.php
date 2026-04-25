<?php

use Hebinet\Notifications\Channels\WebSmsChannel;
use Hebinet\Notifications\Events\WebSmsSending;
use Hebinet\Notifications\Events\WebSmsSent;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Mockery as m;
use WebSms\Client;
use WebSms\TextMessage;

beforeEach(function() {
    Event::fake();

    Config::shouldReceive('get')->with('websms.test', m::any())->andReturn(false);
    Config::shouldReceive('get')->with('websms.verbose', m::any())->andReturn(false);

    $this->notifiable = new class {
        use Notifiable;

        public string $phone_number = '5555555555';
    };

    $this->notification = new class extends Notification {
        public function toWebsms($notifiable)
        {
            return 'this is the way';
        }
    };
});

afterEach(function() {
    m::close();
});

it('can send via channel', function() {
    $channel = new WebSmsChannel(
        $client = m::mock(Client::class)
    );

    $client
        ->makePartial()
        ->shouldReceive('send')
        ->withArgs(fn($message, $count) => $message instanceof TextMessage && is_int($count))
        ->once();

    $channel->send($this->notifiable, $this->notification);

    Event::assertDispatched(WebSmsSending::class);
    Event::assertDispatched(WebSmsSent::class);
});
