<?php
namespace api\common\exception;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/28
 * Time: 15:27
 */
use Exception;
use think\exception\Handle;
use think\exception\HttpException;

class Http extends Handle
{
    public function render(Exception $e)
    {
        if (APP_DEBUG==true) {
            return parent::render($e);
        } elseif ($e instanceof ValidateException) {
            $msg=$e->getMessage();
        } else {
            $msg= '系统错误！';
        }
        $code=0;
        $httpCode=500;
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => [],
        ];
        return json($result, $httpCode);
    }
}
