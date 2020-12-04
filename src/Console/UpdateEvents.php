<?php

namespace MailgunEvents\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MailgunEvents\Facades\MailgunEvents;
use MailgunEvents\Http\MailgunEventItem;
use MailgunEvents\MailgunEvents\MailgunEvent;
use Symfony\Component\Console\Input\InputOption;
use Log;

class UpdateEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun-events:events {--yesterday= : Fetch Yesterday} {--daysBackwards= : The number of days to retrieve the events; default to 31. If the hours backwards option is provided then this option is ignored.} {--hoursBackwards= : The number of hours to retrieve the events; default to 0} {--filter= : A filter expresion. More info at https://documentation.mailgun.com/api-events.html#filter-expression} {--stopAfterNPages= : The process will stop after N pages without new events. Defaults to 5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Mailgun Events data to a persistent layer (database).';

    protected function getOptions()
    {
        return [
            ['daysBackwards', null, InputOption::VALUE_OPTIONAL, 'The number of days to retrieve the events; default to 31. If the hours backwards option is provided then this option is ignored.'],
            ['hoursBackwards', null, InputOption::VALUE_OPTIONAL, 'The number of hours to retrieve the events; default to 0'],
            ['filter', null, InputOption::VALUE_OPTIONAL, 'A filter expression. More info at https://documentation.mailgun.com/api-events.html#filter-expression'],
            ['stopAfterNPages', null, InputOption::VALUE_OPTIONAL, 'The process will stop after N pages without new events. It defaults to 5'],
            ['yesterday', null, InputOption::VALUE_OPTIONAL, 'Run for yesterday']
        ];
    }

    const DEFAULT_DAY_BACKWARDS = 31;
    const LIMIT = 300;
    const DEFAULT_INTERVAL = 60;
    const EMPTY_MULTIPLIER = 2;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        /**  The following code does not follow the event polling proposed because it will store (if new) all the events on each iteration.
         *    Therefore there is no need to check if a page is valid, any missing item will be stored in the next(s) call(s) to the command.
         */
        //Initial Parameters
        $end = Carbon::now();
        $stopDate = null;
        $interval = self::DEFAULT_INTERVAL;

        $begin = Carbon::now()->subMinutes($interval);

        //If the hours backwards parameter is defined then use it
        if ($hours = $this->option("hoursBackwards")) {
            $stopDate = Carbon::now()->subHours($hours);
        }

        //Use the days backwards option or use the default value
        if (empty($stopDate)) {
            //Mailgun keeps the Events Log for 30 days so it's pointless to retrieve older dates than a month ago.
            $daysBackwards = $this->option("daysBackwards") ?: self::DEFAULT_DAY_BACKWARDS;
            if ($daysBackwards > self::DEFAULT_DAY_BACKWARDS) {
                $daysBackwards = self::DEFAULT_DAY_BACKWARDS;
            }
            $stopDate = Carbon::now()->subDays($daysBackwards);
        }

        $stopAfterNPages = $this->option("stopAfterNPages") ?: 5;

        $filter = $this->option("filter") ?: null;

        if (!empty($filter)) {
            $this->error("The filter option has not been implemented yet. Please remove the filter option or request to implement this functionality.");
            return;
        }

        if ($this->option('yesterday')) {
            $begin = Carbon::yesterday()->setTime(0,0,0);
            $end = Carbon::yesterday()->setTime(23,59,59);
        }

        $ascending = "yes";

        try {
            //Retrieve a result page following the parameters defined
            $eventsPage = MailgunEvents::getEventsPage(
                $begin->toRfc2822String(),
                $end->toRfc2822String(),
                $ascending,
                self::LIMIT,
                $filter
            );
        } catch (\Exception $e) {
            $this->logExceptionErrors($e);
        }

        $pagesRetrieved = 1;
        $totalEvents = $eventsCreated = $eventsSkip = 0;
        $this->info("Obtained the first events page starting at " . $begin->toCookieString() . ".");

        $excludeTestModeEvents = config('mailgun_events.settings.exclude_test_mode_events');

