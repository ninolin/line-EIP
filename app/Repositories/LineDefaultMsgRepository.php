<?php

namespace App\Repositories;

use DB;
use Exception;
use Log;

class LineDefaultMsgRepository {

    public function __construct() 
    {

    }

    public function findAllMessage($page = null) 
    {
        try {
            $sql = 'select * from eip_line_default_message order by event';
            if (!is_null($page)) {
                $sql .= ' limit '.(($page-1)*10).',10 ';
            }

            $total_msgs = DB::select('select count(1) as count from eip_line_default_message', []);
            $total_pages = ceil($total_msgs[0]->count/10);

            $data = DB::select($sql, []);

            return [
                'data'          => $data,
                'total_pages'   => $total_pages
            ];

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findOneMessage($event, $hook_user_type) 
    {
        try {
            if (!is_string($event) || !is_string($hook_user_type)) throw new Exception("repository parameter error");

            $sql = 'select * from eip_line_default_message where event =? and hook_user_type =?';
            $data = DB::select($sql, [$event, $hook_user_type]);

            // $data = DB::table('eip_line_default_message')
            //     ->where('event', $event)
            //     ->where('hook_user_type', $hook_user_type)
            //     ->value('message');
            return $data;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findEventMessage($event) 
    {
        try {
            if (!is_string($event)) throw new Exception("repository parameter error");

            $sql = 'select * from eip_line_default_message where event =?';
            return DB::select($sql, [$event]);

        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function updateMessage($id, $message=null) 
    {
        try {
            
            if (!is_numeric($id)) throw new Exception("repository parameter error");

            DB::update("update eip_line_default_message set message =? where id =?", [$message, $id]);
            return "successful";

        } catch (Exception $e) {
            throw $e;
        }
    }
}