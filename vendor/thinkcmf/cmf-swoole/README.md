ThinkPHP 5.1 Swoole 扩展
===============

## 安装

首先按照Swoole官网说明安装swoole扩展，然后使用
~~~
composer require topthink/think-swoole
~~~
安装swoole扩展。

## 使用方法

### HttpServer

直接在命令行下启动服务端。

~~~
php think swoole
~~~

启动完成后，会在0.0.0.0:9501启动一个HTTP Server，可以直接访问当前的应用。

swoole的参数可以在应用配置目录下的swoole.php里面配置（具体参考配置文件内容）。

如果需要使用守护进程方式运行，可以使用
~~~
php think swoole -d
~~~
或者在swoole.php文件中设置
~~~
'daemonize'	=>	true
~~~

注意：由于onWorkerStart运行的时候没有HTTP_HOST，因此最好在应用配置文件中设置app_host

支持的操作包括
~~~
php think swoole [start|stop|reload|restart]
~~~

### Server

可以支持直接启动一个Swoole server

~~~
php think swoole:server
~~~
会在0.0.0.0:9508启动一个Websocket服务。

如果需要自定义参数，可以在config/swoole_server.php中进行配置，包括：

配置参数 | 描述
--- | ---
type| 服务类型
host | 监听地址
port | 监听端口
mode | 运行模式
sock_type | Socket type


并且支持swoole所有的参数。
也支持使用闭包方式定义相关事件回调。

~~~
return [
    // 扩展自身配置
    'host'         => '0.0.0.0', // 监听地址
    'port'         => 9501, // 监听端口
    'type'         => 'socket', // 服务类型 支持 socket http server
    'mode'         => SWOOLE_PROCESS,
    'sock_type'    => SWOOLE_SOCK_TCP,

    // 可以支持swoole的所有配置参数
    'daemonize'    => false,

    // 事件回调定义
    'onOpen'       => function ($server, $request) {
        echo "server: handshake success with fd{$request->fd}\n";
    },

    'onMessage'    => function ($server, $frame) {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd, "this is server");
    },

    'onRequest'    => function ($request, $response) {
        $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
    },

    'onClose'      => function ($ser, $fd) {
        echo "client {$fd} closed\n";
    },
];
~~~

也可以使用自定义的服务类

~~~
<?php
namespace app\http;

use think\swoole\Server;

class Swoole extends Server
{
	protected $host = '127.0.0.1';
	protected $port = 9502;
    protected $serverType = 'socket';
	protected $option = [ 
		'worker_num'=> 4,
		'daemonize'	=> true,
		'backlog'	=> 128
	];

	public function onReceive($server, $fd, $from_id, $data)
	{
		$server->send($fd, 'Swoole: '.$data);
	}
}
~~~

支持swoole所有的回调方法定义（回调方法必须是public类型）
serverType 属性定义为 socket或者http 则支持swoole的swoole_websocket_server和swoole_http_server

然后在swoole_server.php中增加配置参数：
~~~
return [
	'swoole_class'	=>	'app\http\Swoole',
];
~~~

定义该参数后，其它配置参数均不再有效。

在命令行启动服务端
~~~
php think swoole:server
~~~


支持reload|restart|stop|status 操作
~~~
php think swoole:server reload
~~~


### 配置信息详解

swoole.php

```php
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Env;
// +----------------------------------------------------------------------
// | Swoole设置 php think swoole命令行下有效
// +----------------------------------------------------------------------
return [
    // 扩展自身配置
    'host'                  => '0.0.0.0', // 监听地址
    'port'                  => 9501, // 监听端口
    'mode'                  => '', // 运行模式 默认为SWOOLE_PROCESS
    'sock_type'             => '', // sock type 默认为SWOOLE_SOCK_TCP
    'app_path'              => '', // 应用地址 如果开启了 'daemonize'=>true 必须设置（使用绝对路径）
    'file_monitor'          => false, // 是否开启PHP文件更改监控（调试模式下自动开启）
    'file_monitor_interval' => 2, // 文件变化监控检测时间间隔（秒）
    'file_monitor_path'     => [], // 文件监控目录 默认监控application和config目录
    // 可以支持swoole的所有配置参数
    'pid_file'              => Env::get('runtime_path') . 'swoole.pid',//swoole主进程pid存放文件
    'log_file'              => Env::get('runtime_path') . 'swoole.log',//swoole日志存放文件
    'document_root'         => Env::get('root_path') . 'public',//设置静态服务根目录
    'enable_static_handler' => true,//是否由SWOOLE底层自动处理静态文件，TRUE表示SWOOLE判断是否存在静态文件，如果存在则直接返回静态文件信息
    'timer'                 => true,//是否开启系统定时器
    'interval'              => 500,//系统定时器 时间间隔
    'task_worker_num'       => 1,//swoole 任务工作进程数量
    'user'                  =>'www',//表示swoole worker进程所属的管理员名称，如果要绑定1024以下端口则必须要求具有root权限，如果设置了该项，则除主进程外的所有进程都运行于指定用户下
];
```

timer.php

```php
<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/15
 * Time: 下午2:14
 * 秒 分 时 日 月 星期几
 * crontab 格式 * *  *  *  * *    => "类"
 * *中间一个空格
 * 系统定时任务需要在swoole.php中开启
 * 自定义定时器不受其影响
 */
return [
    '*/5 * * * * *' => '\\app\\lib\\Timer',//时间配置方式参考crontab，主要是增加秒，其他和crontab一致，对应Value为定时器接口实现类的完整命名空间（包含类名）
];
```

### 异步任务投递

1.异步任务接口实现

