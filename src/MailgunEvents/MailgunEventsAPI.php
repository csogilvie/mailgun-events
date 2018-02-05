<?php
namespace MailgunEvents\MailgunEvents;

use Carbon\Carbon;
use Mailgun\Mailgun as MailgunSDK;
use MailgunEvents\Http\Response;

class MailgunEventsAPI extends MailgunSDK
{
    /**
     * @var string Domain to get the data
     */
    protected $domainToCache;

    /**
     * @var string API Endpoint
     */
    protected $apiEndpoint;

    /**
     * @var boolean SSL Enabled
     */
    protected $sslEnabled;

    /**
     * @param string Api Version
     */
    protected $apiVersion;

    public function setDomainToCache($domainToCache)
    {
        $this->domainToCache = $domainToCache;
    }

    public function getDomainToCache()
    {
        return $this->domainToCache;
    }

    public function setApiEndpoint($apiEndPoint)
    {
        $this->apiEndpoint = $apiEndPoint;
    }

    public function setSslEnabled($sslEnabled)
    {
        $this->sslEnabled = $sslEnabled;
        return parent::setSslEnabled($sslEnabled);
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
        return parent::setApiVersion($apiVersion);
    }

    public function isDomainWorking()
    {
        try {
            $domainDetails = new Response($this->get("domains/" . $this->domainToCache));
            $isDomainWorking = !empty($domainDetails->domain);
        } catch (\Exception $ex) {
            throw new \Exception("Error while checking the domain.", 0, $ex);
        }

        return $isDomainWorking;
    }

