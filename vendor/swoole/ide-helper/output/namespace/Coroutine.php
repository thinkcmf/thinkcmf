<?php
namespace Swoole;

class Coroutine
{


    /**
     * @param $func[required]
     * @param $params[optional]
     * @return mixed
     */
    public static function create($func, $params = null){}

    /**
     * @param $command[required]
     * @return mixed
     */
    public static function exec($command){}

    /**
     * @param $domain_name[required]
     * @param $family[optional]
     * @param $timeout[optional]
     * @return mixed
     */
    public static function gethostbyname($domain_name, $family = null, $timeout = null){}

    /**
     * @param $callback[required]
     * @return mixed
     */
    public static function defer($callback){}

    /**
     * @param $options[required]
     * @return mixed
     */
    public static function set($options){}

    /**
     * @return mixed
     */
    public static function yield(){}

    /**
     * @return mixed
     */
    public static function suspend(){}

    /**
     * @param $uid[required]
     * @return mixed
     */
    public static function resume($uid){}

    /**
     * @return mixed
     */
    public static function stats(){}

    /**
     * @return mixed
     */
    public static function getCid(){}

    /**
     * @return mixed
     */
    public static function getuid(){}

    /**
     * @param $seconds[required]
     * @return mixed
     */
    public static function sleep($seconds){}

    /**
     * @param $handle[required]
     * @param $length[optional]
     * @return mixed
     */
    public static function fread($handle, $length = null){}

    /**
     * @param $handle[required]
     * @return mixed
     */
    public static function fgets($handle){}

    /**
     * @param $handle[required]
     * @param $string[required]
     * @param $length[optional]
     * @return mixed
     */
    public static function fwrite($handle, $string, $length = null){}

    /**
     * @param $filename[required]
     * @return mixed
     */
    public static function readFile($filename){}

    /**
     * @param $filename[required]
     * @param $data[required]
     * @param $flags[optional]
     * @return mixed
     */
    public static function writeFile($filename, $data, $flags = null){}

    /**
     * @param $hostname[required]
     * @param $family[optional]
     * @param $socktype[optional]
     * @param $protocol[optional]
     * @param $service[optional]
     * @return mixed
     */
    public static function getaddrinfo($hostname, $family = null, $socktype = null, $protocol = null, $service = null){}

    /**
     * @param $path[required]
     * @return mixed
     */
    public static function statvfs($path){}

    /**
     * @param $cid[required]
     * @param $options[optional]
     * @param $limit[optional]
     * @return mixed
     */
    public static function getBackTrace($cid, $options = null, $limit = null){}

    /**
     * @return mixed
     */
    public static function listCoroutines(){}


}
