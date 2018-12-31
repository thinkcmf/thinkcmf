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

use think\Env as ThinkEnv;

class Env
{
    public static function get($name, $default = null)
    {
        switch ($name) {
            case "runtime_path":
                $value = RUNTIME_PATH;
                break;
            case "root_path":
                $value = ROOT_PATH;
                break;
            default:
                $value = ThinkEnv::get($name, $default);
        }

        return $value;
    }

}