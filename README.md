# Mailgun Events Laravel Package

Mailgun API does not offer a persistent storage of the statistics and reporting data. This package offers a layer to retrieve and store the data in a persistent layer (database) for its later access.

## Mailgun API

For more info about what can be retrieved from Mailgun please visit the [Mailgun API Reference](https://documentation.mailgun.com/api_reference.html)

## Install

Install the package via composer

>composer require adrianhl/mailgun-events

Register the ServiceProvider and (optionally) the Facade

```php
// config/app.php

'providers' => [
    ...
    MailgunEvents\MailgunEventsServiceProvider::class

];

...

'aliases' => [
	...
    'MailgunEvents' => MailgunEvents\Facades\MailgunEvents::class
],
```

Next, publish the config file with the following `artisan` command.<br />

```bash
php artisan vendor:publish --provider="MailgunEvents\MailgunEventsServiceProvider"
```

After publishing, configure the package in `config/mailgun_events/config.php`.

## License

The *Mailgun Events* package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

