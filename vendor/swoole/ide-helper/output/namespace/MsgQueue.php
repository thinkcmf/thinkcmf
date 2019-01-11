<?php
namespace Swoole;

class MsgQueue
{


    /**
     * @param $len[required]
     * @return mixed
     */
    public function __construct($len){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $data[required]
     * @param $type[optional]
     * @return mixed
     */
    public function push($data, $type = null){}

    /**
     * @param $type[optional]
     * @return mixed
     */
    public function pop($type = null){}

    /**
     * @param $blocking[required]
     * @return mixed
     */
    public function setBlocking($blocking){}

    /**
     * @return mixed
     */
    public function stats(){}

    /**
     * @return mixed
     */
    public function destroy(){}


}
