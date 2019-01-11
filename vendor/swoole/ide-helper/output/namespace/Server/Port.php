<?php
namespace Swoole\Server;

class Port
{

    public $onConnect;
    public $onReceive;
    public $onClose;
    public $onPacket;
    public $onBufferFull;
    public $onBufferEmpty;
    public $onRequest;
    public $onHandShake;
    public $onMessage;
    public $onOpen;
    public $host;
    public $port;
    public $type;
    public $sock;
    public $setting;
    public $connections;

    /**
     * @return mixed
     */
    private function __construct(){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $settings[required]
     * @return mixed
     */
    public function set($settings){}

    /**
     * @param $event_name[required]
     * @param $callback[required]
     * @return mixed
     */
    public function on($event_name, $callback){}

    /**
     * @return mixed
     */
    public function getSocket(){}


}
