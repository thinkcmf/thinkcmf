<?php
namespace app\api\controller;

use think\Controller;
use think\Db;   // 调用数据库

use app\api\model\UserModel;

class TestController extends Controller
//namespace app\api\controller;
//
//use think\Controller;
//use think\Db;
//
//class TestController extends Controller
{
    /**
     * 默认返回
     * @return array
     */
    public function index()
    {
//        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
        $data = ['name'=>'thinkphp','url'=>'thinkphp.cn'];
        return ['data'=>$data,'code'=>1,'message'=>'操作完成'];
    }

    /**
     * 返回JSON
     * @return \think\response\Json
     */
    public function retjson()
    {
        $data = ['name'=>'thinkphp','url'=>'thinkphp.cn'];
        return json(['data'=>$data,'code'=>1,'message'=>'操作完成']);
    }

    /**
     * 返回xml
     * @return \think\response\Xml
     */
    public function retxml()
    {
        $data = ['name'=>'thinkphp','url'=>'thinkphp.cn'];
        return xml(['data'=>$data,'code'=>1,'message'=>'操作完成']);
    }


    // 获取测试数据库配置
    private function getConfig()
    {
        return [
            // 数据库类型
            'type'           => 'mysql',
            // 服务器地址
            'hostname'       => '127.0.0.1',
            // 数据库名
            'database'       => 'tp5cmf',
            // 用户名
            'username'       => 'root',
            // 密码
            'password'       => '123123',
            // 端口
            'hostport'       => '3306',
            // 连接dsn
            'dsn'            => '',
            // 数据库连接参数
            'params'         => [],
            // 数据库编码默认采用utf8
            'charset'        => 'utf8',
            // 数据库表前缀
            'prefix'         => 'tp_',
            // 数据库调试模式
            'debug'          => true,
            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'         => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'    => false,
            // 读写分离后 主服务器数量
            'master_num'     => 1,
            // 指定从服务器序号
            'slave_no'       => '',
            // 是否严格检查字段是否存在
            'fields_strict'  => true,
            // 数据集返回类型 array 数组 collection Collection对象
            'resultset_type' => 'array',
            // 是否自动写入时间戳字段
            'auto_timestamp' => false,
            // 是否需要进行SQL性能分析
            'sql_explain'    => false,
        ];
    }

    /**
     * @param int $name
     * @return int|string
     */
    public function showDatabases($name = 1)
    {
//        $config = $this->getConfig();
//        $result = Db::connect($config)->execute('show databases');
//        return json($result);

        // 更新
        return Db::table('cmf_user')
            ->where('id', 3)
            ->update(['user_login' => 'thinkhp']);
    }

        /**
     * model 的运用
     * @param int $name
     * @return \think\response\Json
     */
    public function getUser($name = 1){
//        return json(Db::table('cmf_user')->where('id',$name)->find());
//        return json(Db::name('user')->where('id',$name)->find());


        $userModel = new UserModel();

        return json($userModel->getUserbyId($name));

//        return json_decode(json_encode(Db::table('cmf_user')->where('id',1)->find()));
    }
}
