<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuarantineEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quarantine_email', function (Blueprint $table) {
            $table->bigIncrements('pk_id');
            $table->string("sender_email", 100);
            $table->string("sender_name", 100)->nullable();
            $table->string("subject", 200);
            $table->string("message", 2000)->nullable();
            $table->timestamps();
            $table->bigInteger("fk_request_email_id")->unsigned();
            $table->foreign("fk_request_email_id")->references("pk_id")->on("request_email");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quarantine_email');
    }
}
