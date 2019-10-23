<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEipLeaveApplyChangeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eip_leave_apply_change_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('apply_id')->unsigned();
            $table->integer('apply_process_id')->unsigned()->nullable()->default(null);
            $table->string('change_desc', 128);
            $table->string('change_reason', 128)->nullable()->nullable()->default(null);
            $table->timestamp('change_time')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->integer('change_user_no')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eip_leave_apply_change_log');
    }
}
