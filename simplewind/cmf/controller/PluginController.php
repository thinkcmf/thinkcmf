<?php
namespace cmf\controller;

use think\App;
use think\Loader;

class PluginController extends HomeBaseController
{
    public function index($_plugin, $_controller, $_action)
    {

        $class = "plugins\\{$_plugin}\\{$_plugin}Plugin";
        //$plugin = new $class();

        $_controller = Loader::parseName($_controller,1);

        $pluginControllerClass = "plugins\\{$_plugin}\\controller\\{$_controller}Controller";;

        //$pluginController= new $pluginControllerClass;
       // $pluginController->controllerName = $_controller;

        $vars = ['test'=>'test'];
        return App::invokeMethod([$pluginControllerClass,$_action,$vars]);

//
//        print_r($_plugin);
//        print_r($_controller);
//        print_r($_action);
//        $data = $this->request->param();
//        print_r($data);
//        echo url("\\cmf\\controller\\PluginController@index", ['_plugin' => 'demo','_controller'=>'index','_action'=>'index','id'=>1]);
//        echo url("\\cmf\\controller\\PluginController@index","_plugin=demo&_controller=index");
        //$this->assign("hello", "Hello ThinkCMF Portal!");
        //return $this->fetch(':index');
    }

    public function test()
    {

        $this->assign("hello", "Hello ThinkCMF Portal!");
        return $this->fetch(':test');
    }
}
