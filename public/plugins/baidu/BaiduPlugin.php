<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 睡不醒的猪
// +----------------------------------------------------------------------
namespace plugins\baidu;

use cmf\lib\Plugin;


class BaiduPlugin extends Plugin
{

    public $info = [
        'name'        => 'Baidu',
        'title'       => '百度统计插件',
        'description' => '百度统计插件',
        'status'      => 1,
        'author'      => '睡不醒的猪',
        'version'     => '1.0'
    ];

    public $hasAdmin = 0;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    public function adminDashboard()
    {
        $tongji_array = $this->get_tongji();
        $tongji_pv = array();
        $tongji_uv = array();
        $tongji_ip = array();
        $tongji_day = array();
        if(is_array($tongji_array)){
            $tongji_date = $tongji_array['body']['data'][0]['result']['items'][0];

            foreach($tongji_date as $key=>$value){
                $tongji_day[] = "\"".str_replace(array("/","2017-"),array("-",""),$value[0])."\"";
            }
            sort($tongji_day);

            $tongji_data = $tongji_array['body']['data'][0]['result']['items'][1];

            foreach($tongji_data as $key=>$value){
                $tongji_pv[] = "\"".($value[0]/10000)."\"";
                $tongji_uv[] = "\"".($value[1]/10000)."\"";
                $tongji_ip[] = "\"".($value[2]/10000)."\"";
            }
        }
        $this->assign("tongji_pv",implode(",",($tongji_pv)));
        $this->assign("tongji_uv",implode(",",($tongji_uv)));
        $this->assign("tongji_ip",implode(",",($tongji_ip)));
        $this->assign("tongji_day",implode(",",($tongji_day)));




        return [
            'width'  => 12,
            'view'   => $this->fetch('widget'),
            'plugin' => 'Baidu'
        ];
    }

    public function get_tongji(){
        vendor("baidu.Config");
        vendor("baidu.LoginService");
        vendor("baidu.ReportService");

        $end = date("Ymd",time());
        $start = date("Ymd",time()-6*24*3600);
        $is_update = 0; //可以做存储判断

        if($is_update){
            $data = '';
        }else{
            $loginService = new \LoginService(LOGIN_URL, UUID);
            $path = CMF_ROOT."public/plugins/baidu/view/public/";
            $config = $this->getConfig();
            $USERNAME =$config['username'];
            $TOKEN =$config['TOKEN'];
            $PASSWORD =$config['password'];


            if (!$loginService->preLogin($USERNAME, $TOKEN,$path)) {
                exit();
            }


            $ret = $loginService->doLogin($USERNAME, $PASSWORD, $TOKEN,$path);
            if ($ret) {
                $ucid = $ret['ucid'];
                $st = $ret['st'];
            }
            else {
                exit();
            }

            $reportService = new \ReportService(API_URL, $USERNAME, $TOKEN, $ucid, $st);

            $siteId = $config['siteid'];//去玩吗站点ID

            $ret = $reportService->getData(array(
                'site_id' => $siteId,                   //站点ID
                'method' => 'trend/time/a',             //趋势分析报告
                'start_date' => $start,             //所查询数据的起始日期
                'end_date' => $end,               //所查询数据的结束日期
                'metrics' => 'pv_count,visitor_count,ip_count',  //所查询指标为PV和UV
                'max_results' => 0,                     //返回所有条数
                'gran' => 'day',                        //按天粒度
            ));
            $data = json_decode($ret['raw'],true);
            $loginService->doLogout($USERNAME, $TOKEN, $ucid, $st,$path);

        }



        return $data;

    }

}