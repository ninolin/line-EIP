<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;
use DB;

class GenCompensatoryLeave extends Command
{
    protected $signature = 'gen:compensatoryleave';
    protected $description = '加班日後發放補休';

    public function __construct()
    {
        parent::__construct();
    }

    // Console 執行的程式
    public function handle()
    {
        $dates = DB::select("SELECT id, over_work_date, over_work_hours from eip_leave_apply where apply_status = 'Y' and apply_type = 'O' and DATEDIFF(now(), over_work_date) = 1", []);
        foreach ($dates as $d) {
            $sql = "insert into eip_compensatory_leave ";
            $sql .= "(apply_id, over_work_date, over_work_hours) ";
            $sql .= "value ";
            $sql .= "(?, ?, ?) ";
            if(DB::insert($sql, [$d->id, $d->over_work_date, $d->over_work_hours]) != 1) {
                
            }
        }
    }
}