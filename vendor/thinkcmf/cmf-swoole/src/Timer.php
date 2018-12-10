<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/19
 * Time: 下午4:11
 */

namespace think\swoole;

use Swoole\Timer as SwooleTimer;
use think\facade\Config;
use think\swoole\facade\Task as TaskF;
use XCron\CronExpression;

/**
 * Class Timer
 * 可以执行回调函数，同时可以执行定时器模板
 * @package xavier\swoole
 */

class Timer
{
    private static $timerlists = [];
    private $config            = [];

    public function __construct()
    {
        //获取配置信息
        $this->config = Config::pull('timer');

        if (empty($this->config)) {
            $this->config = [];
        }
    }

    /**
     * 开始执行定时器任务
     * @param $serv 服务对象
     */
    public function run($serv)
    {
        if (count(self::$timerlists) > 0) {
            $this->startTask();
        } else {
            $this->initimerlists();
        }
    }

    /**
     * 到期后执行定时任务
     */
    public function startTask()
    {
        foreach (self::$timerlists as &$one) {
            if ($one['next_time'] <= time()) {
                $cron = CronExpression::factory($one['key']);

                $one['next_time'] = $cron->getNextRunDate()->getTimestamp();

                $this->syncTask($one['val']);
            }
        }
        unset($one);
    }

    /**
     * 根据定时配置计算下次执行时间并存储相关信息
     * @throws \Exception
     */
    public function initimerlists()
    {
        $i = 0;
        foreach ($this->config as $key => $val) {
            try {
                $cron = CronExpression::factory($key);
                $time = $cron->getNextRunDate()->getTimestamp();

                self::$timerlists[$i]['key']       = $key;
                self::$timerlists[$i]['val']       = $val;
                self::$timerlists[$i]['next_time'] = $time;
            } catch (\Exception $e) {
                var_dump($e);
                throw new \Exception("定时器异常");
            }
            $i++;
        }
    }

    /**
     * 异步投递任务到task worker
     * @param string $class
     */
    public function syncTask($class)
    {
        if (is_string($class) && class_exists($class)) {
            TaskF::async(function () use ($class) {
                $obj = new $class();
                $obj->run();
                unset($obj);
            });
        }
    }

    /**
     * 每隔固定时间执行一次
     * @param int       $time       间隔时间
     * @param mixed     $callback   可以是回调 可以是定时器任务模板
     * @return bool
     */
    public function tick($time, $callback)
    {
        if ($callback instanceof \Closure) {
            return SwooleTimer::tick($time, $callback);
        } elseif (is_object($callback) && method_exists($callback, 'run')) {
            return SwooleTimer::tick($time, function () use ($callback) {
                $callback->run();
            });
        }

        return false;
    }

    /**
     * 延迟执行
     * @param int       $time       间隔时间
     * @param mixed     $callback   可以是回调 可以是定时器任务模板
     * @return bool
     */
    public function after($time, $callback)
    {
        if ($callback instanceof \Closure) {
            return SwooleTimer::after($time, $callback);
        } elseif (is_object($callback) && method_exists($callback, 'run')) {
            return SwooleTimer::after($time, function () use ($callback) {
                $callback->run();
                unset($callback);
            });
        }

        return false;
    }

    /**
     * 清除定时器
     * @param int $timerId
     * @return bool
     */
    public function clear($timerId)
    {
        return SwooleTimer::clear($timerId);
    }
}