    public function getStats($limit = 100, $skip = 0, $event = null, $startDate = null)
    {
        try {

            $parameters = [
                "limit"         => $limit,
                "skip"          => $skip,
                "event"         => $event,
            ];

            if (!empty($startDate)) {
                $parameters["start-date"] = $startDate;
            }

            $statsDetails = new Response(
                $this->get($this->domainToCache . "/stats/total", $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the stats.", 0, $ex);
        }

        return $statsDetails;
    }

    public function getTotalStats($event = ['accepted', 'delivered', 'failed', 'opened', 'clicked', 'unsubscribed', 'complained', 'stored'], $duration = "1m")
    {
        try {
            $parameters = [
                "event"     => $event,
                "duration"  => $duration,
            ];

            $statsDetails = new Response(
                $this->get($this->domainToCache . "/stats/total", $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the stats.", 0, $ex);
        }

        return $statsDetails;
    }

    public function getTagStats($tag, $event, Carbon $start = null, Carbon $end = null, $resolution = null, $duration = null)
    {
        $now = Carbon::now();
        try {

            //Default: 7 days from the current time.
            if (empty($start)) {
                $startT = clone $now;
                $start = $startT->subDays(7);
            }

            //Default: current time.
            if (empty($end)) {
                $end = clone $now;
            }

            //Default: day
            if (empty($resolution)) {
                $resolution = "day";
            }

            $parameters = [
                "event"     => $event,
                "start"     => $start->timestamp,
                "end"       => $end->timestamp,
                "resolution"    => $resolution
            ];

            if (!empty($duration)) {
                $parameters["resolution"] = $resolution;
            }

            $tagStatsDetails = new Response(
                $this->get($this->domainToCache . "/tags/" . $tag . "/stats", $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the tag '{$tag}' stats.", 0, $ex);
        }

        return $tagStatsDetails;
    }

    public function getTags($limit = 100, $page = null , $tag = null)
    {
        try {
            $parameters = [
                "limit"     => $limit
            ];

            if ($page) {
                $parameters["page"] = $page;
            }

            if ($tag) {
                $parameters["tag"] = $tag;
            }

            $statsDetails = new Response(
                $this->get($this->domainToCache . "/tags", $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the tags.", 0, $ex);
        }

        return $statsDetails;
    }

    public function getBounces($limit = 100,  $address = null, $page = null)
    {
        try {
            $parameters = [
                "limit" => $limit,
            ];

            if ($page) {
                $parameters["page"] = $page;
            }

            if ($address) {
                $parameters["address"] = $address;
            }

            $bouncesDetails = new Response(
                $this->get($this->domainToCache . "/bounces", $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the bounces.", 0, $ex);
        }

        return $bouncesDetails;
    }

    public function getUnsubscribes($limit = 100, $address = null, $page = null)
    {
        try {
            $parameters = [
                "limit" => $limit,
            ];

            if ($page) {
                $parameters["page"] = $page;
            }

            if ($address) {
                $parameters["address"] = $address;
            }

            $unsubscribesDetails = new Response(
                $this->get($this->domainToCache . "/unsubscribes", $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the unsubscribes.", 0, $ex);
        }

        return $unsubscribesDetails;
    }

    public function getComplaints($limit = 100, $address = null, $page = null)
    {
        try {
            $parameters = [
                "limit" => $limit,
            ];

            if ($page) {
                $parameters["page"] = $page;
            }

            if ($address) {
                $parameters["address"] = $address;
            }

            $complaintsDetails = new Response(
                $this->get($this->domainToCache . "/complaints", $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the complaints.", 0, $ex);
        }

        return $complaintsDetails;
    }

    public function getCampaigns($limit = 100, $skip = 0)
    {
        try {
            $parameters = [
                "limit" => $limit,
                "skip"  => $skip,
            ];

            $campaignsDetails = new Response(
                $this->get($this->domainToCache . "/campaigns", $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the campaigns.", 0, $ex);
        }

        return $campaignsDetails;
    }

    /**
     * Get the Events
     *
     * @param string|int    $begin      The beginning of the search time range. It can be specified as a string (see Date Format) or linux epoch seconds.
     * @param string|int    $end        The end of the search time range. It can be specified as a string (see Date Format) or linux epoch seconds.
     * @param string        $ascending  Defines the direction of the search time range if the range end time is not specified. Can be either yes or no.
     * @param int           $limit      Number of entries to return. (300 max)
     * @param string        $filter     The value of the parameter should be a valid Filter Expression: https://documentation.mailgun.com/api-events.html#filter-expression
     * @return string
     * @throws \Exception
     */
    public function getEventsPage($begin = null, $end = null, $ascending = "yes", $limit = null, $filter = null)
    {
        /**
         * Mailgun tracks all of the events that occur throughout the system.
         *
         * Below are listed the events that you can retrieve using this API.
         *
         * Event Type 	Description
         *
         * accepted 	Mailgun accepted the request to send/forward the email and the message has been placed in queue.
         * rejected 	Mailgun rejected the request to send/forward the email.
         * delivered 	Mailgun sent the email and it was accepted by the recipient email server.
         * failed 	    Mailgun could not deliver the email to the recipient email server.
         * opened 	    The email recipient opened the email and enabled image viewing. Open tracking must be enabled in the Mailgun control panel, and the CNAME record must be pointing to mailgun.org.
         * clicked 	    The email recipient clicked on a link in the email. Click tracking must be enabled in the Mailgun control panel, and the CNAME record must be pointing to mailgun.org.
         * unsubscribed The email recipient clicked on the unsubscribe link. Unsubscribe tracking must be enabled in the Mailgun control panel.
         * complained 	The email recipient clicked on the spam complaint button within their email client. Feedback loops enable the notification to be received by Mailgun.
         * stored 	    Mailgun has stored an incoming message
         */

        try {
            $parameters = [
                "ascending"     => $ascending
            ];
            if ($begin) {
                $parameters["begin"] = $begin;
            }
            if ($end) {
                $parameters["end"] = $end;
            }
            if ($limit) {
                $parameters["limit"] = $limit;
            }
            if ($filter) {
                $parameters["filter"] = $filter;
            }

            $eventsPage = new Response(
                $this->get($this->domainToCache . "/events", $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the events.", 0, $ex);
        }

        return $eventsPage;
    }

    public function getApiPageByURL($url, $parameters = [])
    {
        try {
            $urlToRemove = $this->getApiUrl();
            $urlToGet = str_replace($urlToRemove, "", $url);

            $eventsPage = new Response(
                $this->get($urlToGet, $parameters)
            );
        } catch (\Exception $ex) {
            throw new \Exception("Error while getting the events (pagination) - original url: {$urlToGet}", 0, $ex);
        }

        return $eventsPage;
    }

    // -- Helper functions

    /**
     * Gets the API Url
     * @return string
     */
    private function getApiUrl()
    {
        return ($this->sslEnabled ? 'https://' : 'http://') . $this->apiEndpoint.'/'.$this->apiVersion.'/';
    }
}