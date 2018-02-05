<?php

namespace MailgunEvents\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class MailgunEvents.
 */
class MailgunEvents extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mailgunevents';
    }
}
