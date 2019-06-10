<?php

namespace App\Http\Controllers\Line;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Providers\HelperServiceProvider;
use DateTime;
use Log;
class Test extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        // $d = new DateTime();
        // $timeMin = rawurlencode($d->format('Y-m-d\TH:i:sP'));
        // HelperServiceProvider::get_req("https://www.googleapis.com/calendar/v3/calendars/zh-tw.taiwan%23holiday%40group.v.calendar.google.com/events?key=AIzaSyB0ZMfTWE_h_qAVNRWpZFnDUOPaiT-a7xo&timeMin=".$timeMin);
        
        $return = self::is_offday_by_gcalendar("2019-06-08");
        $dates = self::dates2array("2019-06-08", "2019-06-10");
        $hours = 0;
        foreach ($dates as $e) {
            //log::info(self::is_offday_by_gcalendar($e));
            $hours += self::is_offday_by_gcalendar($e);
        }
        return response()->json([
            'status' => $hours
        ]);
    }
    static protected function dates2array($date1, $date2) {
        $return= array();
        $diff_date = (strtotime($date2) - strtotime($date1))/ (60*60*24); //計算相差之天數
        for ($i=0; $i<=$diff_date; $i++) {
            array_push($return, date('Y-m-d', strtotime('+'.$i.' days', strtotime($date1))));
        }
        return $return;
    }

    static protected function is_offday_by_gcalendar($check_date) {
        $timeMin = rawurlencode($check_date."T00:00:00Z");
        $timeMax = rawurlencode(date('Y-m-d', strtotime('+1 days', strtotime($check_date)))."T00:00:00Z");
        $calevents_str = HelperServiceProvider::get_req("https://www.googleapis.com/calendar/v3/calendars/nino.dev.try%40gmail.com/events?key=AIzaSyB0ZMfTWE_h_qAVNRWpZFnDUOPaiT-a7xo&timeMin=".$timeMin."&timeMax=".$timeMax);
        $calevents = json_decode($calevents_str) -> items;
        //return $calevents;
        $offhours = 0;
        foreach ($calevents as $e) {
            if($e-> status == "confirmed") {
                if(strpos($e-> summary,'休息日') !== false && $e-> start-> date == $check_date) {
                    $offhours = $offhours + 8;
                }
            }
            if($e-> status == "cancelled") {
                $offhours = $offhours - 8;
            }
        }
        return $offhours;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
