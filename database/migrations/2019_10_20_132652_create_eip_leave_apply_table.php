<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEipLeaveApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eip_leave_apply', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('apply_user_no')->unsigned();
            $table->enum('apply_type', ['L', 'O'])->default('L')->comment('Leave,Overwork');
            $table->integer('agent_user_no')->unsigned()->nullable();
            $table->integer('leave_type')->unsigned()->comment('eip_leave_type的id');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->float('leave_hours', 8, 2)->nullable()->default(null);
            $table->date('over_work_date')->nullable();
            $table->float('over_work_hours', 8, 2)->nullable()->default(null);
            $table->text('comment')->nullable()->default(null);
            $table->string('event_id', 128)->nullable();
            $table->timestamp('apply_time')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->enum('apply_status', ['P', 'Y', 'N', 'C'])->default('P')->comment('Process,Yes,No,Cencel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eip_leave_apply');
    }
}
