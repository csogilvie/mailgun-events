<?php

namespace MailgunEvents;

use Illuminate\Support\ServiceProvider;
use MailgunEvents\MailgunEvents\MailgunEventsAPI;

class MailgunEventsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/bindings.php' => config_path('mailgun_events/bindings.php')
        ], 'config');

        $this->publishes([
            __DIR__ . '/../config/settings.php' => config_path('mailgun_events/settings.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations')
        ], 'migrations');

        $this->commands([
            \MailgunEvents\Console\UpdateEvents::class,
            \MailgunEvents\Console\UpdateStats::class,
            \MailgunEvents\Console\UpdateTags::class,
            \MailgunEvents\Console\UpdateTagsStats::class,
            \MailgunEvents\Console\UpdateBounces::class,
            \MailgunEvents\Console\UpdateUnsubscribes::class,
            \MailgunEvents\Console\UpdateComplaints::class,
            \MailgunEvents\Console\UpdateDomains::class,
            \MailgunEvents\Console\UpdateCampaigns::class,
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bindings.php', 'mailgun_events.bindings');

        $this->mergeConfigFrom(__DIR__ . '/../config/settings.php', 'mailgun_events.settings');

        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');

        /**
         * Register main Mailgun Events service
         */
        $this->app->bind('mailgunevents', function () use ($config) {
            $clientAdapter = $this->app->make('mailgun.client');
            $apiEndPoint = $config->get('mailgun_events.settings.api.endpoint');

            $mgc = new MailgunEventsAPI(
                $config->get('mailgun_events.settings.api_key'),
                $clientAdapter,
                $apiEndPoint
            );
            $mgc->setApiVersion($config->get('mailgun_events.settings.api.version'));
            $mgc->setSslEnabled($config->get('mailgun_events.settings.api.ssl', true));
            $mgc->setDomainToCache($config->get('mailgun_events.settings.domain'));
            $mgc->setApiEndpoint($apiEndPoint);
            return $mgc;
        });

        $this->app->bind(MailgunEventsContract::class, 'mailgunevents');
    }
}
