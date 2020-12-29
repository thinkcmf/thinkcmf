<?php

class Facade
{

    /**
     * 声明此方法用来处理调用对象中不存在的方法
     */

    public static function __callStatic($funName, $arguments)
    {
        $class = static::class;

        $facadeClass = static::getFacadeClass();

        if ($facadeClass) {
            $class = $facadeClass;
        }

        return call_user_func_array([$class, $funName], $arguments);
    }

}

class ManFacade extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'man';
    }
}

class Man
{
    public function say()
    {
        echo "Hello, world!";
    }

    public function eat()
    {
        echo "Hello, eat!";
    }
}


echo ManFacade::say("teacher"); // 调用对象中不存在的方法，则自动调用了对象中的__call()方法

echo ManFacade::eat("小明", "苹果");

