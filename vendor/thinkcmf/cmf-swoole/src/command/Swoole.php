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

namespace think\swoole\command;

use Swoole\Process;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use think\facade\Env;
use think\swoole\Http as HttpServer;
use think\Container;

/**
 * Swoole HTTP 命令行，支持操作：start|stop|restart|reload
 * 支持应用配置目录下的swoole.php文件进行参数配置
 */
class Swoole extends Command
{
    protected $config = [];

    public function configure()
    {
        $this->setName('swoole')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload", 'start')
            ->addOption('host', 'H', Option::VALUE_OPTIONAL, 'the host of swoole server.', null)
            ->addOption('port', 'p', Option::VALUE_OPTIONAL, 'the port of swoole server.', null)
            ->addOption('daemon', 'd', Option::VALUE_NONE, 'Run the swoole server in daemon mode.')
            ->setDescription('Swoole HTTP Server for ThinkPHP');
    }

    public function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');

        $this->init();

        if (in_array($action, ['start', 'stop', 'reload', 'restart'])) {
            $this->$action();
        } else {
            $output->writeln("<error>Invalid argument action:{$action}, Expected start|stop|restart|reload .</error>");
        }
    }

    protected function init()
    {
        $this->config = Config::pull('swoole');

        if (empty($this->config['pid_file'])) {
            $this->config['pid_file'] = Env::get('runtime_path') . 'swoole.pid';
        }

        // 避免pid混乱
        $this->config['pid_file'] .= '_' . $this->getPort();
    }

    protected function getHost()
    {
        if ($this->input->hasOption('host')) {
            $host = $this->input->getOption('host');
        } else {
            $host = !empty($this->config['host']) ? $this->config['host'] : '0.0.0.0';
        }

        return $host;
    }

    protected function getPort()
    {
        if ($this->input->hasOption('port')) {
            $port = $this->input->getOption('port');
        } else {
            $port = !empty($this->config['port']) ? $this->config['port'] : 9501;
        }

        return $port;
    }

    /**
     * 启动server
     * @access protected
     * @return void
     */
    protected function start()
    {
        $pid = $this->getMasterPid();

        if ($this->isRunning($pid)) {
            $this->output->writeln('<error>swoole http server process is already running.</error>');
            return false;
        }

        $this->output->writeln('Starting swoole http server...');

        $host = $this->getHost();
        $port = $this->getPort();
        $mode = !empty($this->config['mode']) ? $this->config['mode'] : SWOOLE_PROCESS;
        $type = !empty($this->config['sock_type']) ? $this->config['sock_type'] : SWOOLE_SOCK_TCP;

        $ssl = !empty($this->config['ssl']) || !empty($this->config['open_http2_protocol']);
        if ($ssl) {
            $type = SWOOLE_SOCK_TCP | SWOOLE_SSL;
        }

        $swoole = new HttpServer($host, $port, $mode, $type);

        // 开启守护进程模式
        if ($this->input->hasOption('daemon')) {
            $this->config['daemonize'] = true;
        }

        // 设置应用目录
        $swoole->setAppPath($this->config['app_path']);

        // 创建内存表
        if (!empty($this->config['table'])) {
            $swoole->table($this->config['table']);
            unset($this->config['table']);
        }

        $swoole->cachetable();

        // 设置文件监控 调试模式自动开启
        if (Env::get('app_debug') || !empty($this->config['file_monitor'])) {
            $interval = isset($this->config['file_monitor_interval']) ? $this->config['file_monitor_interval'] : 2;
            $paths    = isset($this->config['file_monitor_path']) ? $this->config['file_monitor_path'] : [];
            $swoole->setMonitor($interval, $paths);
            unset($this->config['file_monitor'], $this->config['file_monitor_interval'], $this->config['file_monitor_path']);
        }

        // 设置服务器参数
        if (isset($this->config['pid_file'])) {

        }
        $swoole->option($this->config);

        $this->output->writeln("Swoole http server started: <http://{$host}:{$port}>");
        $this->output->writeln('You can exit with <info>`CTRL-C`</info>');

        $hook = Container::get('hook');
        $hook->listen("swoole_server_start", $swoole);

        $swoole->start();
    }

    /**
     * 柔性重启server
     * @access protected
     * @return void
     */
    protected function reload()
    {
        $pid = $this->getMasterPid();

        if (!$this->isRunning($pid)) {
            $this->output->writeln('<error>no swoole http server process running.</error>');
            return false;
        }

        $this->output->writeln('Reloading swoole http server...');
        Process::kill($pid, SIGUSR1);
        $this->output->writeln('> success');
    }

    /**
     * 停止server
     * @access protected
     * @return void
     */
    protected function stop()
    {
        $pid = $this->getMasterPid();

        if (!$this->isRunning($pid)) {
            $this->output->writeln('<error>no swoole http server process running.</error>');
            return false;
        }

        $this->output->writeln('Stopping swoole http server...');

        Process::kill($pid, SIGTERM);
        $this->removePid();

        $this->output->writeln('> success');
    }

    /**
     * 重启server
     * @access protected
     * @return void
     */
    protected function restart()
    {
        $pid = $this->getMasterPid();

        if ($this->isRunning($pid)) {
            $this->stop();
        }

        $this->start();
    }

    /**
     * 获取主进程PID
     * @access protected
     * @return int
     */
    protected function getMasterPid()
    {
        $pidFile = $this->config['pid_file'];

        if (is_file($pidFile)) {
            $masterPid = (int) file_get_contents($pidFile);
        } else {
            $masterPid = 0;
        }

        return $masterPid;
    }

    /**
     * 删除PID文件
     * @access protected
     * @return void
     */
    protected function removePid()
    {
        $masterPid = $this->config['pid_file'];

        if (is_file($masterPid)) {
            unlink($masterPid);
        }
    }

    /**
     * 判断PID是否在运行
     * @access protected
     * @param  int $pid
     * @return bool
     */
    protected function isRunning($pid)
    {
        if (empty($pid)) {
            return false;
        }

        return Process::kill($pid, 0);
    }
}
