<?php
namespace Swoole\Coroutine\Http2;

class Client
{

    public $errCode;
    public $errMsg;
    public $sock;
    public $type;
    public $setting;
    public $connected;
    public $host;
    public $port;

    /**
     * @param $host[required]
     * @param $port[optional]
     * @param $ssl[optional]
     * @return mixed
     */
    public function __construct($host, $port = null, $ssl = null){}

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
     * @return mixed
     */
    public function connect(){}

    /**
     * @param $key[optional]
     * @return mixed
     */
    public function stats($key = null){}

    /**
     * @param $stream_id[required]
     * @return mixed
     */
    public function isStreamExist($stream_id){}

    /**
     * @param $request[required]
     * @return mixed
     */
    public function send($request){}

    /**
     * @param $stream_id[required]
     * @param $data[required]
     * @param $end_stream[optional]
     * @return mixed
     */
    public function write($stream_id, $data, $end_stream = null){}

    /**
     * @param $timeout[optional]
     * @return mixed
     */
    public function recv($timeout = null){}

    /**
     * @param $error_code[optional]
     * @param $debug_data[optional]
     * @return mixed
     */
    public function goaway($error_code = null, $debug_data = null){}

    /**
     * @return mixed
     */
    public function close(){}


}
