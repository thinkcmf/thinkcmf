<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\RestAdminBaseController;
use cmf\controller\RestBaseController;
use OpenApi\Annotations as OA;
use think\facade\Db;
use think\facade\Validate;

class SettingController extends RestAdminBaseController
{
    /**
     * 清理缓存
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/setting/cache",
     *     summary="清理缓存",
     *     description="清理缓存",
     *     @OA\Response(
     *          response="1",
     *          @OA\JsonContent(example={"code": 1,"msg": "清除成功!","data": ""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "清除失败!","data": ""})
     *     ),
     * )
     */
    public function clearCache()
    {
        cmf_clear_cache();
        $this->success('清除成功！');
    }

    /**
     * 网站信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/setting/site",
     *     summary="网站信息",
     *     description="网站信息",
     *     @OA\Response(
     *          response="1",
     *          @OA\JsonContent(example={"code": 1,"msg": "网站信息!","data": ""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "网站信息!","data": ""})
     *     ),
     * )
     */
    public function site()
    {
        $noNeedDirs     = [".", "..", ".svn", 'fonts'];
        $adminThemesDir = WEB_ROOT . config('template.cmf_admin_theme_path') . config('template.cmf_admin_default_theme') . '/public/assets/themes/';
        $adminStyles    = cmf_scan_dir($adminThemesDir . '*', GLOB_ONLYDIR);
        $adminStyles    = array_diff($adminStyles, $noNeedDirs);
        $cdnSettings    = cmf_get_option('cdn_settings');
        $cmfSettings    = cmf_get_option('cmf_settings');
        $adminSettings  = cmf_get_option('admin_settings');

        $adminThemes = [];
        $themes      = cmf_scan_dir(WEB_ROOT . config('template.cmf_admin_theme_path') . '/*', GLOB_ONLYDIR);

        foreach ($themes as $theme) {
            if (strpos($theme, 'admin_') === 0) {
                array_push($adminThemes, $theme);
            }
        }

        if (APP_DEBUG && false) { // TODO 没确定要不要可以设置默认应用
            $apps = cmf_scan_dir($this->app->getAppPath() . '*', GLOB_ONLYDIR);
            $apps = array_diff($apps, $noNeedDirs);
        }

        $siteInfo = cmf_get_option('site_info');
        $this->success("success", [
            'site_info'      => $siteInfo,
            'admin_styles'   => array_values($adminStyles),
            'admin_themes'   => $adminThemes,
            'cdn_settings'   => $cdnSettings,
            'admin_settings' => $adminSettings,
            'cmf_settings'   => $cmfSettings,
        ]);
    }

    /**
     * 网站信息提交保存
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/setting/site",
     *     summary="网站信息提交保存",
     *     description="网站信息提交保存",
     *     @OA\Response(
     *          response="1",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功!","data": ""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "保存成功!","data": ""})
     *     ),
     * )
     */
    public function sitePost()
    {
        $result = $this->validate($this->request->param(), 'SettingSite');
        if ($result !== true) {
            $this->error($result);
        }

        $options = $this->request->param('options/a');
        cmf_set_option('site_info', $options);

        $cmfSettings = $this->request->param('cmf_settings/a');

        $bannedUsernames                 = preg_replace("/[^0-9A-Za-z_\\x{4e00}-\\x{9fa5}-]/u", ",", $cmfSettings['banned_usernames']);
        $cmfSettings['banned_usernames'] = $bannedUsernames;
        cmf_set_option('cmf_settings', $cmfSettings);

        $cdnSettings = $this->request->param('cdn_settings/a');
        cmf_set_option('cdn_settings', $cdnSettings);

        $adminSettings = $this->request->param('admin_settings/a');

        $routeModel = new RouteModel();
        if (!empty($adminSettings['admin_password'])) {
            $routeModel->setRoute($adminSettings['admin_password'] . '$', 'admin/Index/index', [], 2, 5000);
        } else {
            $routeModel->deleteRoute('admin/Index/index', []);
        }

        $routeModel->getRoutes(true);

        if (!empty($adminSettings['admin_theme'])) {
            $result = cmf_set_dynamic_config([
                'template' => [
                    'cmf_admin_default_theme' => $adminSettings['admin_theme']
                ]
            ]);

            if ($result === false) {
                $this->error('配置写入失败!');
            }
        }

        cmf_set_option('admin_settings', $adminSettings);

        $this->success(lang('EDIT_SUCCESS'));

    }

}
