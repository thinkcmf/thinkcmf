<?php

namespace think\cache\driver;

use think\cache\Driver;
use think\Container;

/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/9/1
 * Time: 上午11:36
 * Email:499873958@qq.com
 */
class Table extends Driver
{
    protected $options = [
        'expire'     => 0,
        'prefix'     => '',
        'serialize'  => true,
    ];
    public function __construct($options = [])
    {
        $this->handler = Container::get('cachetable');
    }

    public function set($name, $value, $expire = null)
    {
        $this->writeTimes++;

        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }

        if ($this->tag && !$this->has($name)) {
            $first = true;
        }

        $key    = $this->getCacheKey($name);
        $expire = $this->getExpireTime($expire);

        $value = $this->serialize($value);

        if ($expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
        }

        isset($first) && $this->setTagItem($key);

        return $result;
    }

    public function dec($name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) - $step;
            $expire = $this->expire;
        } else {
            $value  = -$step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }

    public function clear($tag = null)
    {
        $this->writeTimes++;

        return $this->handler->clear();
    }

    public function get($name, $default = false)
    {
        $this->readTimes++;

        $value = $this->handler->get($this->getCacheKey($name));

        if (is_null($value) || false === $value) {
            return $default;
        }

        return $this->unserialize($value);
    }

    public function has($name)
    {
        return $this->handler->exists($this->getCacheKey($name));
    }

    public function rm($name)
    {
        $this->writeTimes++;
        return $this->handler->del($this->getCacheKey($name));
    }

    public function inc($name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) + $step;
            $expire = $this->expire;
        } else {
            $value  = $step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }
}
