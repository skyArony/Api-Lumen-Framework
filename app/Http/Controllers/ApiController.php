<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Laravel\Lumen\Routing\Controller as BaseController;

class ApiController extends BaseController
{
    use Helpers;

    public $errcode;
    public $errmsg;
    public $status;
    public $header = '未封包';
    public $debug = '未调试';

    // 构造响应基本框架
    public  function createResponse($data, $status, $errcode, $sessionid = null)
    {
        // 设置 errMsg
        $this->__setErrMsg($errcode);
        $body = array(
            'errcode' => $errcode,
            'status' => $status,
            'errmsg' => $this->errmsg,
            'sessionid' => $sessionid,
            'header' => $this->header,
            'debug' => $this->debug,
            'data' => $data
        );
        return $this->response->array($body)->setStatusCode($status);
    }
    
    // 设置debug内容
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    // 当 envelope=true 时将，将 header 头插入返回体中
    public function setHeader($header)
    {
        $this->header = $header;
    }

    // 设置错误码和错误内容
    protected function __setErrMsg($errcode)
    {
        $msgForCode = array(
            0 => '成功',
            1 => '成功(未修改密码)',
            2 => '成功(未绑定邮箱) 请前往教务系统绑定邮箱 ：http://jwxt.xtu.edu.cn/jsxsd/',
            3 => '删除了不存在的数据',
            -1 => '网络故障',
            -2 => '未知错误',
            -3 => '验证码错误',
            -4 => '用户名或密码错误',
            -5 => 'HTTP错误',
            -6 => '唯一性限制',
            -7 => '更新不存在的数据，导致失败',
            -8 => '数据不存在',
            -9 => '越权限操作',
            -10 => '账户可调用次数不足',
            -11 => '数据不存在',
            -65535 => '参数错误'
        );
        $this->errmsg = $msgForCode[$errcode];
    }
}
