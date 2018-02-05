<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailgunCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailgun_campaigns', function (Blueprint $table) {
            $table->increments('id');

            $table->string('domain');
            $table->string('campaign_identifier');
            $table->integer('internal_identifier')->nullable();
            $table->string('name')->nullable();

            $table->integer('bounce_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('complained_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('dropped_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('submitted_count')->default(0);
            $table->integer('unsubscribed_count')->default(0);

            $table->timestamp('campaign_created_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->unique(["domain", "campaign_identifier"], "mailgun_campaigns_domain_campaign_index");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mailgun_campaigns');
    }
}
