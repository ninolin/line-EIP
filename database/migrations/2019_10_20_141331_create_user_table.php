<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('NO');
            $table->char('name', 200);
            $table->char('email', 200);
            $table->string('cname', 120);
            $table->string('google_id', 255);
            $table->string('gmail', 200);
            $table->string('status', 1);
            $table->string('line_id', 64);
            $table->string('line_channel', 64);
            $table->integer('title_id');
            $table->integer('work_class_id')->nullable();
            $table->integer('default_agent_user_no');
            $table->integer('upper_user_no');
            $table->date('onboard_date')->nullable();
            $table->string('eip_level', 10)->default('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
