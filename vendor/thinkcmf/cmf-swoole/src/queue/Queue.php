<?php

namespace think\swoole\queue;

use think\facade\Config;

class Queue
{
    protected $config;

    /**
     * config结构
     * [
     *      "queueName"=>[
     *          "delay"=>0,//延迟时间
     *          "sleep"=>3,//休息时间
     *          "maxTries"=>0,//重试次数
     *          "nums"=>2//进程数量
     *       ],
     * ]
     */
    public function getConfig()
    {
        $config       = Config::get('swoole.queue');
        $this->config = $this->getDefaultsValue($config);
        return $this->config;
    }

    public function getDefaultsValue($config = null)
    {
        if (!empty($config) && $config) {
            foreach ($config as $key => $val) {
                isset($config[$key]["delay"]) ? $config[$key] : ($config[$key]["delay"] = 0);
                isset($config[$key]["sleep"]) ? $config[$key] : ($config[$key]["sleep"] = 3);
                isset($config[$key]["maxTries"]) ? $config[$key] : ($config[$key]["maxTries"] = 0);
                isset($config[$key]["nums"]) ? $config[$key] : ($config[$key]["nums"] = 1);
            }
            return $config;
        }
        return false;
    }
}
