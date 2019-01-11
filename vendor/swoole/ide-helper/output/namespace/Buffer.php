<?php
namespace Swoole;

class Buffer
{


    /**
     * @param $size[optional]
     * @return mixed
     */
    public function __construct($size = null){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @return mixed
     */
    public function __toString(){}

    /**
     * @param $offset[required]
     * @param $length[optional]
     * @param $seek[optional]
     * @return mixed
     */
    public function substr($offset, $length = null, $seek = null){}

    /**
     * @param $offset[required]
     * @param $data[required]
     * @return mixed
     */
    public function write($offset, $data){}

    /**
     * @param $offset[required]
     * @param $length[required]
     * @return mixed
     */
    public function read($offset, $length){}

    /**
     * @param $data[required]
     * @return mixed
     */
    public function append($data){}

    /**
     * @param $size[required]
     * @return mixed
     */
    public function expand($size){}

    /**
     * @return mixed
     */
    public function recycle(){}

    /**
     * @return mixed
     */
    public function clear(){}


}
