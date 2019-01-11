<?php
namespace Swoole\Coroutine;

class PostgreSQL
{

    public $error;

    /**
     * @return mixed
     */
    public function __construct(){}

    /**
     * @param $conninfo[required]
     * @return mixed
     */
    public function connect($conninfo){}

    /**
     * @param $query[optional]
     * @return mixed
     */
    public function query($query = null){}

    /**
     * @param $stmtname[required]
     * @param $query[required]
     * @return mixed
     */
    public function prepare($stmtname, $query){}

    /**
     * @param $stmtname[required]
     * @param $pv_param_arr[required]
     * @return mixed
     */
    public function execute($stmtname, $pv_param_arr){}

    /**
     * @param $result[optional]
     * @param $result_type[optional]
     * @return mixed
     */
    public function fetchAll($result = null, $result_type = null){}

    /**
     * @param $result[optional]
     * @return mixed
     */
    public function affectedRows($result = null){}

    /**
     * @param $result[optional]
     * @return mixed
     */
    public function numRows($result = null){}

    /**
     * @param $table_name[required]
     * @return mixed
     */
    public function metaData($table_name){}

    /**
     * @param $result[required]
     * @param $row[optional]
     * @param $class_name[optional]
     * @param $l[optional]
     * @param $ctor_params[optional]
     * @return mixed
     */
    public function fetchObject($result, $row = null, $class_name = null, $l = null, $ctor_params = null){}

    /**
     * @param $result[required]
     * @param $row[optional]
     * @return mixed
     */
    public function fetchAssoc($result, $row = null){}

    /**
     * @param $result[required]
     * @param $row[optional]
     * @param $result_type[optional]
     * @return mixed
     */
    public function fetchArray($result, $row = null, $result_type = null){}

    /**
     * @param $result[required]
     * @param $row[optional]
     * @param $result_type[optional]
     * @return mixed
     */
    public function fetchRow($result, $row = null, $result_type = null){}

    /**
     * @return mixed
     */
    public function __destruct(){}


}
