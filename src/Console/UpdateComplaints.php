<?php

namespace MailgunEvents\Console;

use Illuminate\Console\Command;
use MailgunEvents\Facades\MailgunEvents;
use MailgunEvents\Http\MailgunComplaintItem;
use MailgunEvents\MailgunEvents\MailgunComplaint;

class UpdateComplaints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun-events:complaints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Mailgun Complaints data to a persistent layer (database).';

    const LIMIT = 100;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $moreComplaints = true;
        $limit = self::LIMIT;
        $complaintsCreated = $complaintsUpdated = 0;
        $address = null;
        $page = null;

        do {
            $complaintsData = MailgunEvents::getComplaints($limit, $address, $page);

            if (count($complaintsData->items) == 0) {
                $this->info("There are no more Complaints to retrieve.");
                break;
            }

            //Store the Complaints
            $complaintsData->items->each(function($complaintData, $key) use (&$complaintsCreated, &$complaintsUpdated) {
                $complaintsItemData = new MailgunComplaintItem($complaintData, MailgunEvents::getDomainToCache());
                $itemData = $complaintsItemData->getData();

                $complaint = MailgunComplaint::updateOrCreate(
                    [
                        'domain' => $itemData['domain'],
                        'address' => $itemData['address']
                    ],
                    $itemData
                );

                if ($complaint->wasRecentlyCreated) {
                    $complaintsCreated++;
                    $this->info("Email '{$complaint->address}' complaint created.");
                } else {
                    $complaintsUpdated++;
                    $this->info("Email '{$complaint->address}' complaint updated.");
                }
            });

            //If the request returned less than the limit of complaints (then it's finished)
            if (count($complaintsData->items) < $limit) {
                $moreComplaints = false;
            }

            if (!empty($complaintsData->next_page)) {
                $urlArray = parse_url($complaintsData->next_page);
                parse_str($urlArray["query"], $parameters);
                $address = $parameters["address"];
                $page = $parameters["page"];
            }

        } while ($moreComplaints);

        $totalComplaints = $complaintsCreated + $complaintsUpdated;
        $this->info("A total of {$totalComplaints} Complaints have been retrieved ({$complaintsCreated} created, {$complaintsUpdated} updated).");
    }
}
