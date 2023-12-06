<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_email', function (Blueprint $table) {
            $table->bigIncrements('pk_id');
            $table->string("amavis_identifier", 21)->unique();
            $table->string("sender_email", 100);
            $table->string("sender_name", 100)->nullable();
            $table->string("message", 500)->nullable();
            $table->timestamp("timestamp");
            $table->tinyInteger("status")->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_email');
    }
}
