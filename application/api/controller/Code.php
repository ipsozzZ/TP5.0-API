<?php
/**
 * Created by PhpStorm.
 * User:  pso318
 * Date: 2018/9/26
 * Time: 14:14
 */

namespace app\api\controller;


class Code extends Common
{
    public function get_code(){
        $username = $this->params['username'];
        $exist = $this->params['is_exist'];
        $username_type = $this->check_username($username);
        switch ($username_type){
            case 'phone':
                $this->get_code_by_phone($username,'phone',$exist);
                break;
            case 'email':
                $this->get_code_by_email($username,'email',$exist);
                break;
        }
        echo "_get_code";
    }

    /**
     * 通过邮箱/手机获取验证码
     * @param $username 手机/邮箱号码
     * @param $type username的类型，手机或邮箱
     * @param $exist 检测username是否在数据库中存在
     * @return [json] json类型数据
     */
    public function get_code_by_username($username,$type,$exist){
        if ($type == 'phone'){
            $type_name = '手机';
        }else{
            $type_name = '邮箱';
        }
        // 检测手机
        $this->check_exist($username,$type,$exist);
        /* 检测验证码请求频率，60秒一次 , 问好是php的语法，表示判断后面的条件对不对，对返回true，不对返回false. */
        if (session("?".$username.'_last_send_time_')){
           if (time() - session($username.'_last_send_time_') < 60){
               $this->return_msg(400,$type_name.'验证码只能60秒发送一次');
           }
        }
        /* 生成验证码 */
        $code = $this->make_code(6);
        /* 使用session保存验证信息，方便比较，并用md5加密 */
        $md5_code = md5($username.'_'.md5($code));
        session($username.'_code'.$md5_code);
        /* 储存验证码的发送时间 */
        session($username.'_last_send_time_',time());
        /* 发送验证码 */
        if ($type == 'phone'){
            $this->send_code_to_phone($username,$code);
        }else{
            $this->send_code_to_email($username,$code);
        }
    }

    /**
     * 生成验证码
     * @param $num  需要生成验证码的位数
     * @return int 生成的二维码
     */
    public function make_code($num){
        $max = pow(10,$num)-1;
        $min = pow(10,$num-1);
        return rand($min,$max);
    }

    /**
     * 发送验证码给手机
     */
    public function send_code_to_phone(){
        echo 'send_code_to_phone';
    }

    /**
     * 发送验证码给邮箱
     */
    public function send_code_to_email(){
        echo 'send_code_to_email';
    }
}