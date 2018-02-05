<?php

namespace MailgunEvents\MailgunEvents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use MailgunEvents\Events\MailgunEventsAcceptedEvent;
use MailgunEvents\Events\MailgunEventsClickedEvent;
use MailgunEvents\Events\MailgunEventsComplainedEvent;
use MailgunEvents\Events\MailgunEventsDeliveredEvent;
use MailgunEvents\Events\MailgunEventsFailedEvent;
use MailgunEvents\Events\MailgunEventsOpenedEvent;
use MailgunEvents\Events\MailgunEventsRejectedEvent;
use MailgunEvents\Events\MailgunEventsStoredEvent;
use MailgunEvents\Events\MailgunEventsUnsubscribedEvent;

class MailgunEvent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mailgun_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain',
        'event_id',
        'event_key',
        'event',
        'generated_at',
        'recipient',
        'message_id',
        'campaigns',
        'campaign_id',
        'tags',
        'envelope',
        'user_variables',
        'flags',
        'test_mode',
        'system_test',
        'routed',
        'authenticated',
        'routes',
        'message',
        'method',
        'delivery_status',
        'severity',
        'reason',
        'delivery_status',
        'geolocation_country',
        'geolocation_region',
        'geolocation_city',
        'ip',
        'client_info',
        'url',
        'storage'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'storage'           => 'array',
        'envelope'          => 'array',
        'flags'             => 'array',
        'message'           => 'array',
        'campaigns'         => 'array',
        'tags'              => 'array',
        'user_variables'    => 'array'
    ];

    public static function create(array $attributes = [])
    {
        $updatedAttributes = $attributes;

        //Generate a unique event key (because the event id is only guaranteed to be unique within a day)
        $updatedAttributes['event_key'] = $attributes["event_id"] . "-" . $attributes["timestamp"];

        //Try to get an exiting event based on the event key
        $mailgunEvent = parent::firstOrNew([
            'domain' => $updatedAttributes['domain'],
            'event_key' => $updatedAttributes['event_key']
        ]);

        //If not found then fill the model and save it
        if (empty($mailgunEvent->created_at))
        {
            $mailgunEvent->fill($updatedAttributes);

            $mailgunEvent->save();

            self::fireEvent($mailgunEvent);
        }

        return $mailgunEvent;
    }

    /**
     * Fire a MailgunEvents event based on the Mailgun event
     *
     * @param $mailgunEvent
     */
    protected static function fireEvent($mailgunEvent)
    {
        switch ($mailgunEvent->event) {
            case 'accepted':
                Event::fire(new MailgunEventsAcceptedEvent($mailgunEvent));
                break;
            case 'rejected':
                Event::fire(new MailgunEventsRejectedEvent($mailgunEvent));
                break;
            case 'delivered':
                Event::fire(new MailgunEventsDeliveredEvent($mailgunEvent));
                break;
            case 'failed':
                Event::fire(new MailgunEventsFailedEvent($mailgunEvent));
                break;
            case 'opened':
                Event::fire(new MailgunEventsOpenedEvent($mailgunEvent));
                break;
            case 'clicked':
                Event::fire(new MailgunEventsClickedEvent($mailgunEvent));
                break;
            case 'unsubscribed':
                Event::fire(new MailgunEventsUnsubscribedEvent($mailgunEvent));
                break;
            case 'complained':
                Event::fire(new MailgunEventsComplainedEvent($mailgunEvent));
                break;
            case 'stored':
                Event::fire(new MailgunEventsStoredEvent($mailgunEvent));
                break;
            default:
                Log::warning("An unknown Mailgun event type ({$mailgunEvent->event}) was created. Please check why this has happened.");
                break;
        }
    }



}
