<?php
namespace Swoole;

class MySQL
{
    const STATE_QUERY = 0;
    const STATE_READ_START = 1;
    const STATE_READ_FIELD  = 2;
    const STATE_READ_ROW = 3;
    const STATE_READ_END = 5;
    const STATE_CLOSED = 6;

    public $serverInfo;
    public $sock;
    public $connected;
    public $errno;
    public $connect_errno;
    public $error;
    public $connect_error;
    public $insert_id;
    public $affected_rows;
    public $onConnect;
    public $onClose;

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
     * @param $callback[required]
     * @return mixed
     */
    public function connect($server_config, $callback){}

    /**
     * @param $callback[required]
     * @return mixed
     */
    public function begin($callback){}

    /**
     * @param $callback[required]
     * @return mixed
     */
    public function commit($callback){}

    /**
     * @param $callback[required]
     * @return mixed
     */
    public function rollback($callback){}

    /**
     * @param $string[required]
     * @param $flags[optional]
     * @return mixed
     */
    public function escape($string, $flags = null){}

    /**
     * @param $sql[required]
     * @param $callback[required]
     * @return mixed
     */
    public function query($sql, $callback){}

    /**
     * @return mixed
     */
    public function close(){}

    /**
     * @return mixed
     */
    public function getState(){}

    /**
     * @param $event_name[required]
     * @param $callback[required]
     * @return mixed
     */
    public function on($event_name, $callback){}


}
