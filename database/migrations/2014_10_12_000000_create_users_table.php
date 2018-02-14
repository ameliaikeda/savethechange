<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');

            // generic user information.
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            // monzo user ID; can be set during socialite creation.
            $table->text('monzo_user_id')->unique()->nullable();

            // primary account ID, if known. For future use!
            $table->text('monzo_account_id')->nullable();

            // The pot ID we're saving change to.
            $table->text('monzo_pot_id')->nullable();

            // access and refresh tokens; stored encrypted.
            $table->text('monzo_access_token')->nullable();
            $table->text('monzo_refresh_token')->nullable();

            // identifiers for webhooks; stored with the webhook token encrypted.
            $table->string('monzo_user_token')->unique();
            $table->text('monzo_webhook_token');

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
