<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: catman <catman@thinkcmf.com>
// +---------------------------------------------------------------------
// ThinkPHP5.0兼容ThinkPHP5.1代码,用法请看ThinkPHP5.1文档
namespace think\facade;

use think\Validate as ThinValidate;

/**
 * Class Validate
 * @package think\facade
 * @method bool is($value, $rule, $data = []) static 验证字段值是否为有效格式
 * @method bool isDate($value) static 验证是否为有效的日期
 * @method bool isEmail($value) static 验证是否为有效邮箱地址
 */
class Validate extends ThinValidate
{
    public static function __callStatic($method, $params)
    {
        $class = self::make();
        if (method_exists($class, $method)) {
            return call_user_func_array([$class, $method], $params);
        } else if ('is' == strtolower(substr($method, 0, 2))) {
            $method = substr($method, 2);
            array_push($params, lcfirst($method));

            return call_user_func_array([$class, 'is'], $params);
        } else {
            throw new \BadMethodCallException('method not exists:' . __CLASS__ . '->' . $method);
        }

    }


}