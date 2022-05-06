<?php

namespace Hebinet\Notifications\Channels;

use Hebinet\Notifications\Events\WebSmsFailed;
use Hebinet\Notifications\Events\WebSmsSending;
use Hebinet\Notifications\Events\WebSmsSent;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use WebSms\Client;
use WebSms\Response;
use WebSms\TextMessage;

class WebSmsChannel
{
    protected string $channelName = 'websms';

    public function __construct(protected Client $client)
    {
    }

    public function send($notifiable, Notification $notification): ?Response
    {
        $to = $notifiable->phone_number ?? null;
        $routeTo = $notifiable->routeNotificationFor($this->channelName, $notification);
        if ($routeTo) {
            $to = $routeTo;
        }

        $to = Arr::wrap($to);

        /** @var \WebSms\Message|\WebSms\BinaryMessage|string|false $message */
        $message = $notification->toWebsms($notifiable);
        // If false is returned from notification, sending will be aborted!
        if (! $message) {
            return null;
        }

        if (is_string($message)) {
            $message = new TextMessage($to, trim($message));
        }

        $client = $this->client;
        $client->test($this->getConfig('test') ?? false);
        if ($this->getConfig('verbose') ?? false) {
            $client->setVerbose(true);
        }

        $response = null;
        try {
            Event::dispatch(new WebSmsSending($notifiable, $notification, $this->channelName));

            $response = $client->send($message, $this->getSmsCount($message->getMessageContent()));

            Event::dispatch(new WebSmsSent($notifiable, $notification, $this->channelName, [
                'to' => $to,
                'message' => $message,
                'response' => $response,
            ]));
        } catch (\Exception $e) {
            Event::dispatch(new WebSmsFailed($notifiable, $notification, $this->channelName, [
                'to' => $to,
                'message' => $message,
                'exception' => $e,
            ]));
        }

        return $response;
    }

    public function getSmsCount(string $message): int
    {
        $length = strlen(trim($message));
        if ($length > 160) {
            return (int)($length / 153) + 1;
        }

        return 1;
    }

    protected function getConfig($key): mixed
    {
        return Config::get("websms.{$key}");
    }
}
