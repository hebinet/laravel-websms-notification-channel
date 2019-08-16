<?php

namespace Hebinet\Notifications\Channels;


use Illuminate\Notifications\Notification;
use Hebinet\Notifications\Messages\WebSmsMessage;
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
        if (!$to = $notifiable->routeNotificationFor('websms', $notification)) {
            return;
        }

        $message = $notification->toWebsms($notifiable);

        if (is_string($message)) {
            $message = new WebSmsMessage($message);
        }


        $message = new TextMessage([$to], trim($message->content));

        $client = $this->client;
        if (config('websms')['test']) {
            $client->test();
        }

        return $client->send(new TextMessage([$to], trim($message->content)),
            intval(strlen(trim($message->content)) / 160) + 1);
    }
}
