<?php
namespace Swoole\Memory\Pool;

class Slice
{


    /**
     * @param $size[optional]
     * @param $offset[optional]
     * @return mixed
     */
    public function read($size = null, $offset = null){}

    /**
     * @param $data[required]
     * @param $offset[optional]
     * @return mixed
     */
    public function write($data, $offset = null){}

    /**
     * @return mixed
     */
    public function __destruct(){}


}
