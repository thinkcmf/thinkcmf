<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\model\RecycleBinModel;
use app\admin\model\SlideItemModel;
use app\admin\model\SlideModel;
use app\admin\model\ThemeFileModel;
use app\admin\model\ThemeModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;

class ThemeController extends RestAdminBaseController
{
    /**
     * 已安装模板列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/themes",
     *     summary="已安装模板列表",
     *     description="已安装模板列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "themes":{
     *                  {"id": 1,"create_time": 0,
     *                  "update_time": 0,"status": 0,
     *                  "is_compiled": 0,
     *                  "theme": "default",
     *                  "name": "default","version": "1.0.0",
     *                  "demo_url": "http://demo.thinkcmf.com","thumbnail": "",
     *                  "author": "ThinkCMF","author_url": "http://www.thinkcmf.com",
     *                  "lang": "zh-cn","keywords": "ThinkCMF默认模板",
     *                  "description": "ThinkCMF默认模板"}
     *              }
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
        $themeModel = new ThemeModel();
        $themes     = $themeModel->select();

        $defaultTheme = config('template.cmf_default_theme');
        if ($temp = session('cmf_default_theme')) {
            $defaultTheme = $temp;
        }
        $this->success('success', ['themes' => $themes, 'default_theme' => $defaultTheme]);
    }

    /**
     * 未安装模板列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/themes/not/installed",
     *     summary="未安装模板列表",
     *     description="未安装模板列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "themes":{
     *                  {"id": 1,"create_time": 0,
     *                  "update_time": 0,"status": 0,
     *                  "is_compiled": 0,
     *                  "theme": "default",
     *                  "name": "default","version": "1.0.0",
     *                  "demo_url": "http://demo.thinkcmf.com","thumbnail": "",
     *                  "author": "ThinkCMF","author_url": "http://www.thinkcmf.com",
     *                  "lang": "zh-cn","keywords": "ThinkCMF默认模板",
     *                  "description": "ThinkCMF默认模板"}
     *              }
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function notInstalled()
    {
        $themesDirs = cmf_scan_dir(WEB_ROOT . "themes/*", GLOB_ONLYDIR);

        $themeModel = new ThemeModel();

        $themesInstalled = $themeModel->column('theme');

        $themesDirs = array_diff($themesDirs, $themesInstalled);

        $themes = [];
        foreach ($themesDirs as $dir) {
            if (!preg_match("/^admin_/", $dir)) {
                $manifest = WEB_ROOT . "themes/$dir/manifest.json";
                if (file_exists_case($manifest)) {
                    $manifest       = file_get_contents($manifest);
                    $theme          = json_decode($manifest, true);
                    $theme['theme'] = $dir;
                    array_push($themes, $theme);
                }
            }
        }
        $this->success('success', ['themes' => $themes]);
    }

    /**
     * 安装模板
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/themes/{theme}",
     *     summary="安装模板",
     *     description="安装模板",
     *     @OA\Parameter(
     *         name="theme",
     *         in="path",
     *         example="demo",
     *         description="模板名,如demo,simpleboot3",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "安装成功！","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function install()
    {
        if ($this->request->isPost()) {
            $theme      = $this->request->param('theme');
            $themeModel = new ThemeModel();
            $themeCount = $themeModel->where('theme', $theme)->count();

            if ($themeCount > 0) {
                $this->error('模板已经安装!');
            }
            $result = $themeModel->installTheme($theme);
            if ($result === false) {
                $this->error('模板不存在!');
            }
            $this->success(lang('Installed successfully'));
        }
    }

    /**
     * 更新模板
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/themes/{theme}",
     *     summary="更新模板",
     *     description="更新模板",
     *     @OA\Parameter(
     *         name="theme",
     *         in="path",
     *         example="demo",
     *         description="模板名,如demo,simpleboot3",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "更新成功！","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function update()
    {
        if ($this->request->isPut()) {
            $theme      = $this->request->param('theme');
            $themeModel = new ThemeModel();
            $themeCount = $themeModel->where('theme', $theme)->count();

            if ($themeCount === 0) {
                $this->error('模板未安装!');
            }
            $result = $themeModel->updateTheme($theme);
            if ($result === false) {
                $this->error('模板不存在!');
            }
            $this->success(lang('Updated successfully'));
        }
    }

    /**
     *  卸载模板
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/themes/{theme}",
     *     summary="卸载模板",
     *     description="卸载模板",
     *     @OA\Parameter(
     *         name="theme",
     *         in="path",
     *         example="demo",
     *         description="模板名,如demo,simpleboot3",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "卸载成功！","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "默认模板无法卸载!","data":""})
     *     ),
     * )
     */
    public function uninstall()
    {
        if ($this->request->isDelete()) {
            $theme = $this->request->param('theme');
            if (config('template.cmf_default_theme') == $theme) {
                $this->error(lang('NOT_ALLOWED_UNINSTALL_THEME_ERROR'));
            }

            $themeModel = new ThemeModel();
            $themeModel->transaction(function () use ($theme, $themeModel) {
                $themeModel->where('theme', $theme)->delete();
                ThemeFileModel::where('theme', $theme)->delete();
            });

            $this->success(lang('Uninstall successful'));

        }
    }


}
