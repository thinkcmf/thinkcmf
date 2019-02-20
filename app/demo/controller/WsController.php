<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\demo\controller;

use cmf\controller\HomeBaseController;
use think\swoole\WebSocketFrame;

class WsController extends HomeBaseController
{
    public function index()
    {
        $client = WebSocketFrame::getInstance();

        $message=$this->request->post('message');

        //发送数据给当前请求的客户端
        $client->pushToClient(['message' => $message." from server"]);//参数为数组，字符串，数字

        return "";
    }
}
