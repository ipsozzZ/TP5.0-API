<?php
/**
 * Created by PhpStorm.
 * User:  pso318
 * Date: 2018/9/21
 * Time: 22:34
 */

namespace app\api\controller;


class User extends Common
{
    public function index(){
        echo 'user_index';
    }
    public function login(){
//        $data = $this->params;
//        dump($data);
        echo 'user_login';
    }
}