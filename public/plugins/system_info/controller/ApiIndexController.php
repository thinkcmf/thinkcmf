<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace plugins\system_info\controller;

//Demo插件英文名，改成你的插件英文就行了


use cmf\controller\PluginRestBaseController;
use think\facade\Db;

/**
 * Class AdminIndexController.
 */
class ApiIndexController extends PluginRestBaseController
{
    /**
     * 获取后台系统信息
     * Throws \think\exception\DbException
     * @OA\Get(
     *     tags={"plugin/SystemInfo"},
     *     path="/plugin/system_info/api_admin_index/index",
     *     summary="获取后台系统信息",
     *     description="获取后台系统信息",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "hello": "hello ThinkCMF Admin!"
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function index()
    {
        $mysql = Db::query("select VERSION() as version");
        $mysql = $mysql[0]['version'];
        $mysql = empty($mysql) ? lang('UNKNOWN') : $mysql;

        $version = cmf_version();

        $curlVersions = curl_version();

        $opensslVersion = 'Unknown';
        if (defined('OPENSSL_VERSION_TEXT')) {
            $opensslVersion = OPENSSL_VERSION_TEXT;
        }

        //server infomation
        $info = [
            lang('OPERATING_SYSTEM')      => PHP_OS,
            lang('OPERATING_ENVIRONMENT') => $_SERVER["SERVER_SOFTWARE"],
            lang('PHP_VERSION')           => PHP_VERSION,
            lang('PHP_RUN_MODE')          => php_sapi_name(),
            lang('PHP_VERSION')           => phpversion(),
            lang('MYSQL_VERSION')         => $mysql,
            'CURL'                        => $curlVersions['version'],
            'OpenSSL'                     => $opensslVersion,
            'ThinkPHP'                    => cmf_thinkphp_version(),
            'ThinkCMF'                    => $version,
            lang('UPLOAD_MAX_FILESIZE')   => ini_get('upload_max_filesize'),
            lang('MAX_EXECUTION_TIME')    => ini_get('max_execution_time') . "s",
            //TODO 增加更多信息
            lang('DISK_FREE_SPACE')       => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
        ];
        $this->success("success", ['item' => $info]);
    }


}
