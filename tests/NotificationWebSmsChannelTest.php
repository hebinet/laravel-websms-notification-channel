<?php

namespace Hebinet\Notifications\Channels {

    use Illuminate\Tests\Notifications\NotificationWebSmsChannelTest;

    function config($cmd)
    {
        return NotificationWebSmsChannelTest::$functions->config($cmd);
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

        public function setUp()
        {
            self::$functions = m::mock();
        }

        public function tearDown()
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

            self::$functions->shouldReceive('config')->with('websms')->twice()->andReturns([
                'test' => false,
                'verbose' => false
            ]);

            $client->shouldReceive('send')->withArgs(function ($message, $count) {
                if ($message instanceof TextMessage && is_int($count)) {
                    return true;
                }
                return false;
            })->once();

            $channel->send($notifiable, $notification);
        }

        public function testSmsCount() {
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
            return 'this is my message';
        }
    }
}