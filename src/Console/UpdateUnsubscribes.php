<?php

namespace MailgunEvents\Console;

use Illuminate\Console\Command;
use MailgunEvents\Facades\MailgunEvents;
use MailgunEvents\Http\MailgunUnsubscribedItem;
use MailgunEvents\MailgunEvents\MailgunUnsubscribe;

class UpdateUnsubscribes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun-events:unsubscribes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Mailgun Unsubscribes data to a persistent layer (database).';

    const LIMIT = 100;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $moreUnsubscribes = true;
        $unsubscribesCreated = $unsubscribesUpdated = 0;
        $page = null;
        $address = null;

        $limit = self::LIMIT;
        do {
            $unsubscribesData = MailgunEvents::getUnsubscribes($limit, $address, $page);

            if (count($unsubscribesData->items) == 0) {
                $this->info("There are no more Unsubscribes to retrieve.");
                break;
            }

            //Store the Unsubscribes
            $unsubscribesData->items->each(function($unsubscribeData, $key) use (&$unsubscribesCreated, &$unsubscribesUpdated) {

                $unsubscribedItemData = new MailgunUnsubscribedItem($unsubscribeData, MailgunEvents::getDomainToCache());

                $itemData = $unsubscribedItemData->getData();

                $unsubscribed = MailgunUnsubscribe::updateOrCreate(
                    [
                        'domain' => $itemData['domain'],
                        'address' => $itemData['address']
                    ],
                    $itemData
                );

                if ($unsubscribed->wasRecentlyCreated) {
                    $unsubscribesCreated++;
                    $this->info("Email '{$unsubscribed->address}' unsubscribe created.");
                } else {
                    $unsubscribesUpdated++;
                    $this->info("Email '{$unsubscribed->address}' unsubscribe updated.");
                }
            });

            //If the request returned less than the limit of bounces (then it's finished)
            if (count($unsubscribesData->items) < $limit) {
                $moreUnsubscribes = false;
                break;
            }

            if (!empty($unsubscribesData->next_page)) {
                $urlArray = parse_url($unsubscribesData->next_page);
                parse_str($urlArray["query"], $parameters);
                $address = $parameters["address"];
                $page = $parameters["page"];
            }

        } while ($moreUnsubscribes);

        $totalUnsubscribes = $unsubscribesCreated + $unsubscribesUpdated;
        $this->info("A total of {$totalUnsubscribes} Unsubscribes have been retrieved ({$unsubscribesCreated} created, {$unsubscribesUpdated} updated).");
    }
}
