<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQuarantineEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("quarantine_email", function (Blueprint $table) {
            $table->bigInteger("fk_amavis_server_id")->unsigned()->after("fk_request_email_id");
            $table->foreign("fk_amavis_server_id")->references("pk_id")->on("amavis_server");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("quarantine_email", function (Blueprint $table) {
            $table->dropForeign("quarantine_email_fk_amavis_server_id_foreign");
            $table->dropColumn("fk_amavis_server_id");
        });
    }
}
