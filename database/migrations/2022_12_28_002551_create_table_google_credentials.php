<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_credentials', function (Blueprint $table) {
            $table->id();
            $table->text('google_calendar_id')->nullable();
            $table->text('google_client_id')->nullable();
            $table->text('google_client_secret')->nullable();
            $table->text('google_redirect_uri')->nullable();
            $table->text('google_webhook_uri')->nullable();
            $table->integer('user_id')->unsigned();;
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Schema::table('google_credentials', function ($table) {
        //     $table->foreign('user_id')->references('id')->on('users');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_credentials');
    }
};
