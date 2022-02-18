<?php

namespace Hebinet\Notifications\Channels {

    use Illuminate\Tests\Notifications\NotificationWebSmsChannelTest;

    function config($cmd)
    {
        return NotificationWebSmsChannelTest::$functions->config($cmd);
    }

    function event($event)
    {
        return null;
    }
}

namespace Illuminate\Tests\Notifications {

    use Hebinet\Notifications\Channels\WebSmsChannel;
    use Mockery as m;
    use PHPUnit\Framework\TestCase;
    use Illuminate\Notifications\Notifiable;
    use Illuminate\Notifications\Notification;
    use WebSms\Client;
    use WebSms\TextMessage;

    class NotificationWebSmsChannelTest extends TestCase
    {
        public static $functions;

        public function setUp(): void
        {
            self::$functions = m::mock();
        }

        public function tearDown(): void
        {
            m::close();
        }

        public function testSmsIsSentViaWebsms()
        {
            $notification = new NotificationWebsmsChannelTestNotification;
            $notifiable = new NotificationWebsmsChannelTestNotifiable;

            $channel = new WebSmsChannel(
                $client = m::mock(Client::class)
            );

            self::$functions->shouldReceive('config')
                            ->with('websms')
                            ->twice()
                            ->andReturns([
                                'test' => false,
                                'verbose' => false,
                            ]);

            $client->shouldReceive('send')
                   ->withArgs(function ($message, $count) {
                       return $message instanceof TextMessage && is_int($count);
                   })
                   ->once();

            $channel->send($notifiable, $notification);

            $this->assertEquals('this is the way', $notification->toWebsms($notifiable));
        }

        public function testSmsCount()
        {
            $channel = new WebSmsChannel(
                $client = m::mock(Client::class)
            );

            $this->assertEquals(1, $channel->getSmsCount(str_repeat('1', 50)));
            $this->assertEquals(1, $channel->getSmsCount(str_repeat('1', 160)));
            $this->assertEquals(2, $channel->getSmsCount(str_repeat('1', 161)));
            $this->assertEquals(2, $channel->getSmsCount(str_repeat('1', 305)));
            $this->assertEquals(3, $channel->getSmsCount(str_repeat('1', 306)));
        }
    }

    class NotificationWebsmsChannelTestNotifiable
    {
        use Notifiable;

        public $phone_number = '5555555555';
    }

    class NotificationWebsmsChannelTestNotification extends Notification
    {
        public function toWebsms($notifiable)
        {
            return 'this is the way';
        }
    }
}
