<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
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
        $mysql = Db::query("select VERSION() as version");
        $mysql = $mysql[0]['version'];
        $mysql = empty($mysql) ? lang('UNKNOWN') : $mysql;

        $version = THINKCMF_VERSION;

        //server infomation
        $info = [
            lang('OPERATING_SYSTEM')      => PHP_OS,
            lang('OPERATING_ENVIRONMENT') => $_SERVER["SERVER_SOFTWARE"],
            lang('PHP_VERSION')           => PHP_VERSION,
            lang('PHP_RUN_MODE')          => php_sapi_name(),
            lang('PHP_VERSION')           => phpversion(),
            lang('MYSQL_VERSION')         => $mysql,
            'ThinkPHP'                    => THINK_VERSION,
            'ThinkCMF'                    => "{$version} <a href=\"http://www.thinkcmf.com\" target=\"_blank\">访问官网</a>",
            lang('UPLOAD_MAX_FILESIZE')   => ini_get('upload_max_filesize'),
            lang('MAX_EXECUTION_TIME')    => ini_get('max_execution_time') . "s",
            //TODO 增加更多信息
            lang('DISK_FREE_SPACE')       => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
        ];
        $this->assign('server_info', $info);

        return $this->fetch();
    }
}
