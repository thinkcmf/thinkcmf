<?php
namespace Swoole;

class Channel
{


    /**
     * @param $size[required]
     * @return mixed
     */
    public function __construct($size){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $data[required]
     * @return mixed
     */
    public function push($data){}

    /**
     * @return mixed
     */
    public function pop(){}

    /**
     * @return mixed
     */
    public function peek(){}

    /**
     * @return mixed
     */
    public function stats(){}


}
