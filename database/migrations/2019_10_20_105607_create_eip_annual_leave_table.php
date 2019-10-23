<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEipAnnualLeaveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eip_annual_leave', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_no')->unsigned();
            $table->integer('year')->unsigned()->comment('年份');
            $table->integer('annual_leaves')->unsigned()->comment('年假總天數(預設為勞基法,但可以人為修改)');
            $table->integer('labor_annual_leaves')->unsigned()->comment('勞基法年假天數');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eip_annual_leave');
    }
}
