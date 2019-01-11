<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/23
 * Time: 下午5:20
 * Email:499873958@qq.com
 */

namespace think\swoole\template;

abstract class WorkerStart
{
    private $server;
    private $worker_id;

    public function __construct($server, $worker_id)
    {
        $this->server    = $server;
        $this->worker_id = $worker_id;
        $this->_initialize($server, $worker_id);
    }

    abstract public function _initialize($server, $worker_id);

    abstract public function run();
}
