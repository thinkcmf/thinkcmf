<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/19
 * Time: 下午4:17
 */

namespace think\swoole\facade;

use think\Facade;

class Timer extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return '\\think\\swoole\\Timer';
    }
}
