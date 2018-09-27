<?php
/**
 * Created by PhpStorm.
 * User:  pso318
 * Date: 2018/9/24
 * Time: 11:24
 */

namespace app\api\controller;

use think\Controller;
use think\Request;   // 处理传来的参数
use think\Validate;
use think\Db;
class Common extends Controller
{
    protected $request;  // 用来处理参数
    protected $validate; // 用来验证数据
    protected $params; // 过滤后符合要求的参数 不包含time、token
    protected $rules = array(  // 验证规则
        'User' => array(
            'login' => array(
                'user_name' => 'require|chsDash|max:16',
                'user_pwd' => 'require|min:8',
            )
        ),
        'Code' => array(
            'get_code' => array(
                'username' => 'require|max:16',
                'is_exist' => 'require|number|length:1',
            )
        )
    );

    /**
     * Common初始化
     */
    protected function _initialize(){
        parent::_initialize();  // 继承父类的初始化
        $this->request = Request::instance();
//        $this->check_time($this->request->only(['time'])); // 验证时间
//        $this->check_token($this->request->only(['token'])); // 验证token
        $this->params = $this->check_params($this->request->except(['time','token'])); // 验证参数,except()过滤掉time和token

    }

    /**
     * 验证请求是否超时
     * @param $arr 包含时间戳的数组
     * 返回的结果是json格式
     */
    public function check_time($arr){
        // intval() 如果传过来的参数是字符串就转为0，字符串中含有数字的话就转为只有数字的部分，如果是空数组转为0，不是空数组就转为1
        if (!isset($arr['time']) || intval($arr['time']) <= 1){
            $this->return_msg(400,'时间戳不正确!');
        }
        if (time() - intval($arr['time']) > 60){
            $this->return_msg(400,'请求超时！');
        }
    }

    /**
     * @param $code [结果码  200：正常，4**：数据错误，5**：服务器问题]
     * @param string $msg 接口要返回的提示信息
     * @param array $data  接口要返回的数据
     * @type string 最终返回的数据类型为json数据类型
     */
    public function return_msg($code,$msg='',$data=[]){
        $return_data['code'] = $code;
        $return_data['msg'] = $msg;
        $return_data['data'] = $data;

        echo json_encode($return_data);die;
    }

    /**
     * 验证token(防止篡改数据)
     * @param $arr [全部请求参数]
     * @return string 最终的返回数据类型json格式
     */
    public function check_token($arr){
        if (!isset($arr['token']) || empty($arr['token'])){
            $this->return_msg(400,'token不能为空！');
        }
        $app_token = $arr['token']; // api传过来的token
        unset($arr['token']);   // 将api传过来的token值过滤之后把数据都连接起来用md5加密之后生成服务端token
        $service_token = '';
        foreach($arr as $k => $v){
            $service_token .= md5($v);
        }
        $service_token = md5('api_'.$service_token.'_api'); // 服务器端即时生成的token
        // 如果服务器端token与api传过来的token不相等则报token值不相等错误
        if ($app_token != $service_token){
            $this->return_msg(400,'token值不正确!');
        }
    }

    /**
     * 对api传过来的数据除了time和token以外的数据进行验证，这里没有采用验证器验证，也可以通过创建验证器验证数据
     * @param $arr api传过来的数据
     * @return 将验证结果返回
     */
    public function check_params($arr){
        $rule = $this->rules[$this->request->controller()][$this->request->action()];
        $this->validate = new Validate($rule);
        // 未通过数据验证
        if (!$this->validate->check($arr)){
            $this->return_msg(400,$this->validate->getError());
        }
        // 通过数据验证
        return $arr;
    }

    /**
     * 检测用户名格式上的合法性
     *  用户名可能是邮箱和手机
     * @param $username 用户名
     * @return string 字符串
     */
    public function check_username($username){
        $is_email = Validate::is($username,'email')?1:0;
        $is_phone = preg_match('/~1[34578]\d{9}$/',$username)?4:2;
        $flag = $is_email + $is_phone;
        switch ($flag){
            case 2:
                $this->return_msg(400,'邮箱或号码不正确');
                break;
            case 3:
                return 'email';
                break;
            case 4:
                return 'phone';
                break;
        }
    }

    /**
     * @param $value 需要验证的值，可能是电话或者邮箱
     * @param $type  // 值的类型:phone或者email
     * @param $exist // 是否希望数据库中有
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @code 状态码
     * @return 返回类型为[json]
     */
    public function check_exist($value,$type,$exist){
        $type_num = $type == 'phone' ? 2 : 4;
        $flag = $type_num + $exist;
        $phone_res = db('user')->where('phone',$value)->find();
        $email_res = db('user')->where('email',$value)->find();
        switch ($flag){
            case 2:
                if ($phone_res){
                    $this->return_msg(400,'手机号已经注册');
                }
                break;
            case 3:
                if (!$phone_res){
                    $this->return_msg(400,'手机号不存在');
                }
                break;
            case 4:
                if ($email_res){
                    $this->return_msg(400,'邮箱已经被占用');
                }
                break;
            case 5:
                if (!$email_res){
                    $this->return_msg(400,'邮箱不存在');
                }
                break;
        }
    }
}