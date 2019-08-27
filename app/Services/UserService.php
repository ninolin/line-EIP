<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Exception;

class UserService 
{
    protected $userRepo;

    public function __construct
    (
        UserRepository $userRepo
    )
    {
        $this->userRepo = $userRepo;
    }

    public function get_user_info($id, $use_mode) 
    {
        try {
            $data = [];
            if($use_mode == 'web') {
                $data = $this->userRepo->findUserByUserNo($id);
            } else if($use_mode == 'line') {
                $data = $this->userRepo->findUserByLineId($id);
            } else {
                throw new Exception('user_mode不存在');
            }
            if(count($data) != 1) throw new Exception('找不到用戶');

            return [
                'status'=> 'successful',
                'data'  => $data[0]
            ];
        } catch (Exception $e) {
            return [
                'status'    => 'error',
                'message'   => $e->getMessage()
            ];
        }
    }
}