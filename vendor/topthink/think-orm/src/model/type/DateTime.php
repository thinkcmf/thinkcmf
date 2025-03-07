<?php

declare (strict_types = 1);

namespace think\model\type;

use think\model\contract\Modelable;
use think\model\contract\Typeable;

class DateTime implements Typeable
{
    protected $data;

    public static function from(mixed $value, Modelable $model)
    {
        $static = new static();
        $static->data($value, 'Y-m-d H:i:s');
        return $static;
    }

    public function data($time, $format)
    {
        $date = new \DateTime;
        $date->setTimestamp(is_numeric($time) ? (int) $time : strtotime($time));
        $this->data = $date->format($format);
    }

    public function value()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->data;
    }
}
