<?php

namespace MailgunEvents\Http;

use Carbon\Carbon;

class MailgunCampaignItem
{
    protected $data;

    public function __construct($eventItemProperties, $domain)
    {
        $this->data = json_decode(json_encode($eventItemProperties), true);

        //Replace the ID provided and use it as campaign_identifier
        $this->data["campaign_identifier"] = empty($this->data["id"]) ? null : $this->data["id"];

        //Set the Campaign Created At
        $this->data["campaign_created_at"] = empty($this->data["created_at"]) ? null : Carbon::parse($this->data["created_at"]);

        unset(
            $this->data["id"],
            $this->data["created_at"]
        );

    }

    public function getData()
    {
        return $this->data;
    }
}