<?php
namespace Swoole\Process;

class Pool
{


    /**
     * @param $worker_num[required]
     * @param $ipc_type[optional]
     * @param $msgqueue_key[optional]
     * @return mixed
     */
    public function __construct($worker_num, $ipc_type = null, $msgqueue_key = null){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $event_name[required]
     * @param $callback[required]
     * @return mixed
     */
    public function on($event_name, $callback){}

    /**
     * @return mixed
     */
    public function getProcess(){}

    /**
     * @param $host[required]
     * @param $port[optional]
     * @param $backlog[optional]
     * @return mixed
     */
    public function listen($host, $port = null, $backlog = null){}

    /**
     * @param $data[required]
     * @return mixed
     */
    public function write($data){}

    /**
     * @return mixed
     */
    public function start(){}


}
