<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEipLineMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eip_line_message', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('username', 200)->nullable();
            $table->string('line_channel', 64);
            $table->string('line_id', 64);
            $table->text('body');
            $table->string('type', 64)->nullable();
            $table->text('message', 255)->nullable();
            $table->integer('time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eip_line_message');
    }
}
