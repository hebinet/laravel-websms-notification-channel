# Laravel WebSMS Notifications

Provides a Notification Channel for WebSms

## Installation

You can install it using Composer:

```bash
composer require hebinet/laravel-websms-notification-channel
```

Then publish the config file of the package.

```bash
php artisan vendor:publish --provider="Hebinet\Notifications\WebSmsChannelServiceProvider" --tag=config
```

## Usage

If a notification supports being sent as an SMS, you should define a `toWebsms` method on the notification class.
This method will receive a `$notifiable` entity and should return a `string` with the content of the SMS:

```php
/**
 * Get the WebSMS / SMS representation of the notification.
 *
 * @param  mixed  $notifiable
 * @return string
 */
public function toWebsms($notifiable)
{
    return 'Your SMS message content';
}
```

### Routing SMS Notifications
To route Nexmo notifications to the proper phone number, 
define a `routeNotificationForWebsms` method on your notifiable entity:

```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Route notifications for the WebSms channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForWebsms($notification)
    {
        return $this->phone_number;
    }
}
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email office@hebinet.at instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.