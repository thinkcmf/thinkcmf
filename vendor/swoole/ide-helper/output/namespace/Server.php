<?php
namespace Swoole;

class Server
{

    private $onConnect;
    private $onReceive;
    private $onClose;
    private $onPacket;
    private $onBufferFull;
    private $onBufferEmpty;
    private $onStart;
    private $onShutdown;
    private $onWorkerStart;
    private $onWorkerStop;
    private $onWorkerExit;
    private $onWorkerError;
    private $onTask;
    private $onFinish;
    private $onManagerStart;
    private $onManagerStop;
    private $onPipeMessage;
    public $setting;
    public $connections;
    public $host;
    public $port;
    public $type;
    public $mode;
    public $ports;
    public $master_pid;
    public $manager_pid;
    public $worker_id;
    public $taskworker;
    public $worker_pid;

    /**
     * @param $host[required]
     * @param $port[optional]
     * @param $mode[optional]
     * @param $sock_type[optional]
     * @return mixed
     */
    public function __construct($host, $port = null, $mode = null, $sock_type = null){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $host[required]
     * @param $port[required]
     * @param $sock_type[required]
     * @return mixed
     */
    public function listen($host, $port, $sock_type){}

    /**
     * @param $host[required]
     * @param $port[required]
     * @param $sock_type[required]
     * @return mixed
     */
    public function addlistener($host, $port, $sock_type){}

    /**
     * @param $event_name[required]
     * @param $callback[required]
     * @return mixed
     */
    public function on($event_name, $callback){}

    /**
     * @param $settings[required]
     * @return mixed
     */
    public function set($settings){}

    /**
     * @return mixed
     */
    public function start(){}

    /**
     
     * 向客户端发送数据
     *
     *  * $data，发送的数据。TCP协议最大不得超过2M，UDP协议不得超过64K
     *  * 发送成功会返回true，如果连接已被关闭或发送失败会返回false
     *
     * TCP服务器
     * -------------------------------------------------------------------------
     *  * send操作具有原子性，多个进程同时调用send向同一个连接发送数据，不会发生数据混杂
     *  * 如果要发送超过2M的数据，可以将数据写入临时文件，然后通过sendfile接口进行发送
     *
     * swoole-1.6以上版本不需要$from_id
     *
     * UDP服务器
     * ------------------------------------------------------------------------
     *  * send操作会直接在worker进程内发送数据包，不会再经过主进程转发
     *  * 使用fd保存客户端IP，from_id保存from_fd和port
     *  * 如果在onReceive后立即向客户端发送数据，可以不传$reactor_id
     *  * 如果向其他UDP客户端发送数据，必须要传入$reactor_id
     *  * 在外网服务中发送超过64K的数据会分成多个传输单元进行发送，如果其中一个单元丢包，会导致整个包被丢弃。所以外网服务，建议发送1.5K以下的数据包
     *
     * @param $fd[required]
     * @param $send_data[required]
     * @param $server_socket[optional]
     * @return bool
     */
    public function send($fd, $send_data, $server_socket = null){}

    /**
     * @param $ip[required]
     * @param $port[required]
     * @param $send_data[required]
     * @param $server_socket[optional]
     * @return mixed
     */
    public function sendto($ip, $port, $send_data, $server_socket = null){}

    /**
     * @param $conn_fd[required]
     * @param $send_data[required]
     * @return mixed
     */
    public function sendwait($conn_fd, $send_data){}

    /**
     * @param $fd[required]
     * @return mixed
     */
    public function exist($fd){}

    /**
     * @param $fd[required]
     * @param $is_protected[optional]
     * @return mixed
     */
    public function protect($fd, $is_protected = null){}

    /**
     * @param $conn_fd[required]
     * @param $filename[required]
     * @param $offset[optional]
     * @param $length[optional]
     * @return mixed
     */
    public function sendfile($conn_fd, $filename, $offset = null, $length = null){}

    /**
     * @param $fd[required]
     * @param $reset[optional]
     * @return mixed
     */
    public function close($fd, $reset = null){}

    /**
     * @param $fd[required]
     * @return mixed
     */
    public function confirm($fd){}

    /**
     * @param $fd[required]
     * @return mixed
     */
    public function pause($fd){}

    /**
     * @param $fd[required]
     * @return mixed
     */
    public function resume($fd){}

    /**
     * @param $data[required]
     * @param $worker_id[optional]
     * @param $finish_callback[optional]
     * @return mixed
     */
    public function task($data, $worker_id = null, $finish_callback = null){}

    /**
     * @param $data[required]
     * @param $timeout[optional]
     * @param $worker_id[optional]
     * @return mixed
     */
    public function taskwait($data, $timeout = null, $worker_id = null){}

    /**
     * @param $tasks[required]
     * @param $timeout[optional]
     * @return mixed
     */
    public function taskWaitMulti($tasks, $timeout = null){}

    /**
     * @param $tasks[required]
     * @param $timeout[optional]
     * @return mixed
     */
    public function taskCo($tasks, $timeout = null){}

    /**
     * @param $data[required]
     * @return mixed
     */
    public function finish($data){}

    /**
     * @return mixed
     */
    public function reload(){}

    /**
     * @return mixed
     */
    public function shutdown(){}

    /**
     * @param $worker_id[optional]
     * @return mixed
     */
    public function stop($worker_id = null){}

    /**
     * @return mixed
     */
    public function getLastError(){}

    /**
     * @param $reactor_id[required]
     * @return mixed
     */
    public function heartbeat($reactor_id){}

    /**
     * @param $fd[required]
     * @param $reactor_id[optional]
     * @return mixed
     */
    public function connection_info($fd, $reactor_id = null){}

    /**
     * @param $start_fd[required]
     * @param $find_count[optional]
     * @return mixed
     */
    public function connection_list($start_fd, $find_count = null){}

    /**
     * @param $fd[required]
     * @param $reactor_id[optional]
     * @return mixed
     */
    public function getClientInfo($fd, $reactor_id = null){}

    /**
     * @param $start_fd[required]
     * @param $find_count[optional]
     * @return mixed
     */
    public function getClientList($start_fd, $find_count = null){}

    /**
     * @param $ms[required]
     * @param $callback[required]
     * @param $param[optional]
     * @return mixed
     */
    public function after($ms, $callback, $param = null){}

    /**
     * @param $ms[required]
     * @param $callback[required]
     * @return mixed
     */
    public function tick($ms, $callback){}

    /**
     * @param $timer_id[required]
     * @return mixed
     */
    public function clearTimer($timer_id){}

    /**
     * @param $callback[required]
     * @return mixed
     */
    public function defer($callback){}

    /**
     * @param $message[required]
     * @param $dst_worker_id[required]
     * @return mixed
     */
    public function sendMessage($message, $dst_worker_id){}

    /**
     * @param $process[required]
     * @return mixed
     */
    public function addProcess($process){}

    /**
     * @return mixed
     */
    public function stats(){}

    /**
     * @param $port[optional]
     * @return mixed
     */
    public function getSocket($port = null){}

    /**
     * @param $fd[required]
     * @param $uid[required]
     * @return mixed
     */
    public function bind($fd, $uid){}


}
