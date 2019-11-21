<?php

namespace Hebinet\Notifications\Channels;

use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
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
     * @var string
     */
    private $channelName = 'websms';

    /**
     * Create a new WebSms channel instance.
     *
     * @param  Client  $client
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
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     *
     * @return Response|null
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
        $routeTo = $notifiable->routeNotificationFor($this->channelName, $notification);
        if ($routeTo) {
            $to = $routeTo;
        }

        $to = Arr::wrap($to);

        $message = $notification->toWebsms($notifiable);
        // If false is returned from notification, sending will be aborted!
        if ($message === false) {
            return null;
        }
        if (is_string($message)) {
            $message = new TextMessage($to, trim($message));
        }

        $client = $this->client;
        if ($this->getConfig('test') ?? false) {
            $client->test();
        }
        if ($this->getConfig('verbose') ?? false) {
            $client->setVerbose(true);
        }

        $response = null;
        try {
            event(new NotificationSending($notifiable, $notification, $this->channelName));

            $response = $client->send($message, $this->getSmsCount($message->getMessageContent()));

            event(new NotificationSent($notifiable, $notification, $this->channelName, [
                'response' => $response
            ]));
        } catch (\Exception $e) {
            event(new NotificationFailed($notifiable, $notification, $this->channelName, [
                'exception' => $e
            ]));
        }

        return $response;
    }

    /**
     * @param  string  $message
     *
     * @return int
     */
    public function getSmsCount(string $message): int
    {
        $length = strlen(trim($message));
        if ($length > 160) {
            return (int) ($length / 153) + 1;
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
