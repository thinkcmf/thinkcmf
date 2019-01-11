<?php
namespace Swoole\Coroutine\Http;

class Client
{

    public $errCode;
    public $errMsg;
    public $connected;
    public $host;
    public $port;
    public $ssl;
    public $setting;
    public $requestMethod;
    public $requestHeaders;
    public $requestBody;
    public $uploadFiles;
    public $downloadFile;
    public $downloadOffset;
    public $statusCode;
    public $headers;
    public $set_cookie_headers;
    public $cookies;
    public $body;

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
    public function getDefer(){}

    /**
     * @param $defer[optional]
     * @return mixed
     */
    public function setDefer($defer = null){}

    /**
     * @param $method[required]
     * @return mixed
     */
    public function setMethod($method){}

    /**
     * @param $headers[required]
     * @return mixed
     */
    public function setHeaders($headers){}

    /**
     * @param $cookies[required]
     * @return mixed
     */
    public function setCookies($cookies){}

    /**
     * @param $data[required]
     * @return mixed
     */
    public function setData($data){}

    /**
     * @param $path[required]
     * @return mixed
     */
    public function execute($path){}

    /**
     * @param $path[required]
     * @return mixed
     */
    public function get($path){}

    /**
     * @param $path[required]
     * @param $data[required]
     * @return mixed
     */
    public function post($path, $data){}

    /**
     * @param $path[required]
     * @param $file[required]
     * @param $offset[optional]
     * @return mixed
     */
    public function download($path, $file, $offset = null){}

    /**
     * @param $path[required]
     * @return mixed
     */
    public function upgrade($path){}

    /**
     * @param $path[required]
     * @param $name[required]
     * @param $type[optional]
     * @param $filename[optional]
     * @param $offset[optional]
     * @param $length[optional]
     * @return mixed
     */
    public function addFile($path, $name, $type = null, $filename = null, $offset = null, $length = null){}

    /**
     * @param $path[required]
     * @param $name[required]
     * @param $type[optional]
     * @param $filename[optional]
     * @return mixed
     */
    public function addData($path, $name, $type = null, $filename = null){}

    /**
     * @param $timeout[optional]
     * @return mixed
     */
    public function recv($timeout = null){}

    /**
     * @param $data[required]
     * @param $opcode[optional]
     * @param $finish[optional]
     * @return mixed
     */
    public function push($data, $opcode = null, $finish = null){}

    /**
     * @return mixed
     */
    public function close(){}


}
