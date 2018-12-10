<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think\swoole;

use Swoole\Http\Server as HttpServer;
use Swoole\Table;
use think\Facade;
use think\facade\Config;
use think\Loader;
use think\swoole\facade\Timer as TimerF;
use think\Container;
use think\swoole\queue\Task as QueueTask;
use think\swoole\queue\Process as QueueProcess;
use Swoole\WebSocket\Server as WebSocketServer;

/**
 * Swoole Http Server 命令行服务类
 */
class Http extends Server
{
    protected $app;
    protected $appPath;
    protected $table;
    protected $cachetable;
    protected $monitor;
    protected $server_type;
    protected $lastMtime;
    protected $fieldType = [
        'int'    => Table::TYPE_INT,
        'string' => Table::TYPE_STRING,
        'float'  => Table::TYPE_FLOAT,
    ];

    protected $fieldSize = [
        Table::TYPE_INT    => 4,
        Table::TYPE_STRING => 32,
        Table::TYPE_FLOAT  => 8,
    ];

    /**
     * 架构函数
     * @access public
     */
    public function __construct($host, $port, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP)
    {
        $this->server_type = Config::get('swoole.server_type');
        switch ($this->server_type) {
            case 'websocket':
                $this->swoole = new WebSocketServer($host, $port, $mode, SWOOLE_SOCK_TCP);
                break;
            default:
                $this->swoole = new HttpServer($host, $port, $mode, SWOOLE_SOCK_TCP);
        }
        if ("process" == Config::get('swoole.queue_type')) {
            $process = new QueueProcess();
            $process->run($this->swoole);
        }
    }

    public function setAppPath($path)
    {
        $this->appPath = $path;
    }

    public function setMonitor($interval = 2, $path = [])
    {
        $this->monitor['interval'] = $interval;
        $this->monitor['path']     = (array) $path;
    }

    public function table(array $option)
    {
        $size        = !empty($option['size']) ? $option['size'] : 1024;
        $this->table = new Table($size);

        foreach ($option['column'] as $field => $type) {
            $length = null;

            if (is_array($type)) {
                list($type, $length) = $type;
            }

            if (isset($this->fieldType[$type])) {
                $type = $this->fieldType[$type];
            }

            $this->table->column($field, $type, isset($length) ? $length : $this->fieldSize[$type]);
        }

        $this->table->create();
    }

    public function cachetable()
    {
        $this->cachetable = new CacheTable();
    }

    public function option(array $option)
    {
        // 设置参数
        if (!empty($option)) {
            $this->swoole->set($option);
        }

        foreach ($this->event as $event) {
            // 自定义回调
            if (!empty($option[$event])) {
                $this->swoole->on($event, $option[$event]);
            } elseif (method_exists($this, 'on' . $event)) {
                $this->swoole->on($event, [$this, 'on' . $event]);
            }
        }
        if ("websocket" == $this->server_type) {
            foreach ($this->event as $event) {
                if (method_exists($this, 'Websocketon' . $event)) {
                    $this->swoole->on($event, [$this, 'Websocketon' . $event]);
                }
            }
        }
    }

    /**
     * 此事件在Worker进程/Task进程启动时发生,这里创建的对象可以在进程生命周期内使用
     *
     * @param $server
     * @param $worker_id
     */
    public function onWorkerStart($server, $worker_id)
    {
        // 应用实例化
        $this->app       = new Application($this->appPath);
        $this->lastMtime = time();

        //swoole server worker启动行为
        $hook = Container::get('hook');
        $hook->listen('swoole_worker_start', ['server' => $server, 'worker_id' => $worker_id]);

        // Swoole Server保存到容器
        $this->app->swoole = $server;

        if ($this->table) {
            $this->app['swoole_table'] = $this->table;
        }

        $this->app->cachetable = $this->cachetable;

        // 指定日志类驱动
        Loader::addClassMap([
            'think\\log\\driver\\File'    => __DIR__ . '/log/File.php',
            'think\\cache\\driver\\Table' => __DIR__ . '/cache/driver/Table.php',
        ]);

        Facade::bind([
            'think\facade\Cookie'     => Cookie::class,
            'think\facade\Session'    => Session::class,
            facade\Application::class => Application::class,
            facade\Http::class        => Http::class,
            facade\Task::class        => Task::class,
            facade\Timer::class       => Timer::class,
        ]);

        // 应用初始化
        $this->app->initialize();

        $this->app->bindTo([
            'cookie'  => Cookie::class,
            'session' => Session::class,
        ]);

        $this->initServer($server, $worker_id);

        if (0 == $worker_id && $this->monitor) {
            $this->monitor($server);
        }

        //只在一个进程内执行定时任务
        if (0 == $worker_id) {
            $this->timer($server);
        }
    }

