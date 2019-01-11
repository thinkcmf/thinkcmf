<?php
namespace Swoole\Memory;

class Pool
{
    const TYPE_RING = 1;
    const TYPE_GLOBAL = 2;
    const TYPE_FIXED = 0;
    const TYPE_MALLOC = 3;
    const TYPE_EMALLOC = 4;


    /**
     * @param $size[required]
     * @param $type[required]
     * @param $slice_size[optional]
     * @param $shared[optional]
     * @return mixed
     */
    public function __construct($size, $type, $slice_size = null, $shared = null){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $size[optional]
     * @return mixed
     */
    public function alloc($size = null){}


}
