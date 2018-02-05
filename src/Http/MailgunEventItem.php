<?php

namespace MailgunEvents\Http;

use Carbon\Carbon;

class MailgunEventItem
{
    //Mailgun only stores one Campaign so the position in the Campaigns data is zero
    const CAMPAIGN_POSITION = 0;

    protected $data;

    public function __construct($eventItemProperties, $domain)
    {
        $this->data = json_decode(json_encode($eventItemProperties), true);

        //Set the Generated At
        $this->data["generated_at"] = empty($this->data["timestamp"]) ? null : Carbon::createFromTimestamp($this->data["timestamp"]);

        //Replace the ID provided and use it as event id
        $this->data["event_id"] = empty($this->data["id"]) ? null : $this->data["id"];

        //Set the Message ID (if available)
        $this->data["message_id"] = empty($this->data["message"]["headers"]["message-id"]) ? null : $this->data["message"]["headers"]["message-id"];

        //Set the Domain
        $this->data["domain"] = $domain;

        //Set the Campaign ID (if available)
        $this->data["campaign_id"] = empty($this->data["campaigns"][self::CAMPAIGN_POSITION]["id"]) ? null : $this->data["campaigns"][self::CAMPAIGN_POSITION]["id"];

        //Set the Flags as separate items (if available)
        $this->data["test_mode"] = empty($this->data["flags"]["is-test-mode"]) ? false : $this->data["flags"]["is-test-mode"];
        $this->data["system_test"] = empty($this->data["flags"]["is-system-test"]) ? false : $this->data["flags"]["is-system-test"];
        $this->data["routed"] = empty($this->data["flags"]["is-routed"]) ? false : $this->data["flags"]["is-routed"];
        $this->data["authenticated"] = empty($this->data["flags"]["is-authenticated"]) ? false : $this->data["flags"]["is-authenticated"];

        unset(
            $this->data["id"]
        );

    }

    public function getData()
    {
        return $this->data;
    }
}