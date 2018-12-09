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

namespace think\swoole\facade;

use think\Facade;

/**
 * @see \think\swoole\Http
 * @mixin \think\swoole\Http
 * @method void option(array $option) static 参数设置
 * @method void start() static 启动服务
 * @method void stop() static 停止服务
 */
class Http extends Facade
{
}
