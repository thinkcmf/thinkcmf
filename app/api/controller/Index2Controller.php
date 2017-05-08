<?php
namespace app\api\controller;

use think\Controller;
use think\Request;
use think\config;

/**
 * 带参数的方法
 * 注意配置
 * // 应用类库后缀
 * 'class_suffix'           => true,
 * // 控制器类后缀
 * 'controller_suffix'      => true,
 * Class Index2Controller
 * @package app\api\controller
 */
class Index2Controller extends Controller
{
//    public function index()
//    {
////        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
//        return 'Hello,World！';
//    }

    public function index($name = 'World!')
    {
        return 'Hello,' . $name . '！';
//        $request = Request::instance();
//        echo "当前模块名称是" . $request->module();
//        echo "当前控制器名称是" . $request->controller();
//        echo "当前操作名称是" . $request->action();
    }

    public function hello($name = 'thinkphp')
    {
        $this->assign('name', $name);  // 继承 thinkphp\Controller assign
        return $this->fetch();

//        return 'Hello,' . $name . '！';

    }

    public function testRequest2()
    {
        $info = Request::instance()->header();
        echo 'accept:' . $info['accept'] . '<br/>';
        echo 'accept-encoding:' . $info['accept-encoding'] . '<br/>';
        echo 'user-agent:' . $info['user-agent'] . '<br/>';

// 是否为 GET 请求
        if (Request::instance()->isGet()) echo "当前为 GET 请求";
// 是否为 POST 请求
        if (Request::instance()->isPost()) echo "当前为 POST 请求";
// 是否为 PUT 请求
        if (Request::instance()->isPut()) echo "当前为 PUT 请求";
// 是否为 DELETE 请求
        if (Request::instance()->isDelete()) echo "当前为 DELETE 请求";
// 是否为 Ajax 请求
        if (Request::instance()->isAjax()) echo "当前为 Ajax 请求";
// 是否为 Pjax 请求
        if (Request::instance()->isPjax()) echo "当前为 Pjax 请求";
// 是否为手机访问
        if (Request::instance()->isMobile()) echo "当前为手机访问";
// 是否为 HEAD 请求
        if (Request::instance()->isHead()) echo "当前为 HEAD 请求";
// 是否为 Patch 请求
        if (Request::instance()->isPatch()) echo "当前为 PATCH 请求";
// 是否为 OPTIONS 请求
        if (Request::instance()->isOptions()) echo "当前为 OPTIONS 请求";
// 是否为 cli
        if (Request::instance()->isCli()) echo "当前为 cli";
// 是否为 cgi
        if (Request::instance()->isCgi()) echo "当前为 cgi";


        $request = request();   // 助手函数

// 更改GET变量
//        $request->get(['id' => 10]);
// 更改POST变量
        $request->post(['name' => 'thinkphp']);

//        $request->param(['id'=>10]);

//        dump($request->param());

    }

    /**
     * 请求信息 Request
     */
    public function testRequest()
    {
//        $request = Request::instance();  // \think\Request
        $request = request();   // 助手函数

//        $request->root('index.php'); // 手动设置请求消息
//        $request->pathinfo('index/index/retjson');

        echo '路由信息：';
        dump($request->route());
        echo '调度信息：';
        dump($request->dispatch());

// 获取当前域名
        echo 'domain: ' . $request->domain() . '<br/>';
// 获取当前入口文件
        echo 'file: ' . $request->baseFile() . '<br/>';
// 获取当前URL地址 不含域名
        echo 'url: ' . $request->url() . '<br/>';
// 获取包含域名的完整URL地址
        echo 'url with domain: ' . $request->url(true) . '<br/>';
// 获取当前URL地址 不含QUERY_STRING
        echo 'url without query: ' . $request->baseUrl() . '<br/>';
// 获取URL访问的ROOT地址
        echo 'root:' . $request->root() . '<br/>';
// 获取URL访问的ROOT地址
        echo 'root with domain: ' . $request->root(true) . '<br/>';
// 获取URL地址中的PATH_INFO信息
        echo 'pathinfo: ' . $request->pathinfo() . '<br/>';
// 获取URL地址中的PATH_INFO信息 不含后缀
        echo 'pathinfo: ' . $request->path() . '<br/>';
// 获取URL地址中的后缀信息
        echo 'ext: ' . $request->ext() . '<br/>' . '<br/>';

        echo "当前模块名称是" . $request->module() . '<br/>';
        echo "当前控制器名称是" . $request->controller() . '<br/>';
        echo "当前操作名称是" . $request->action() . '<br/>' . '<br/>';

        echo '请求方法：' . $request->method() . '<br/>';
        echo '资源类型：' . $request->type() . '<br/>';
        echo '访问地址：' . $request->ip() . '<br/>';
        echo '是否AJax请求：' . var_export($request->isAjax(), true) . '<br/>';
        echo '请求参数：';
        dump($request->param());
        echo '请求参数：仅包含name';
        dump($request->only(['name']));
        echo '请求参数：排除name';
        dump($request->except(['name']));

    }


//系统默认的规则（视图目录/控制器/操作方法）输出了

    public function testrequest3()
    {
        return $this->fetch();
    }

    public function tConfig()
    {
        echo config('url_html_suffix') . '<br/>' ; // 助手函数
        echo(Config::get('database.password')). '<br/>';
        echo(Config::get('database.database')). '<br/>'. '<br/>';

        print_r(Config::get('database'));
        echo '<br/>' . '<br/>';
        var_dump(Config::get('database')) . '<br/>' . '<br/>';

//        echo Config::get('url_html_suffix');
    }
}
