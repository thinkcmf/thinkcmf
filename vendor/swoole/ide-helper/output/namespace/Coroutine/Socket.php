<?php
namespace Swoole\Coroutine;

class Socket
{

    public $errCode;

    /**
     * @param $domain[required]
     * @param $type[required]
     * @param $protocol[optional]
     * @return mixed
     */
    public function __construct($domain, $type, $protocol = null){}

    /**
     * @param $address[required]
     * @param $port[optional]
     * @return mixed
     */
    public function bind($address, $port = null){}

    /**
     * @param $backlog[optional]
     * @return mixed
     */
    public function listen($backlog = null){}

    /**
     * @param $timeout[optional]
     * @return mixed
     */
    public function accept($timeout = null){}

    /**
     * @param $host[required]
     * @param $port[optional]
     * @param $timeout[optional]
     * @return mixed
     */
    public function connect($host, $port = null, $timeout = null){}

    /**
     * @param $length[optional]
     * @param $timeout[optional]
     * @return mixed
     */
    public function recv($length = null, $timeout = null){}

    /**
     * @param $data[required]
     * @param $timeout[optional]
     * @return mixed
     */
    public function send($data, $timeout = null){}

    /**
     * @param $peername[required]
     * @param $timeout[optional]
     * @return mixed
     */
    public function recvfrom($peername, $timeout = null){}

    /**
     * @param $addr[required]
     * @param $port[required]
     * @param $data[required]
     * @return mixed
     */
    public function sendto($addr, $port, $data){}

    /**
     * @return mixed
     */
    public function getpeername(){}

    /**
     * @return mixed
     */
    public function getsockname(){}

    /**
     * @return mixed
     */
    public function getSocket(){}

    /**
     * @return mixed
     */
    public function close(){}


}
