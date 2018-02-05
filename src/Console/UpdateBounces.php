<?php

namespace MailgunEvents\Console;

use Illuminate\Console\Command;
use MailgunEvents\Facades\MailgunEvents;
use MailgunEvents\Http\MailgunBouncedItem;
use MailgunEvents\MailgunEvents\MailgunBounce;

class UpdateBounces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun-events:bounces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Mailgun Bounces data to a persistent layer (database).';

    const LIMIT = 100;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $moreBounces = true;
        $limit = self::LIMIT;
        $bouncesCreated = $bouncesUpdated = 0;
        $address = null;
        $page = null;

        do {
            $bouncesData = MailgunEvents::getBounces($limit, $address, $page);

            if (count($bouncesData->items) == 0) {
                $this->info("There are no more Bounces to retrieve");
                break;
            }

            //Store the Bounces
            $bouncesData->items->each(function($bounceData, $key) use (&$bouncesCreated, &$bouncesUpdated) {

                $bounceItemData = new MailgunBouncedItem($bounceData, MailgunEvents::getDomainToCache());

                $itemData = $bounceItemData->getData();

                $bounced = MailgunBounce::updateOrCreate(
                    [
                        'domain' => $itemData['domain'],
                        'address' => $itemData['address']
                    ],
                    $itemData
                );

                if ($bounced->wasRecentlyCreated) {
                    $bouncesCreated++;
                    $this->info("Email '{$bounced->address}' bounce created.");
                } else {
                    $bouncesUpdated++;
                    $this->info("Email '{$bounced->address}' bounce updated.");
                }
            });

            //If the request returned less than the limit of bounces (then it's finished)
            if (count($bouncesData->items) < $limit) {
                $moreBounces = false;
            }

            if (!empty($bouncesData->next_page)) {
                $urlArray = parse_url($bouncesData->next_page);
                parse_str($urlArray["query"], $parameters);
                $address = $parameters["address"];
                $page = $parameters["page"];
            }

        } while ($moreBounces);

        $totalBounces = $bouncesCreated + $bouncesUpdated;
        $this->info("A total of {$totalBounces} Unsubscribes have been retrieved ({$bouncesCreated} created, {$bouncesUpdated} updated).");
    }
}
