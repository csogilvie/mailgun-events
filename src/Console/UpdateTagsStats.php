<?php

namespace MailgunEvents\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MailgunEvents\Facades\MailgunEvents;
use MailgunEvents\Http\MailgunTagStatsItem;
use MailgunEvents\MailgunEvents\MailgunTag;
use MailgunEvents\MailgunEvents\MailgunTagStats;
use Symfony\Component\Console\Input\InputOption;

class UpdateTagsStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun-events:tags-stats {--event= : The event to retrieve the Tag Stats.} {--hoursBackwards= : The number of hours to retrieve the tag stats; default to 24.} {--duration= : Period of time with resolution encoded. If provided, overwrites the start date and resolution. See https://documentation.mailgun.com/api-tags.html#duration for more info} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Mailgun Tags Stats data to a persistent layer (database).';


    const STATS_RESOLUTION = "hour";
    const DETAULT_HOURS = 24;
    const MAX_HOURS = 672;

    protected function getOptions()
    {
        return [
            ['event', null, InputOption::VALUE_REQUIRED, 'The event to retrieve the Tag Stats.'],
            ['hoursBackwards', null, InputOption::VALUE_OPTIONAL, 'The number of hours to retrieve the tag stats; default to 24.'],
            ['duration', null, InputOption::VALUE_OPTIONAL, 'Period of time with resolution encoded. If provided, overwrites the start date and resolution. See https://documentation.mailgun.com/api-tags.html#duration for more info.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hours = $this->option("hoursBackwards") ?: $hours = self::DETAULT_HOURS;

        if ($hours > self::MAX_HOURS) {
            $this->error("The maximum hours allowed to retrieved Tag Stats is " . self::MAX_HOURS . " please set the hoursBackwards option accordingly");
            return;
        }

        $event = $this->option('event');
        if (empty($event)) {
            $this->error("The Event option is required to retrieve the Tags Stats. Please provide one of the available Mailgun event types: https://documentation.mailgun.com/api-tags.html#event-types");
            return;
        }
        $duration = $this->option('duration');
        $start = Carbon::now()->subHours($hours)->minute(0)->second(0);
        $end = Carbon::now();

        $tagStatsCreated = $tagStatsUpdated = 0;

        //For each tag get the stats in a hourly rate (lower detail provided by Mailgun)
        MailgunTag::all(["id", "tag"])->each(function($tag, $key) use ($event, $start, $end, $hours, $duration, &$tagStatsCreated, &$tagStatsUpdated) {

            //Get the Event Tag Stats
            $eventTagStats = MailgunEvents::getTagStats($tag->tag, $event, $start, $end, self::STATS_RESOLUTION, $duration);

            if (empty($eventTagStats->stats)) {
                return false;
            }
            foreach ($eventTagStats->stats as $tagStats) {

                $tagStatsItemData = new MailgunTagStatsItem(
                    $tagStats,
                    $tag->tag,
                    $event,
                    MailgunEvents::getDomainToCache(),
                    $tag->id
                );

                $tagsStatsData = $tagStatsItemData->getData();

                $tagStats = MailgunTagStats::updateOrCreate(
                    [
                        'domain' => $tagsStatsData['domain'],
                        'tag'   => $tag->tag,
                        'event' => $tagsStatsData['event'],
                        'time' => $tagsStatsData['time'],
                    ],
                    $tagsStatsData
                );

                if ($tagStats->wasRecentlyCreated) {
                    $tagStatsCreated++;
                    $this->info("Event '{$event}' Stats created for Tag '{$tag->tag}' at {$tagStats->time}.");
                } else {
                    $tagStatsUpdated++;
                    $this->info("Event '{$event}' Stats updated for Tag '{$tag->tag}' at {$tagStats->time}.");
                }
            }
        });

        $totalTagStats = $tagStatsCreated + $tagStatsUpdated;

        $this->info("The stats for the tags have been retrieved for the domain. A total of {$totalTagStats} tag stats have been retrieved ({$tagStatsCreated} created, {$tagStatsUpdated} updated).");
    }
}
