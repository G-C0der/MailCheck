<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuarantineEmailAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quarantine_email_attachments', function (Blueprint $table) {
            $table->bigIncrements('pk_id');
            $table->string("name", 256);
            $table->string("type", 200);
            $table->float("bytes");
            $table->bigInteger("fk_quarantine_email_id")->unsigned();
            $table->foreign("fk_quarantine_email_id")->references("pk_id")->on("quarantine_email");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quarantine_email_attachments');
    }
}
