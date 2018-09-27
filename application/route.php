<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;
// api.ipso.me:8083 ==> www.ipso.me:8083/index.php/api
Route::domain('api','api'); // 前提：app/config.php中 url_domain_deploy 值为true
Route::rule('user/:id','user/index');
// post类型访问user.php login() 转成 api.ipso.me:8083/user
Route::post('user','user/login');
Route::get('code/:time/:token/:username/:is_exist','code/get_code');