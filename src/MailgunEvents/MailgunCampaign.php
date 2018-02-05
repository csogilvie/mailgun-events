<?php

namespace MailgunEvents\MailgunEvents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
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

class MailgunCampaign extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mailgun_campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain',
        'campaign_identifier',
        'internal_identifier',
        'name',

        'bounce_count',
        'clicked_count',
        'complained_count',
        'delivered_count',
        'dropped_count',
        'opened_count',
        'submitted_count',
        'unsubscribed_count',

        'campaign_created_at',
    ];
}
