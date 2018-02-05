<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailgunBouncesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailgun_bounces', function (Blueprint $table) {
            $table->increments('id');

            $table->string('domain');
            $table->string('address');
            $table->string('code')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('bounced_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->unique(["domain", "address"], "mailgun_bounces_domain_address_index");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mailgun_bounces');
    }
}
