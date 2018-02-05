<?php

namespace MailgunEvents\Events;

use Event;
use Illuminate\Queue\SerializesModels;
use MailgunEvents\MailgunEvents\MailgunEvent;

class MailgunEventsStoredEvent extends Event
{
    use SerializesModels;

    /**
     * 
     * @var MailgunEvent
     */
    public $mailgunEvent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(MailgunEvent $mailgunEvent)
    {
        $this->mailgunEvent = $mailgunEvent;
    }
}
