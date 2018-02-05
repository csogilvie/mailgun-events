<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailgunUnsubscribesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailgun_unsubscribes', function (Blueprint $table) {
            $table->increments('id');

            $table->string('domain');
            $table->string('address');
            $table->text('tags')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->unique(["domain", "address"], "mailgun_unsubscribes_domain_address_index");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mailgun_unsubscribes');
    }
}
