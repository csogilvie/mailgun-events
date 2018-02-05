<?php

namespace MailgunEvents\Console;

use Illuminate\Console\Command;
use MailgunEvents\Facades\MailgunEvents;
use MailgunEvents\Http\MailgunCampaignItem;
use MailgunEvents\MailgunEvents\MailgunCampaign;

class UpdateCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun-events:campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Mailgun Campaigns data to a persistent layer (database).';

    const LIMIT = 10;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $skip = 0;
        $limit = self::LIMIT;
        $requests = 0;
        $campaignsCreated = $campaignsUpdated = 0;
        $moreCampaigns = true;

        do {
            $requests++;
            $this->info("Request #{$requests} - Obtaining the following {$limit} Campaigns skipping {$skip}...");
            $campaigns = MailgunEvents::getCampaigns($limit, $skip);

            //Break the loop if there are no more items
            if (count($campaigns->items) == 0) {
                $this->info("No campaigns were included in the request.");
                break;
            }

            $this->info("Request #{$requests} - Processing the campaigns...");
            $campaigns->items->each(function ($campaignDetails, $key) use (&$campaignsCreated, &$campaignsUpdated) {

                $campaignItemData = new MailgunCampaignItem(
                    $campaignDetails,
                    MailgunEvents::getDomainToCache()
                );

                $campaignData = $campaignItemData->getData();

                $campaign = MailgunCampaign::updateOrCreate(
                    [
                        'domain'              => MailgunEvents::getDomainToCache(),
                        'campaign_identifier' => $campaignData['campaign_identifier']
                    ],
                    $campaignData
                );

                if ($campaign->wasRecentlyCreated) {
                    $campaignsCreated++;
                    $this->info("Campaign '{$campaign->campaign_identifier}' created.");
                } else {
                    $campaignsUpdated++;
                    $this->info("Campaign '{$campaign->campaign_identifier}' updated.");
                }
            });

            //Update the skip for the next request and set to continue
            $skip = $skip + $limit;

            //If the request returned less than the limit of campaigns (then it's finished)
            if (count($campaigns->items) < $limit) {
                $moreCampaigns = false;
            }

        } while ($moreCampaigns);

        $totalCampaigns = $campaignsCreated + $campaignsUpdated;

        $this->info("The campaigns have been retrieved for the domain (in {$requests} requests). A total of {$totalCampaigns} campaigns have been retrieved ({$campaignsCreated} created, {$campaignsUpdated} updated).");
    }
}
