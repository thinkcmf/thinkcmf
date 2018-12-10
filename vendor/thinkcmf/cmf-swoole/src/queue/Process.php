<?php
namespace think\swoole\queue;

use think\queue\Worker;
use think\facade\Config;

/**
 * 进程模式运行队列
 * 优点：每个队列都有一个或者多个进程，且互相不干扰
 */
class Process extends Queue
{
    private $new_index = 0;
    private $maxtimes = 0;
    private static $times = 0;

    public function __construct()
    {
        $this->getConfig();
        $maxtimes       = Config::get('swoole.queue_maxtimes');
        $this->maxtimes = $maxtimes ? $maxtimes : 10000;
    }

    public function run($server)
    {
        $list = $this->config;
        foreach ($list as $key => $val) {
            for ($i = 0; $i < $val['nums']; $i++) {
                $p = $this->CreateProcess($key, $val);
                $server->addProcess($p);
            }
        }
    }

    public function CreateProcess($key = null, $val)
    {
        $index   = $key . $val['nums'];
        $process = new \swoole_process(function ($process) use ($index, $key, $val) {
            if (is_null($index)) {
                $index = $this->new_index;
                $this->new_index++;
            }
            \swoole_set_process_name(sprintf('php-ps:%s', $index));

            while (true) {
                $this->job($key, $val);
                self::$times++;
                if (self::$times > $this->maxtimes) {//一定次数后挂掉进程，master进程会重新启动进程，这样可以防止长时间运行造成的内存泄露
                    $process->exit();
                }
            }
        }, false, false);
        return $process;
    }

    public function job($key, $val)
    {
        $worker = new Worker();
        $worker->pop($key, $val['delay'], $val['sleep'], $val['maxTries']);
        unset($worker);
    }
}
