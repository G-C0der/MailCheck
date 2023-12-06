<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableQuarantineEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quarantine_email', function (Blueprint $table) {
            $table->string("subject", 2000)->nullable()->change();
            $table->string("message", 5000)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quarantine_email', function (Blueprint $table) {
            $table->string("subject", 200)->change();
            $table->string("message", 2000)->nullable()->change();
        });
    }
}