```php
<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/19
 * Time: 下午8:28
 */

namespace app\lib;

use think\swoole\template\Task;
class TestTask extends Task
{
    public function _initialize(...$arg)
    {
        // TODO: Implement _initialize() method.
    }

    public function run($serv, $task_id, $fromWorkerId)
    {
        // TODO: Implement run() method.
    }
}
```

2.异步任务投递在控制器中的使用

```php
<?php
namespace app\index\controller;
use think\swoole\facade\Task;

class Index
{
    public function hello()
    {
        //投递任务模板
        $task=new \app\lib\TestTask();
        Task::async($task);
        //异步任务投递闭包
        Task::async(function ($serv, $task_id, $data) {
            $i = 0;
            while ($i < 10) {
                $i++;
                echo $i;
                sleep(1);
            }
        });

        return 'hello' ;
    }
}
```

### 定时器的使用

定时器分为系统定时器和自定义定时器，系统定时器需要在配置文件（timer.php）中进行配置，会根据配置由系统自动处理

1. 定时器接口的实现

```php
<?php
namespace app\lib;
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/19
 * Time: 下午8:01
 */
use think\swoole\template\Timer;
class TestTimer extends Timer
{

    public function _initialize(...$arg)
    {
        // TODO: Implement _initialize() method.
    }

    public function run()
    {
        $i=0;
        // TODO: Implement run() method.
        while($i<10){
            var_dump(12);
            $i++;
            sleep(1);
        }
    }
}
```

2. 系统定时器的使用

参考上面timer.php的配置方法，系统定时器任务会自动进行异步投递，因此必须在swoole.php中配置task_worker_num，系统会自动调用非繁忙的task worker进行任务处理

3. 自定义定时器

自定义定时器可以执行闭包和Timer接口实现类。注意，如非必要请勿在控制器等重复调用的地方使用tick方法，因为每次请求都会创建新的定时器。如果必须创建，请注意定时器回收。

```php
<?php
namespace app\index\controller;
use think\swoole\facade\Task;
use think\swoole\facade\Timer;
class Index
{
    public function hello()
    {
        //闭包方式使用定时器
        Timer::tick(1000,function(){
            var_dump(1);
        });
        //使用定时器模板
        $t=new \app\lib\TestTimer();
        Timer::tick(1000,$t);
        return 'hello,' ;
    }
}
```

### 获取Server

获取Swoole实例对象

```php
<?php
namespace app\index\controller;
use think\Container;
class Index
{
    public function hello()
    {
        $swoole=Container::get('swoole');//获取swoole实例
    }
}
```


### 自定义服务启动

如果需要在Swoole启动的时候创建一些服务，可以按照如下方法进行自定义

在swoole.php中配置配置

//闭包方式
```php
'wokerstart'=>function($server, $worker_id){
    //如果只在一个进程处理 则可以这样

    if (0==$worker_id){
        //这样只会在第一个woker进程处理
    }
}
```

如果需要实现Workerstart的接口，可以这样实现

```php
<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/23
 * Time: 下午5:27
 * Email:499873958@qq.com
 */

namespace app\lib;

use think\swoole\template\WorkerStart as Woker;
class WorkStart extends Woker
{
    public function _initialize($server, $worker_id)
    {
        // TODO: Implement _initialize() method.
    }

    public function run()
    {
        var_dump(1);
    }
}
```

配置文件

```php
'wokerstart'=>'\\app\\lib\\WorkStart'
```

### Websocket支持  
需要在swoole.php修改

```a
'server_type'           => 'websocket', // 服务类型 支持 http websocket
```

由于Swoole\WebSocket\Server继承于Swoole\Http\Server顾Http服务具有的功能Websocket也具有。如果采用如下websocket服务，
通讯数据格式已经固定，如采用自定义数据结构，请采用自定义服务

WebSocket通讯数据结构
```json
{
    "event":"",//事件名称
    "url":"",//目标地址
    "arguments":{//客户端投递数据
        "post":[],//post数据
        "get":[],//get数据
        "cookie":[],//cookie数据
   }
}
```
详解 

* event  该参数为事件名称，用于触发客户端事件，即接收服务器推送数据处理指定任务

* url，该为访问的目标地址，相当于http模式下http://xxx.com/url

* arguments 客户端投递的所有数据，如果希望可以按照原来POST,GET的方式处理数据，可以提交时按照上述方式提交，会自动处理

客户端JS可以参考example里websocketclient.js


服务端发送数据

```php
use think\swoole\WebSocketFrame;

$client=WebSocketFrame::getInstance();
//发送数据给当前请求的客户端
$client->pushToClient([]);//参数为数组，字符串，数字
//发送给所有客户端
$client->pushToClients([]);//参数为数组，字符串，数字

$client->getArgs();//获取arguments里的参数

$client->getData();//获取客户端发送给的所有数据

$client->getServer();//获取当前server

$client->getFrame();//获取当前客户端给发送的原始数据
```


新增队列支持

think-queue，是一个非常好用的队列服务，xavier-swoole的队列服务依赖于think-queue，使用方法和think-queue保持一致

主要增加如下功能

1. 采用多进程模式运行或采用Task异步执行
2. 可以指定同时消耗队列任务的进程数量
3. Task模式和Server共享Task进程，充分利用资源
4. Process模式采用进程模式，各个任务之间相互隔离，执行一定次数后，重启进程，防止内存泄露


如果任务较多且复杂，推荐采用Process模式

```php 
'queue_type'=>'task',//task or process
    'queue'=>[
        "Index"=>[
                      "delay"=>0,//延迟时间
                      "sleep"=>3,//休息时间
                      "maxTries"=>0,//重试次数
                      "nums"=>2//进程数量
        ],
    ]
```

