<?php

use think\Container;

if (!function_exists('app')) {
    /**
     * 快速获取容器中的实例 支持依赖注入
     * @param string $name 类名或标识 默认获取当前应用实例
     * @param array $args 参数
     * @param bool $newInstance 是否每次创建新的实例
     * @return object
     */
    function app($name = 'cmf\think\App', $args = [], $newInstance = false)
    {
        return Container::get($name, $args, $newInstance);
    }
}