<?php

namespace MailgunEvents\Http;

use Carbon\Carbon;

class MailgunBouncedItem
{

    protected $data;

    public function __construct($bouncedItemProperties, $domain)
    {
        $this->data = json_decode(json_encode($bouncedItemProperties), true);

        //Set the Bounced At
        $this->data["bounced_at"] = empty($this->data["created_at"]) ? null : Carbon::parse($this->data["created_at"]);

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