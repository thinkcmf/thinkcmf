<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\test\controller;

use cmf\controller\HomeBaseController;

class IndexController extends HomeBaseController
{
    private $cookieFile = "test.ini";

    public function index()
    {
        $content = $this->curl('http://demo.chemcms.com/portal/test/index');

        print_r($content);
    }

    // 116.208.11.181 ,119.101.117.113

    private function curl($url, $data = [], $post = true)
    {
        //提交登录表单请求
        $ch     = curl_init($url);
        $header = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko',
            'X-Requested-With: XMLHttpRequest'
        ];
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko'); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, $post ? 1 : 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile); //存储提交后得到的cookie数据
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);

        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
        curl_setopt($ch, CURLOPT_PROXY, "119.101.117.113"); //代理服务器地址
        curl_setopt($ch, CURLOPT_PROXYPORT, 9999); //代理服务器端口
        //curl_setopt($ch, CURLOPT_PROXYUSERPWD, ":"); //http代理认证帐号，username:password的格式
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

//        curl_setopt($ch, CURLOPT_COOKIELIST, 'RELOAD');
        $contents = curl_exec($ch);
        echo curl_error($ch);

        curl_close($ch);

        return $contents;
    }
}
