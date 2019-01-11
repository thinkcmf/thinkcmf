<?php
namespace Swoole;

class Async
{


    /**
     * @param $filename[required]
     * @param $callback[required]
     * @param $chunk_size[optional]
     * @param $offset[optional]
     * @return mixed
     */
    public static function read($filename, $callback, $chunk_size = null, $offset = null){}

    /**
     * @param $filename[required]
     * @param $content[required]
     * @param $offset[optional]
     * @param $callback[optional]
     * @return mixed
     */
    public static function write($filename, $content, $offset = null, $callback = null){}

    /**
     * @param $filename[required]
     * @param $callback[required]
     * @return mixed
     */
    public static function readFile($filename, $callback){}

    /**
     * @param $filename[required]
     * @param $content[required]
     * @param $callback[optional]
     * @param $flags[optional]
     * @return mixed
     */
    public static function writeFile($filename, $content, $callback = null, $flags = null){}

    /**
     * @param $hostname[required]
     * @param $callback[required]
     * @return mixed
     */
    public static function dnsLookup($hostname, $callback){}

    /**
     * @param $domain_name[required]
     * @param $timeout[optional]
     * @return mixed
     */
    public static function dnsLookupCoro($domain_name, $timeout = null){}

    /**
     * @param $settings[required]
     * @return mixed
     */
    public static function set($settings){}

    /**
     * @param $command[required]
     * @param $callback[required]
     * @return mixed
     */
    public static function exec($command, $callback){}


}
