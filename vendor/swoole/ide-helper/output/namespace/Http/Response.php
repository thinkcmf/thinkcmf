<?php
namespace Swoole\Http;

class Response
{

    public $fd;
    public $header;
    public $cookie;
    public $trailer;

    /**
     * @return mixed
     */
    public function initHeader(){}

    /**
     * @param $name[required]
     * @param $value[optional]
     * @param $expires[optional]
     * @param $path[optional]
     * @param $domain[optional]
     * @param $secure[optional]
     * @param $httponly[optional]
     * @return mixed
     */
    public function cookie($name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null){}

    /**
     * @param $name[required]
     * @param $value[optional]
     * @param $expires[optional]
     * @param $path[optional]
     * @param $domain[optional]
     * @param $secure[optional]
     * @param $httponly[optional]
     * @return mixed
     */
    public function rawcookie($name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null){}

    /**
     * @param $http_code[required]
     * @param $reason[optional]
     * @return mixed
     */
    public function status($http_code, $reason = null){}

    /**
     * @param $compress_level[optional]
     * @return mixed
     */
    public function gzip($compress_level = null){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @param $ucwords[optional]
     * @return mixed
     */
    public function header($key, $value, $ucwords = null){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @param $ucwords[optional]
     * @return mixed
     */
    public function trailer($key, $value, $ucwords = null){}

    /**
     * @param $content[required]
     * @return mixed
     */
    public function write($content){}

    /**
     * @param $content[optional]
     * @return mixed
     */
    public function end($content = null){}

    /**
     * @param $filename[required]
     * @param $offset[optional]
     * @param $length[optional]
     * @return mixed
     */
    public function sendfile($filename, $offset = null, $length = null){}

    /**
     * @param $location[required]
     * @param $http_code[optional]
     * @return mixed
     */
    public function redirect($location, $http_code = null){}

    /**
     * @return mixed
     */
    public function detach(){}

    /**
     * @param $fd[required]
     * @return mixed
     */
    public static function create($fd){}

    /**
     * @return mixed
     */
    public function __destruct(){}


}
