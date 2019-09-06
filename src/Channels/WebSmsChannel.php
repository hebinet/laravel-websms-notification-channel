<?php

namespace Hebinet\Notifications\Channels;


use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use WebSms\Client;
use WebSms\Response;
use WebSms\TextMessage;

class WebSmsChannel
{
    /**
     * The WebSms client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * Create a new WebSms channel instance.
     *
     * @param Client $client
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return Response
     *
     * @throws \WebSms\Exception\ApiException
     * @throws \WebSms\Exception\AuthorizationFailedException
     * @throws \WebSms\Exception\HttpConnectionException
     * @throws \WebSms\Exception\ParameterValidationException
     * @throws \WebSms\Exception\UnknownResponseException
     */
    public function send($notifiable, Notification $notification)
    {
        $to = $notifiable->phone_number ?? null;
        $routeTo = $notifiable->routeNotificationFor('websms', $notification);
        if ($routeTo) {
            $to = $routeTo;
        }

        $to = Arr::wrap($to);

        $message = $notification->toWebsms($notifiable);
        if (is_string($message)) {
            $message = new TextMessage($to, trim($message));;
        }

        $client = $this->client;
        if ($this->getConfig('test') ?? false) {
            $client->test();
        }
        if ($this->getConfig('verbose') ?? false) {
            $client->setVerbose(true);
        }

        return $client->send($message, $this->getSmsCount($message->getMessageContent()));
    }

    /**
     * @param string $message
     *
     * @return int
     */
    public function getSmsCount(string $message): int
    {
        $length = strlen(trim($message));
        if ($length > 160) {
            return intval($length / 153) + 1;
        }

        return 1;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    private function getConfig($key)
    {
        return config('websms')[$key];
    }
}
