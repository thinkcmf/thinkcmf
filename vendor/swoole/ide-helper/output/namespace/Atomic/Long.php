<?php
namespace Swoole\Atomic;

class Long
{


    /**
     * @param $value[optional]
     * @return mixed
     */
    public function __construct($value = null){}

    /**
     * @param $add_value[optional]
     * @return mixed
     */
    public function add($add_value = null){}

    /**
     * @param $sub_value[optional]
     * @return mixed
     */
    public function sub($sub_value = null){}

    /**
     * @return mixed
     */
    public function get(){}

    /**
     * @param $value[required]
     * @return mixed
     */
    public function set($value){}

    /**
     * @param $cmp_value[required]
     * @param $new_value[required]
     * @return mixed
     */
    public function cmpset($cmp_value, $new_value){}


}
