<?php

namespace MailgunEvents\Http;

use Carbon\Carbon;

class MailgunUnsubscribedItem
{

    protected $data;

    public function __construct($unsubscribedItemProperties, $domain)
    {
        $this->data = json_decode(json_encode($unsubscribedItemProperties), true);

        //Set the Unsubscribed At
        $this->data["unsubscribed_at"] = empty($this->data["created_at"]) ? null : Carbon::parse($this->data["created_at"]);

        //Set the Domain
        $this->data["domain"] = $domain;

        unset(
            $this->data["created_at"]
        );

    }

    public function getData()
    {
        return $this->data;
    }
}