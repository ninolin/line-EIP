<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Log;


class HelperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public static function get_req($_url)
    {
        $ch = curl_init($_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
        //log::info($json_content);
    }

    public static function post_req($_url, $_data)
    {
        log::info($_url);
        $header[] = "Content-Type: application/json";
        $ch = curl_init($_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  	    curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);                                                                  
  	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
  	    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
  	    $result = curl_exec($ch);
        curl_close($ch);
        log::info($result);
        return $result;
        //log::info($json_content);
    }
}
