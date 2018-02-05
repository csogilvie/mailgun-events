<?php

namespace MailgunEvents\Http;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MailgunTagStatsItem
{

    protected $data;


    public function __construct($tagStatsItemProperties, $tag, $event, $domain, $tagId = null)
    {
        $this->data = json_decode(json_encode($tagStatsItemProperties), true);

        //Set the Domain
        $this->data["domain"] = $domain;

        //Set the Tag
        $this->data["tag"] = $tag;

        //Set the Event
        $this->data["event"] = $event;

        //Set the Time (as Carbon)
        $this->data["time"] = Carbon::parse($this->data["time"]);

        if (!empty($this->data["accepted"])) {
            $this->data["accepted_total"]    = empty($this->data["accepted"]["total"])    ? 0 : $this->data["accepted"]["total"];
            $this->data["accepted_incoming"] = empty($this->data["accepted"]["incoming"]) ? 0 : $this->data["accepted"]["incoming"];
            $this->data["accepted_outgoing"] = empty($this->data["accepted"]["outgoing"]) ? 0 : $this->data["accepted"]["outgoing"];
        }

        if (!empty($this->data["opened"])) {
            $this->data["opened_total"] = empty($this->data["opened"]["total"]) ? 0 : $this->data["opened"]["total"];
        }

        if (!empty($this->data["delivered"])) {
            $this->data["delivered_total"] = empty($this->data["delivered"]["total"]) ? 0 : $this->data["delivered"]["total"];
            $this->data["delivered_smtp"]  = empty($this->data["delivered"]["smtp"])  ? 0 : $this->data["delivered"]["smtp"];
            $this->data["delivered_http"]  = empty($this->data["delivered"]["http"])  ? 0 : $this->data["delivered"]["http"];
        }

        if (!empty($this->data["failed"])) {
            $this->data["failed_total"] = empty($this->data["failed"]["total"]) ? 0 : $this->data["failed"]["total"];
            $this->data["failed_temporary_espblock"] = empty($this->data["failed"]["temporary"]["espblock"]) ? 0 : $this->data["failed"]["temporary"]["espblock"];
            $this->data["failed_permanent_suppress_bounce"] = empty($this->data["failed"]["permanent"]["suppress-bounce"]) ? 0 : $this->data["failed"]["permanent"]["suppress-bounce"];
            $this->data["failed_permanent_suppress_unsubscribe"] = empty($this->data["failed"]["permanent"]["suppress-unsubscribe"]) ? 0 : $this->data["failed"]["permanent"]["suppress-unsubscribe"];
            $this->data["failed_permanent_suppress_complaint"] = empty($this->data["failed"]["permanent"]["suppress-complaint"]) ? 0 : $this->data["failed"]["permanent"]["suppress-complaint"];
            $this->data["failed_bounce"] = empty($this->data["failed"]["bounce"]) ? 0 : $this->data["failed"]["bounce"];
        }

        if (!empty($this->data["clicked"])) {
            $this->data["clicked_total"] = empty($this->data["clicked"]["total"]) ? 0 : $this->data["clicked"]["total"];
        }

        if (!empty($this->data["unsubscribed"])) {
            $this->data["unsubscribed_total"] = empty($this->data["unsubscribed"]["total"]) ? 0 : $this->data["unsubscribed"]["total"];
        }

        if (!empty($this->data["complained"])) {
            $this->data["complained_total"] = empty($this->data["complained"]["total"]) ? 0 : $this->data["complained"]["total"];
        }

        if (!empty($this->data["stored"])) {
            $this->data["stored_total"] = empty($this->data["stored"]["total"]) ? 0 : $this->data["stored"]["total"];
        }

        $this->data["tag_id"] = $tagId;
    }

    public function getData()
    {
        return $this->data;
    }
}