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

class CurlController
{
    private $cookieFile = "hua.ini";

    private function _login()
    {
        $param = request()->param();

        $url = 'http://hxp.ecust.edu.cn/chem/www/Factory/Index/loginJson.html';

        $this->cookieFile = "hua_" . $param['name'] . ".ini";

        $content = $this->curl($url, $param);

        print_r($content);

        echo "\n";
    }

    public function orders()
    {
        $this->_login();

        $name = request()->param('name');
        $user = db('test_user')->where('name', $name)->find();

        if (empty($user)) {

            $url     = 'http://hxp.ecust.edu.cn/chem/www/Factory/Index/read';
            $content = $this->curl($url);


            db('test_user')->insert([
                'name' => $name,
                'data' => $content
            ]);

        }

        $url  = 'http://hxp.ecust.edu.cn/chem/www/Factory/OrderManage/showallJson.html?status=';
        $data = [
            'pageIndex' => 0,
            'pageSize'  => 1000000000,
            'sortField' => '',
            'sortOrder' => ''
        ];

        $content = $this->curl($url, $data);

        if (!empty($content)) {
            $data = json_decode($content, true);

            foreach ($data['data'] as $order) {
                $orderId = $order['ORDERFORM_NO'];
                echo $orderId . "\n";
                $findOrder = db('test_order')->where('order_id', $orderId)->field('id,items')->find();

                if (empty($findOrder)) {
                    $url = 'http://hxp.ecust.edu.cn/chem/www/Factory/OrderManage/showItem.html?t1=' . $orderId;

                    $data = [
                        'pageIndex' => 0,
                        'pageSize'  => 1000000000,
                        'sortField' => '',
                        'sortOrder' => ''
                    ];

                    $items = $this->curl($url, $data);

                    db('test_order')->insert([
                        'order_id' => $orderId,
                        'order'    => json_encode($order),
                        'items'    => $items
                    ]);
                }
            }

        }

        echo "\n";
        exit;
    }

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
//        curl_setopt($ch, CURLOPT_COOKIELIST, 'RELOAD');
        $contents = curl_exec($ch);

        curl_close($ch);

        return $contents;
    }

}
