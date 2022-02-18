<?php

namespace Hebinet\Notifications\Channels;

use Hebinet\Notifications\Events\WebSmsFailed;
use Hebinet\Notifications\Events\WebSmsSending;
use Hebinet\Notifications\Events\WebSmsSent;
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

    private string $channelName = 'websms';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @throws \WebSms\Exception\ApiException
     * @throws \WebSms\Exception\AuthorizationFailedException
     * @throws \WebSms\Exception\HttpConnectionException
     * @throws \WebSms\Exception\ParameterValidationException
     * @throws \WebSms\Exception\UnknownResponseException
     */
    public function send($notifiable, Notification $notification): ?Response
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
            event(new WebSmsSending($notifiable, $notification, $this->channelName));

            $response = $client->send($message, $this->getSmsCount($message->getMessageContent()));

            event(new WebSmsSent($notifiable, $notification, $this->channelName, [
                'to' => $to,
                'message' => $message,
                'response' => $response
            ]));
        } catch (\Exception $e) {
            event(new WebSmsFailed($notifiable, $notification, $this->channelName, [
                'to' => $to,
                'message' => $message,
                'exception' => $e
            ]));
        }

        return $response;
    }

    public function getSmsCount(string $message): int
    {
        $length = strlen(trim($message));
        if ($length > 160) {
            return (int) ($length / 153) + 1;
        }

        return 1;
    }

    private function getConfig($key)
    {
        return config('websms')[$key];
    }
}
