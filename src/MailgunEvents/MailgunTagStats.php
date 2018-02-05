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

class MailgunTagStats extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mailgun_tags_stats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain',
        'tag',
        'event',
        'time',

        'tag_id',

        'accepted',
        'delivered',
        'failed',
        'opened',
        'clicked',
        'unsubscribed',
        'complained',
        'stored',

        'accepted_total',
        'delivered_total',
        'failed_total',
        'opened_total',
        'clicked_total',
        'unsubscribed_total',
        'complained_total',
        'stored_total',

        'accepted_incoming',
        'accepted_outgoing',

        'delivered_http',
        'delivered_smtp',

        'failed_temporary_espblock',
        'failed_permanent_suppress_bounce',
        'failed_permanent_suppress_unsubscribe',
        'failed_permanent_suppress_complaint',
        'failed_bounce',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'accepted'      => 'array',
        'delivered'     => 'array',
        'failed'        => 'array',
        'opened'        => 'array',
        'clicked'       => 'array',
        'unsubscribed'  => 'array',
        'complained'    => 'array',
        'stored'        => 'array'
    ];


}
