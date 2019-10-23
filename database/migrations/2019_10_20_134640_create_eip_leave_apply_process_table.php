<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEipLeaveApplyProcessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eip_leave_apply_process', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('apply_id')->unsigned();
            $table->enum('apply_type', ['L', 'O'])->default('L');
            $table->integer('apply_user_no');
            $table->integer('upper_user_no');
            $table->boolean('is_validate')->nullable()->default(null);
            $table->string('reject_reason', 255)->nullable()->default(null);
            $table->timestamp('validate_time')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eip_leave_apply_process');
    }
}
