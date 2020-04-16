<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('telegram_id')->nullable()->index();
            $table->string('bot_id')->nullable()->index()->default(1);
            $table->string('step_id')->nullable()->index()->default(1);
            $table->tinyInteger('banned')->unsigned()->nullable()->index();
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['telegram_id', 'bot_id']);

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
