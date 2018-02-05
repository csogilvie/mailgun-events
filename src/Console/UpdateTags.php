<?php

namespace MailgunEvents\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use MailgunEvents\Facades\MailgunEvents;
use MailgunEvents\Http\MailgunTagItem;
use MailgunEvents\MailgunEvents\MailgunTag;

class UpdateTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun-events:tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Mailgun Tags data to a persistent layer (database).';


    const LIMIT = 100;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tagsLimit = self::LIMIT;

        //Retrieve a result page following the parameters defined
        $tagsPage = MailgunEvents::getTags(
            $tagsLimit
        );

        $notFinished = true;
        $tagsCreated = $tagsUpdated = 0;

        do {
            if (empty($tagsPage->items)) {
                break;
            }

            $tagsPage->items->each(function ($tagItem, $key)  use (&$tagsUpdated, &$tagsCreated) {

                $tagItemData = new MailgunTagItem($tagItem, MailgunEvents::getDomainToCache());

                $itemData = $tagItemData->getData();

                $tag = MailgunTag::updateOrCreate(
                    [
                        'domain' => $itemData['domain'],
                        'tag' => $itemData['tag']
                    ],
                    $itemData
                );

                if ($tag->wasRecentlyCreated) {
                    $tagsCreated++;
                    $this->info("Tag '{$tag->tag}' created.");
                } else {
                    $tagsUpdated++;
                    $this->info("Tag '{$tag->tag}' updated.");
                }
            });
            $previousPageTags = $tagsPage->items->count();

            //If the page contains less than the limit requested then there are no more pages
            if ($previousPageTags < $tagsLimit) {
                break;
            }

            if (!empty($tagsPage->next_page)) {
                $urlArray = parse_url($tagsPage->next_page);
                parse_str($urlArray["query"], $parameters);

                $tagsPage = MailgunEvents::getTags(
                    $parameters["limit"],
                    $parameters["page"],
                    $parameters["tag"]
                );
            }
        } while ($notFinished);

        $totalTags = $tagsCreated + $tagsUpdated;

        $this->info("The tags have been retrieved for the domain. A total of {$totalTags} tags have been retrieved ({$tagsCreated} created, {$tagsUpdated} updated).");
    }
}
