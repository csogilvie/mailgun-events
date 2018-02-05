<?php

namespace MailgunEvents\Http;

use MailgunEvents\Http\Items;
use stdClass;

class Response
{

    /**
     * @var int
     */
    public $status;

    /**
     * @var array|null
     */
    public $data;

    /**
     * @var string
     */
    public $message;

    /**
     * The ID of the sent message, if it exists
     * @var string
     */
    public $id;

    /**
     * @param \stdClass $response
     */
    public function __construct(stdClass $response)
    {
        $this->status = $response->http_response_code;

        //Domain
        if (property_exists($response->http_response_body, 'domain')) {
            $this->domain = $response->http_response_body->domain;
        }
        if (property_exists($response->http_response_body, 'receiving_dns_records')) {
            $this->receiving_dns_records = $response->http_response_body->receiving_dns_records;
        }
        if (property_exists($response->http_response_body, 'sending_dns_records')) {
            $this->sending_dns_records = $response->http_response_body->sending_dns_records;
        }

        //Stats
        if (property_exists($response->http_response_body, 'stats')) {
            $this->stats = $response->http_response_body->stats;
        }
        if (property_exists($response->http_response_body, 'start')) {
            $this->start = $response->http_response_body->start;
        }
        if (property_exists($response->http_response_body, 'end')) {
            $this->end = $response->http_response_body->end;
        }
        if (property_exists($response->http_response_body, 'resolution')) {
            $this->resolution = $response->http_response_body->resolution;
        }

        //Tag
        if (property_exists($response->http_response_body, 'tag')) {
            $this->tag = $response->http_response_body->tag;
        }
        //Items (used in Events, Tags...) - convert to Items (Collection)
        if (property_exists($response->http_response_body, 'items')) {
            $this->items = new Items($response->http_response_body->items);
        }
        //Paging (used in Events)
        if (property_exists($response->http_response_body, 'paging')) {
            $this->paging = $response->http_response_body->paging;

            //First, Last, Next, and Previous Page (used in Events)
            if (property_exists($response->http_response_body->paging, 'next')) {
                $this->next_page = $response->http_response_body->paging->next;
            }
            if (property_exists($response->http_response_body->paging, 'previous')) {
                $this->previous_page = $response->http_response_body->paging->previous;
            }
            if (property_exists($response->http_response_body->paging, 'first')) {
                $this->first_page = $response->http_response_body->paging->first;
            }
            if (property_exists($response->http_response_body->paging, 'last')) {
                $this->last_page = $response->http_response_body->paging->last;
            }
        }

        $this->id = property_exists($response->http_response_body, 'id') ? $response->http_response_body->id : '';
    }

    /**
     * @return bool
     */
    public function success()
    {
        return $this->status === 200;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return !$this->success();
    }
}
