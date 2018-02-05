<?php

namespace MailgunEvents\Http;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MailgunTagItem
{

    protected $data;

    const TIME_SEEN_FORMAT = "Y-m-d\TH:i:sT";
    const MILITIME_SEEN_FORMAT = "Y-m-d\TH:i:s.uT";

    protected function generateTimeValue($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            try {
                $timeValue = Carbon::createFromFormat(self::TIME_SEEN_FORMAT, $value);
            } catch (\Exception $ex) {
                $timeValue =  Carbon::createFromFormat(self::MILITIME_SEEN_FORMAT, $value);
            }
        } catch (\Exception $ex) {
            Log::warning("A wrong format time was returned in a Tag Item. Please the value ({$value}) and the available formats");
            return null;
        }

        return $timeValue;
    }
    public function __construct($tagItemProperties, $domain)
    {
        $this->data = json_decode(json_encode($tagItemProperties), true);

        //Set the First and Last Seen At
        $this->data["first_seen_at"] = isset($this->data["first-seen"]) ? $this->generateTimeValue($this->data['first-seen']) : null;
        $this->data["last_seen_at"] = isset($this->data["last-seen"]) ? $this->generateTimeValue($this->data['last-seen']) : null;

        //Set the Domain
        $this->data["domain"] = $domain;

        unset(
            $this->data["first-seen"],
            $this->data["last-seen"]
        );
    }

    public function getData()
    {
        return $this->data;
    }
}