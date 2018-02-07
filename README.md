# Mailgun Events Laravel Package

Mailgun API does not offer a persistent storage of the statistics and reporting data. This package offers a layer to retrieve and store the data in a persistent layer (database) for its later access.

## Mailgun API

For more info about what can be retrieved from Mailgun please visit the [Mailgun API Reference](https://documentation.mailgun.com/api_reference.html)

## Installation

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

### HTTP Client Dependency

To remove the dependency for a specific HTTP client library (e.g. Guzzle) the [mailgun-php](https://github.com/mailgun/mailgun-php) library has a dependency on the virtual package
[php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation) which allows you to install **any** supported client adapter, it does not care which one. Please refer to the [documentation](http://docs.php-http.org/) for more information.

This gives you the freedom to use any (supported) client for communicating with the Mailgun API.
To register your driver you must register it in the Service Container with the `mailgun.client` key.

The registration **must** occur before the `MailgunServiceProvider` is being registered.

#### Guzzle 6 example implementation

Install the dependencies:

```bash
$ composer require php-http/guzzle6-adapter
```

Add the following to your `AppServiceProvider` `register()` method.

```php
$this->app->bind('mailgun.client', function() {
	return \Http\Adapter\Guzzle6\Client::createWithConfig([
		// your Guzzle6 configuration
	]);
});
```
---

<br /><br />

## License

The *Mailgun Events* package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

