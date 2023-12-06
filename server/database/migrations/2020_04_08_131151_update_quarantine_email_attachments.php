<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQuarantineEmailAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quarantine_email_attachments', function (Blueprint $table) {
            $table->float("bytes")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quarantine_email_attachments', function (Blueprint $table) {
            $table->float("bytes")->nullable(false)->change();
        });
    }
}
