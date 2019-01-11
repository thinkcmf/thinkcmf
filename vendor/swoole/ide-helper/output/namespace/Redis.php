<?php
namespace Swoole;

class Redis
{
    const STATE_CONNECT = 0;
    const STATE_READY = 1;
    const STATE_WAIT_RESULT = 2;
    const STATE_SUBSCRIBE = 3;
    const STATE_CLOSED = 4;

    public $onConnect;
    public $onClose;
    public $onMessage;
    public $setting;
    public $host;
    public $port;
    public $sock;
    public $errCode;
    public $errMsg;

    /**
     * @param $setting[optional]
     * @return mixed
     */
    public function __construct($setting = null){}

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
     * @param $host[required]
     * @param $port[required]
     * @param $callback[required]
     * @return mixed
     */
    public function connect($host, $port, $callback){}

    /**
     * @return mixed
     */
    public function close(){}

    /**
     * @return mixed
     */
    public function getState(){}

    /**
     * @param $command[required]
     * @param $params[required]
     * @return mixed
     */
    public function __call($command, $params){}


}