    /**
     * 自定义初始化Swoole
     * @param $server
     * @param $worker_id
     */
    public function initServer($server, $worker_id)
    {
        $wokerStart = Config::get('swoole.wokerstart');
        if ($wokerStart) {
            if (is_string($wokerStart) && class_exists($wokerStart)) {
                $obj = new $wokerStart($server, $worker_id);
                $obj->run();
                unset($obj);
            } elseif ($wokerStart instanceof \Closure) {
                $wokerStart($server, $worker_id);
            }
        }
    }

    /**
     * 文件监控
     *
     * @param $server
     */
    protected function monitor($server)
    {
        $paths = $this->monitor['path'] ?: [$this->app->getAppPath(), $this->app->getConfigPath()];
        $timer = $this->monitor['interval'] ?: 2;

        $server->tick($timer, function () use ($paths, $server) {
            foreach ($paths as $path) {
                $dir      = new \RecursiveDirectoryIterator($path);
                $iterator = new \RecursiveIteratorIterator($dir);

                foreach ($iterator as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) != 'php') {
                        continue;
                    }

                    if ($this->lastMtime < $file->getMTime()) {
                        $this->lastMtime = $file->getMTime();
                        echo '[update]' . $file . " reload...\n";
                        $server->reload();
                        return;
                    }
                }
            }
        });
    }

    /**
     * 系统定时器
     *
     * @param $server
     */
    public function timer($server)
    {
        $timer    = Config::get('swoole.timer');
        $interval = intval(Config::get('swoole.interval'));
        if ($timer) {
            $interval = $interval > 0 ? $interval : 1000;
            $server->tick($interval, function () use ($server) {
                TimerF::run($server);
            });
        }
        $queue_type = Config::get('swoole.queue_type');
        $task       = QueueTask::instance();
        if ("task" == $queue_type) {
            $server->tick(1000, function () use ($queue_type, $task) {
                $task->run();
            });
        }
    }

    /**
     * request回调
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response)
    {
        // 执行应用并响应
        $this->app->swoole($request, $response);
    }

    /**
     * Message回调
     * @param $server
     * @param $frame
     */
    public function WebsocketonMessage($server, $frame)
    {
        // 执行应用并响应
        $this->app->swooleWebSocket($server, $frame);
    }

    /**
     * 任务投递
     * @param HttpServer $serv
     * @param $task_id
     * @param $fromWorkerId
     * @param $data
     * @return mixed|null
     */
    public function onTask(HttpServer $serv, $task_id, $fromWorkerId, $data)
    {
        if (is_string($data) && class_exists($data)) {
            $taskObj = new $data;
            if (method_exists($taskObj, 'run')) {
                $taskObj->run($serv, $task_id, $fromWorkerId);
                unset($taskObj);
                return true;
            }
        }

        if (is_object($data) && method_exists($data, 'run')) {
            $data->run($serv, $task_id, $fromWorkerId);
            unset($data);
            return true;
        }

        if ($data instanceof SuperClosure) {
            return $data($serv, $task_id, $data);
        } else {
            $serv->finish($data);
        }
    }

    /**
     * 任务结束，如果有自定义任务结束回调方法则不会触发该方法
     * @param HttpServer $serv
     * @param $task_id
     * @param $data
     */
    public function onFinish(HttpServer $serv, $task_id, $data)
    {
        if ($data instanceof SuperClosure) {
            $data($serv, $task_id, $data);
        }
    }
}
