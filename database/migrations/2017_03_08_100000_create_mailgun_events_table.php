<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailgunEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailgun_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain');
            $table->string('event_id');
            $table->enum('event', [
                'accepted',
                'rejected',
                'delivered',
                'failed',
                'opened',
                'clicked',
                'unsubscribed',
                'complained',
                'stored'
            ]);
            $table->string("event_key");
            $table->timestamp("generated_at");
            $table->string("recipient")->nullable();
            $table->string("message_id")->nullable();
            $table->text("campaigns")->nullable();
            $table->string("campaign_id")->nullable();
            $table->text("tags")->nullable();
            $table->text("envelope")->nullable();
            $table->text("user_variables")->nullable();
            $table->text("flags")->nullable();
            $table->boolean("authenticated")->default(false);
            $table->boolean("test_mode")->default(false);
            $table->boolean("system_test")->default(false);
            $table->boolean("routed")->default(false);
            $table->text("routes")->nullable();
            $table->text("message")->nullable();
            $table->string("method")->nullable();
            $table->text("delivery_status")->nullable();
            $table->string("severity")->nullable();
            $table->string("reason")->nullable();
            $table->string("geolocation_country")->nullable();
            $table->string("geolocation_region")->nullable();
            $table->string("geolocation_city")->nullable();
            $table->string("ip")->nullable();
            $table->text("client_info")->nullable();
            $table->text("url")->nullable();
            $table->text("storage")->nullable();

            $table->timestamps();

            $table->unique(["domain", "event_key"], "mailgun_events_domain_event_key_index");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mailgun_events');
    }
}
