<?php
namespace Swoole\Coroutine;

class MySQL
{

    public $serverInfo;
    public $sock;
    public $connected;
    public $connect_error;
    public $connect_errno;
    public $affected_rows;
    public $insert_id;
    public $error;
    public $errno;

    /**
     * @return mixed
     */
    public function __construct(){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $server_config[required]
     * @return mixed
     */
    public function connect($server_config){}

    /**
     * @param $sql[required]
     * @param $timeout[optional]
     * @return mixed
     */
    public function query($sql, $timeout = null){}

    /**
     * @return mixed
     */
    public function recv(){}

    /**
     * @return mixed
     */
    public function nextResult(){}

    /**
     * @param $string[required]
     * @param $flags[optional]
     * @return mixed
     */
    public function escape($string, $flags = null){}

    /**
     * @param $timeout[optional]
     * @return mixed
     */
    public function begin($timeout = null){}

    /**
     * @param $timeout[optional]
     * @return mixed
     */
    public function commit($timeout = null){}

    /**
     * @param $timeout[optional]
     * @return mixed
     */
    public function rollback($timeout = null){}

    /**
     * @param $query[required]
     * @param $timeout[optional]
     * @return mixed
     */
    public function prepare($query, $timeout = null){}

    /**
     * @param $defer[optional]
     * @return mixed
     */
    public function setDefer($defer = null){}

    /**
     * @return mixed
     */
    public function getDefer(){}

    /**
     * @return mixed
     */
    public function close(){}


}
