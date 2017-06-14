<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\Menu;

class MainController extends AdminBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     *  后台欢迎页
     */
    public function index()
    {
        $dashboardWidgets = [];
        $widgets          = cmf_get_option('admin_dashboard_widgets');

        if (empty($widgets)) {
            $dashboardWidgets = [
                '_SystemCmfHub'           => ['name' => 'CmfHub', 'is_system' => 1],
                '_SystemMainContributors' => ['name' => 'MainContributors', 'is_system' => 1],
                '_SystemContributors'     => ['name' => 'Contributors', 'is_system' => 1],
            ];
        } else {
            foreach ($widgets as $widget) {
                if ($widget['is_system']) {
                    $dashboardWidgets['_System' . $widget['name']] = ['name' => $widget['name'], 'is_system' => 1];
                } else {
                    $dashboardWidgets[$widget['name']] = ['name' => $widget['name'], 'is_system' => 0];
                }
            }
        }

        $dashboardWidgetPlugins = [];

        $hookResults = hook('admin_dashboard');

        if (!empty($hookResults)) {
            foreach ($hookResults as $hookResult) {
                if (isset($hookResult['width']) && isset($hookResult['view']) && isset($hookResult['plugin'])) { //验证插件返回合法性
                    $dashboardWidgetPlugins[$hookResult['plugin']] = $hookResult;
                    if (!isset($dashboardWidgets[$hookResult['plugin']])) {
                        $dashboardWidgets[$hookResult['plugin']] = ['name' => $hookResult['plugin'], 'is_system' => 0];
                    }
                }
            }
        }

        $smtpSetting = cmf_get_option('smtp_setting');

        $this->assign('dashboard_widgets', $dashboardWidgets);
        $this->assign('dashboard_widget_plugins', $dashboardWidgetPlugins);
        $this->assign('has_smtp_setting', empty($smtpSetting) ? false : true);


        //统计代码BY
        $tongji_array = $this->tongji();
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

        return $this->fetch();
    }

    public function dashboardWidget()
    {
        $dashboardWidgets = [];
        $widgets          = $this->request->param('widgets/a');
        if (!empty($widgets)) {
            foreach ($widgets as $widget) {
                if ($widget['is_system']) {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 1]);
                } else {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 0]);
                }
            }
        }

        cmf_set_option('admin_dashboard_widgets', $dashboardWidgets, true);

        $this->success('更新成功!');

    }

    /**
     * 后台获取百度统计
     * BY 睡不醒的猪
     */
    public function tongji(){
        vendor("baidu.Config");
        vendor("baidu.LoginService");
        vendor("baidu.ReportService");

        $end = date("Ymd",time());
        $start = date("Ymd",time()-6*24*3600);
        $is_update = 0;//$GLOBALS['db']->getOne("select count(*) from baidu where create_time='".($end)."'");

        if($is_update){
            $data = '';//$GLOBALS['db']->getOne("select json from baidu where create_time='".($end)."'");
        }else{
            $loginService = new \LoginService(LOGIN_URL, UUID);
            $path = CMF_ROOT."data/";

            if (!$loginService->preLogin(USERNAME, TOKEN,$path)) {
                return false;
            }


            $ret = $loginService->doLogin(USERNAME, PASSWORD, TOKEN,$path);
            if ($ret) {
                $ucid = $ret['ucid'];
                $st = $ret['st'];
            }
            else {
                return false;
            }

            $reportService = new \ReportService(API_URL, USERNAME, TOKEN, $ucid, $st);

            $ret = $reportService->getData(array(
                'site_id' => SITEID,                   //站点ID
                'method' => 'trend/time/a',             //趋势分析报告
                'start_date' => $start,             //所查询数据的起始日期
                'end_date' => $end,               //所查询数据的结束日期
                'metrics' => 'pv_count,visitor_count,ip_count',  //所查询指标为PV和UV
                'max_results' => 0,                     //返回所有条数
                'gran' => 'day',                        //按天粒度
            ));
            $data = json_decode($ret['raw'],true);
            $loginService->doLogout(USERNAME, TOKEN, $ucid, $st,$path);


        }

        return $data;
    }

}
