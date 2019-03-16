<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

require_once(app_path().'/constants.php');

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
            $table->string('username', USERNAME_MAX_LENGTH)->unique();
            $table->string('email', EMAIL_MAX_LENGTH)->unique();
            $table->string('password', PASSWORD_LENGTH);

            // Random string sent to user's email upon registration, nullified after confirmation
            $table->string('email_confirmation_code', EMAIL_CONFIRMATION_LENGTH)->nullable();

            /*
            0 = unconfirmed                                             ACCOUNT_STATUS_UNCONFIRMED
            1 = email confirmed, in good standing, non-premium          ACCOUNT_STATUS_CONFIRMED
            2 = premium user                                            ACCOUNT_STATUS_PREMIUM
            3 = previously premium user, currently not premium          ACCOUNT_STATUS_PREV_PREMIUM
            4 = user cancelled account                                  ACCOUNT_STATUS_CANCELLED
            5 = banned                                                  ACCOUNT_STATUS_BANNED
            6 = admin                                                   ACCOUNT_STATUS_ADMIN
            */
            $table->tinyInteger('account_status')->unsigned()->default(0);

            $table->softDeletes();
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
