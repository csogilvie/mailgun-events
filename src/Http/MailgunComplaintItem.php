<?php

namespace MailgunEvents\Http;

use Carbon\Carbon;

class MailgunComplaintItem
{

    protected $data;

    public function __construct($complaintItemProperties, $domain)
    {
        $this->data = json_decode(json_encode($complaintItemProperties), true);

        //Set the Complained At
        $this->data["complained_at"] = empty($this->data["created_at"]) ? null : Carbon::parse($this->data["created_at"]);

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