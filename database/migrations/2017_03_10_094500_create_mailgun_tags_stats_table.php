<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailgunTagsStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailgun_tags_stats', function (Blueprint $table) {
            $table->increments('id');

            $table->string('domain');
            $table->string('tag');
            $table->integer('tag_id')->nullable();
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
            $table->timestamp('time');

            $table->text('accepted')->nullable();
            $table->integer('accepted_total')->default(0);
            $table->integer('accepted_incoming')->default(0);
            $table->integer('accepted_outgoing')->default(0);

            $table->text('delivered')->nullable();
            $table->integer('delivered_total')->default(0);
            $table->integer('delivered_smtp')->default(0);
            $table->integer('delivered_http')->default(0);

            $table->text('failed')->nullable();
            $table->integer('failed_total')->default(0);
            $table->integer('failed_temporary_espblock')->default(0);
            $table->integer('failed_permanent_suppress_bounce')->default(0);
            $table->integer('failed_permanent_suppress_unsubscribe')->default(0);
            $table->integer('failed_permanent_suppress_complaint')->default(0);
            $table->integer('failed_bounce')->default(0);

            $table->text('opened')->nullable();
            $table->integer('opened_total')->default(0);

            $table->text('clicked')->nullable();
            $table->integer('clicked_total')->default(0);

            $table->text('unsubscribed')->nullable();
            $table->integer('unsubscribed_total')->default(0);

            $table->text('complained')->nullable();
            $table->integer('complained_total')->default(0);

            $table->text('stored')->nullable();
            $table->integer('stored_total')->default(0);

            $table->timestamps();

            $table->unique(["domain", "tag", "event", "time"], "mailgun_tags_stats_domain_tag_event_time_index");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mailgun_tags_stats');
    }
}
