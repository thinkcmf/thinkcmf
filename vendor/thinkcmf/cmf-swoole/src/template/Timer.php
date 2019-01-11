<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/19
 * Time: 下午4:40
 */

namespace think\swoole\template;

abstract class Timer
{
    protected $lock = false;

    public function __construct(...$args)
    {
        $this->initialize($args);
    }

    abstract public function initialize($args);

    abstract public function run();
}