        $continue = true;
        $currentPreviousPage = null;
        $currentNextPage = null;
        $pagesWithoutNewEvents = 0;
        do {
            $beginStart = $begin->toCookieString();
            $endStart = $end->toCookieString();
            $itemsOnThePage = empty($eventsPage->items) ? 0 : $eventsPage->items->count();

            $this->info("The current events page, begins at {$beginStart}, ends at {$endStart}, and contains {$itemsOnThePage} item(s).");

            //If there are no items in the response then jump to the next period
            if (empty($itemsOnThePage))
            {
                $end = clone $begin;
                $begin = $begin->subMinutes($interval);

                if ($stopDate->gte($end)) {
                    $this->info("Mailgun keeps the Events details for 30 days (for paid accounts, and 2 for free accounts) therefore this process is stopped before sending additional request using as begin timestamp {$beginStart}.");
                    break;
                }

                try {
                    $eventsPage = MailgunEvents::getEventsPage(
                        $begin->toRfc2822String(),
                        $end->toRfc2822String(),
                        $ascending,
                        self::LIMIT,
                        $filter
                    );
                } catch (\Exception $e) {
                    $this->logExceptionErrors($e);
                }

                //The interval is multiplied so the next check will be great (if again empty) to reduce the number of request
                $interval = $interval * self::EMPTY_MULTIPLIER;

                $pagesRetrieved++;
                continue;
            }

            $itemsCreatedOnPage = 0;
            //Process every event item in the events Page
            $eventsPage->items->each(function ($eventItem, $key) use (&$eventsCreated, &$eventsSkip, $excludeTestModeEvents, &$itemsCreatedOnPage) {
                $eventItemData = new MailgunEventItem($eventItem, MailgunEvents::getDomainToCache());
                $eventData = $eventItemData->getData();
                if ($excludeTestModeEvents && $eventData['test_mode']) {
                    $this->info("Mailgun (" . ucwords($eventData["event"]) . ") Event skip because it was done in test mode (please change the configuration in order to include test mode events).");
                    $eventsSkip++;
                    return false;
                }
                $mailgunEvent = MailgunEvent::create($eventData);
                if ($mailgunEvent->wasRecentlyCreated) {
                    $this->info("Mailgun (" . ucwords($mailgunEvent->event) . ") Event created #{$mailgunEvent->id}.");
                    $eventsCreated++;
                    $itemsCreatedOnPage++;
                } else {
                    $this->info("Mailgun (" . ucwords($mailgunEvent->event) . ") Event skip because it was already on the database #{$mailgunEvent->id}.");
                    $eventsSkip++;
                }
            });

            $totalEvents = $totalEvents + $eventsPage->items->count();

            if ($itemsCreatedOnPage == 0) {
                $pagesWithoutNewEvents++;
            } else {
                $pagesWithoutNewEvents = 0;
            }

            if ($pagesWithoutNewEvents >= $stopAfterNPages) {
                $continue = false;
            }

            if ($continue) {
                try {
                    if (!empty($eventsPage->next_page)) {
                        $interval = self::DEFAULT_INTERVAL;
                        $currentNextPage = $eventsPage->next_page;
                        $eventsPage = MailgunEvents::getApiPageByUrl($currentNextPage);
                        $pagesRetrieved++;
                        continue;
                    }
                } catch (\Exception $e) {
                    $this->logExceptionErrors($e);
                }
            }

        } while ($continue);


        $this->info("The Mailgun Events have been retrieved and saved into the database. A total of {$totalEvents} events have been retrieved ({$eventsCreated} created, {$eventsSkip} skip), by using {$pagesRetrieved} page request(s).");
    }


    /**
     * Receives all types of Exception type and logs the error message, along with which exception type has been thrown.
     *
     * WHY?: Guzzle returns chained Exceptions, so standard Exception capture will only capture the first exception
     * which is a standard 'Exception' type and holds no useful error response.
     * The other more useful Exception types are hidden after this one.
     *
     * So here, we loop through the chained Exceptions, using $e->getPrevious() to access
     * the hidden exceptions, until all have been logged, along with their more useful error messages.
     *
     * @param Exception $e
     */
    private function logExceptionErrors($e)
    {
        while (!empty($e)) {
            switch ($e) {
                case $e instanceof HttpException:
                    Log::warning('Mailgun - "UpdateEvents": Response code: ' . $e->getCode() . ' - Exception type: Http\Client\Exception\HttpException thrown: ' . $e->getMessage());
                    break;
                case $e instanceof ClientErrorResponseException:
                    Log::warning('Mailgun - "UpdateEvents": Response code: ' . $e->getCode() . ' - Exception type: Guzzle\Http\Exception\Guzzle ClientErrorResponseException thrown: ' . $e->getMessage());
                    break;
                case $e instanceof ClientException:
                    Log::warning('Mailgun - "UpdateEvents": Response code: ' . $e->getCode() . ' - Exception type: Guzzle\Http\Exception\Guzzle ClientException thrown: ' . $e->getMessage());
                    break;
                case $e instanceof Exception:
                    Log::warning('Mailgun - "UpdateEvents": Response code: ' . $e->getCode() . ' - Exception type "Exception" thrown: ' . $e->getMessage());
                    break;
                default:
                    Log::warning('Mailgun - "UpdateEvents": Response code: ' . $e->getCode() . ' - Exception Type: ' . get_class($e) . ' thrown: ' . $e->getMessage());
                    break;
            }
            $e = $e->getPrevious();
        }
    }

}
