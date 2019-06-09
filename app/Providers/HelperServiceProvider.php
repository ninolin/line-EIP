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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json_content = curl_exec($ch);
        curl_close($ch);
        return $json_content;
        //log::info($json_content);
    }
}
