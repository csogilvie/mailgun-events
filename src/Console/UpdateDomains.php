<?php

namespace MailgunEvents\Console;

use Illuminate\Console\Command;
use MailgunEvents\Facades\MailgunEvents;

class UpdateDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun-events:domains';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Mailgun Domains data to a persistent layer (database).';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $isWorking = MailgunEvents::isDomainWorking();
    }
}
