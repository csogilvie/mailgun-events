<?php

namespace MailgunEvents\Console;

use Illuminate\Console\Command;
use MailgunEvents\Facades\MailgunEvents;

class UpdateStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun-events:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Mailgun Stats data to a persistent layer (database).';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stats = MailgunEvents::getTotalStats();
        dd($stats);
    }
}
