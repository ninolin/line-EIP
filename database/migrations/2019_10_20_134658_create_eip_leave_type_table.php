<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEipLeaveTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eip_leave_type', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 32);
            $table->integer('day');
            $table->integer('min_time')->default(30)->comment('最小請假分鐘');
            $table->boolean('annual')->default(0)->comment('是否為年休假');
            $table->boolean('compensatory')->default(0)->comment('是否為加班補休');
            $table->integer('approved_title_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eip_leave_type');
    }
}
