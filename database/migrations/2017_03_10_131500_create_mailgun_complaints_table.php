<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailgunComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailgun_complaints', function (Blueprint $table) {
            $table->increments('id');

            $table->string('domain');
            $table->string('address');
            $table->timestamp('complained_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->unique(["domain", "address"], "mailgun_complaints_domain_address_index");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mailgun_complaints');
    }
}
