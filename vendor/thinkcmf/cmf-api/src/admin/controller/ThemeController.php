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
use think\Validate;

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

    /**
     *  启用模板
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/themes/{theme}/active",
     *     summary="启用模板",
     *     description="启用模板",
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
     *          @OA\JsonContent(example={"code": 1,"msg": "启用成功！","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "模板未安装!","data":""})
     *     ),
     * )
     */
    public function active()
    {
        if ($this->request->isPost()) {
            $theme = $this->request->param('theme');

            if ($theme == config('template.cmf_default_theme')) {
                $this->error('模板已启用');
            }

            $themeModel = new ThemeModel();
            $themeCount = $themeModel->where('theme', $theme)->count();

            if ($themeCount === 0) {
                $this->error('模板未安装!');
            }

            $result = cmf_set_dynamic_config(['template' => ['cmf_default_theme' => $theme]]);

            if ($result === false) {
                $this->error('配置写入失败!');
            }
            session('cmf_default_theme', $theme);

            $this->success('模板启用成功');
        }
    }

    /**
     * 模板文件列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/theme/{theme}/files",
     *     summary="模板文件列表",
     *     description="模板文件列表",
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
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "files":{
     *                  {"id": 141,"is_public": 1,"list_order": 0,"theme": "default","name": "模板全局配置","action": "public/Config","file": "public/config","description": "模板全局配置文件","more": {"vars": {"enable_mobile": {"title": "手机注册","type": "select","value": 1,"options": {"0": "关闭","1": "开启"},"tip": ""}}}}
     *              }
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function files()
    {
        $theme = $this->request->param('theme');
        $files = ThemeFileModel::where('theme', $theme)->order('list_order ASC')->select();
        $this->success('success', ['files' => $files]);
    }

    /**
     * 获取模板文件设置
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/theme/{theme}/file/setting",
     *     summary="获取模板文件设置",
     *     description="获取模板文件设置",
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
     *     @OA\Parameter(
     *         name="file",
     *         in="query",
     *         example="portal/index",
     *         description="模板文件ID或模板文件名,如1,portal/index",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "files":{
     *                  {"id": 141,"is_public": 1,"list_order": 0,"theme": "default","name": "模板全局配置","action": "public/Config","file": "public/config","description": "模板全局配置文件","more": {"vars": {"enable_mobile": {"title": "手机注册","type": "select","value": 1,"options": {"0": "关闭","1": "开启"},"tip": ""}}}}
     *              }
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function fileSetting()
    {
        $file   = $this->request->param('file');
        $fileId = 0;
        if (!is_int($file)) {
            $fileName = $file;
            $theme    = $this->request->param('theme');
            $files    = ThemeFileModel::where('theme', $theme)
                ->where(function ($query) use ($file) {
                    $query->where('is_public', 1)->whereOr('file', $file);
                })->order('list_order ASC')->select();
            $file     = ThemeFileModel::where(['file' => $file, 'theme' => $theme])->find();
        } else {
            $fileId   = $file;
            $file     = ThemeFileModel::where('id', $fileId)->find();
            $files    = ThemeFileModel::where('theme', $file['theme'])
                ->where(function ($query) use ($fileId) {
                    $query->where('id', $fileId)->whereOr('is_public', 1);
                })->order('list_order ASC')->select();
            $fileName = $file['file'];
        }

        $hasFile = false;
        if (!empty($file)) {
            $hasFile = true;
            $fileId  = $file['id'];
        }
        $hasPublicVar = false;
        $hasWidget    = false;
        foreach ($files as $key => $mFile) {
            $hasFile = true;
            if (!empty($mFile['is_public']) && !empty($mFile['more']['vars'])) {
                $hasPublicVar = true;
            }

            if (!empty($mFile['more']['widgets'])) {
                $hasWidget = true;
            }

            $files[$key] = $mFile;
        }

        $this->success('success', [
            'file_name'      => $fileName,
            'files'          => $files,
            'file'           => $file,
            'file_id'        => $fileId,
            'has_public_var' => $hasPublicVar,
            'has_widget'     => $hasWidget,
            'has_file'       => $hasFile
        ]);
    }

    /**
     * 模板设置提交
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/theme/{theme}/file/setting",
     *     summary="模板设置提交",
     *     description="模板设置提交",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminThemeFileSettingPostRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "slide":{"id": 1,"status": 1,"delete_time": 0,"name": "又菜又爱玩","remark": ""}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function fileSettingPost()
    {
        if ($this->request->isPost()) {
            $files = $this->request->param('files/a');
            if (!empty($files) && is_array($files)) {
                foreach ($files as $id => $post) {
                    $file = ThemeFileModel::field('theme,more')->where('id', $id)->find();
                    $more = $file['more'];
                    if (isset($post['vars'])) {
                        $messages = [];
                        $rules    = [];

                        foreach ($more['vars'] as $mVarName => $mVar) {

                            if (!empty($mVar['rule'])) {
                                $rules[$mVarName] = $this->_parseRules($mVar['rule']);
                            }

                            if (!empty($mVar['message'])) {
                                foreach ($mVar['message'] as $rule => $msg) {
                                    $messages[$mVarName . '.' . $rule] = $msg;
                                }
                            }

                            if (isset($post['vars'][$mVarName])) {
                                $more['vars'][$mVarName]['value'] = $post['vars'][$mVarName];
                            }

                            if (isset($post['vars'][$mVarName . '_text_'])) {
                                $more['vars'][$mVarName]['valueText'] = $post['vars'][$mVarName . '_text_'];
                            }
                        }

                        $validate = new Validate();
                        $validate->rule($rules);
                        $validate->message($messages);
                        $result   = $validate->check($post['vars']);
                        if (!$result) {
                            $this->error($validate->getError());
                        }
                    }

                    if (isset($post['widget_vars']) || isset($post['widget'])) {
                        foreach ($more['widgets'] as $mWidgetName => $widget) {

                            if (empty($post['widget'][$mWidgetName]['display'])) {
                                $widget['display'] = 0;
                            } else {
                                $widget['display'] = 1;
                            }

                            if (!empty($post['widget'][$mWidgetName]['title'])) {
                                $widget['title'] = $post['widget'][$mWidgetName]['title'];
                            }

                            $messages = [];
                            $rules    = [];

                            foreach ($widget['vars'] as $mVarName => $mVar) {

                                if (!empty($mVar['rule'])) {
                                    $rules[$mVarName] = $this->_parseRules($mVar['rule']);
                                }

                                if (!empty($mVar['message'])) {
                                    foreach ($mVar['message'] as $rule => $msg) {
                                        $messages[$mVarName . '.' . $rule] = $msg;
                                    }
                                }

                                if (isset($post['widget_vars'][$mWidgetName][$mVarName])) {
                                    $widget['vars'][$mVarName]['value'] = $post['widget_vars'][$mWidgetName][$mVarName];
                                }

                                if (isset($post['widget_vars'][$mWidgetName][$mVarName . '_text_'])) {
                                    $widget['vars'][$mVarName]['valueText'] = $post['widget_vars'][$mWidgetName][$mVarName . '_text_'];
                                }
                            }

                            if ($widget['display']) {
                                $validate   = new Validate();
                                $validate->rule($rules);
                                $validate->message($messages);
                                $widgetVars = empty($post['widget_vars'][$mWidgetName]) ? [] : $post['widget_vars'][$mWidgetName];
                                $result     = $validate->check($widgetVars);
                                if (!$result) {
                                    $this->error($widget['title'] . ':' . $validate->getError());
                                }
                            }

                            $more['widgets'][$mWidgetName] = $widget;
                        }
                    }

                    $more = json_encode($more);
                    ThemeFileModel::where('id', $id)->update(['more' => $more]);
                }
            }
            cmf_clear_cache();
            $this->success(lang('EDIT_SUCCESS'), '');
        }
    }

    /**
     * 解析模板变量验证规则
     * @param $rules
     * @return array
     */
    private function _parseRules($rules)
    {
        $newRules = [];

        $simpleRules = [
            'require', 'number',
            'integer', 'float', 'boolean', 'email',
            'array', 'accepted', 'date', 'alpha',
            'alphaNum', 'alphaDash', 'activeUrl',
            'url', 'ip'];
        foreach ($rules as $key => $rule) {
            if (in_array($key, $simpleRules) && $rule) {
                array_push($newRules, $key);
            }
        }

        return $newRules;
    }

    /**
     * 获取模板自由控件设置
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/theme/widget/setting",
     *     summary="获取模板自由控件设置",
     *     description="获取模板自由控件设置",
     *     @OA\Parameter(
     *         name="widget_id",
     *         in="query",
     *         example="",
     *         description="自由控件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="block_name",
     *         in="query",
     *         example="",
     *         description="模板块名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="file_id",
     *         in="query",
     *         example="",
     *         description="模板文件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "files":{
     *                  {"id": 141,"is_public": 1,"list_order": 0,"theme": "default","name": "模板全局配置","action": "public/Config","file": "public/config","description": "模板全局配置文件","more": {"vars": {"enable_mobile": {"title": "手机注册","type": "select","value": 1,"options": {"0": "关闭","1": "开启"},"tip": ""}}}}
     *              }
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function widgetSetting()
    {
        $widgetId  = $this->request->param('widget_id', '');
        $blockName = $this->request->param('block_name', '');
        $fileId    = $this->request->param('file_id', 0, 'intval');

        $file            = ThemeFileModel::where('id', $fileId)->find();
        $oldMore         = $file['more'];
        $widgetWithValue = $oldMore['widgets_blocks'][$blockName]['widgets'][$widgetId];
        $theme           = $file['theme'];
        $widgetManifest  = file_get_contents(WEB_ROOT . "themes/$theme/public/widgets/{$widgetWithValue['name']}/manifest.json");
        $widget          = json_decode($widgetManifest, true);

        $defaultCss = [
            "margin-top"    => [
                "title" => "上边距",
                "value" => "0",
                "type"  => "text",
                "tip"   => "支持单位,如px(像素),em(字符),rem;例子:10px,2em,1rem",
            ],
            "margin-bottom" => [
                "title" => "下边距",
                "value" => "15px",
                "type"  => "text",
                "tip"   => "支持单位,如px(像素),em(字符),rem;例子:10px,2em,1rem",
            ],
            "margin-left"   => [
                "title" => "左边距",
                "value" => "0",
                "type"  => "text",
                "tip"   => "支持单位,如px(像素),em(字符),rem;例子:10px,2em,1rem",
            ],
            "margin-right"  => [
                "title" => "右边距",
                "value" => "0",
                "type"  => "text",
                "tip"   => "支持单位,如px(像素),em(字符),rem;例子:10px,2em,1rem",
            ],
        ];
        if (empty($widget['css'])) {
            $widget['css'] = $defaultCss;
        } else {
            $widget['css'] = array_merge($defaultCss, $widget['css']);
        }

        foreach ($widgetWithValue as $key => $value) {
            if ($key == 'vars') {
                foreach ($value as $varName => $varValue) {
                    if (isset($widget['vars'][$varName])) {
                        if (isset($value[$varName . '_text_'])) {
                            $widget['vars'][$varName]['valueText'] = $value[$varName . '_text_'];
                        }

                        if (in_array($widget['vars'][$varName]['type'], ['rich_text'])) {
                            $varValue = cmf_replace_content_file_url(htmlspecialchars_decode($varValue));
                        }

                        $widget['vars'][$varName]['value'] = $varValue;
                    }
                }
            } else if ($key == 'css') {
                foreach ($value as $varName => $varValue) {
                    if (isset($widget['css'][$varName])) {
                        $widget['css'][$varName]['value'] = $varValue;
                    }
                }
            } else {
                $widget[$key] = $value;
            }
        }

        $this->success('success', ['widget' => $widget]);
    }

    /**
     * 模板自由控件设置提交保存
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/theme/widget/setting",
     *     summary="模板自由控件设置提交保存",
     *     description="模板自由控件设置提交保存",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminThemeWidgetSettingPostRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function widgetSettingPost()
    {
        $widgetId  = $this->request->param('widget_id', '');
        $blockName = $this->request->param('block_name', '');
        $fileId    = $this->request->param('file_id', 0, 'intval');
        $widget    = $this->request->param('widget/a');
        $vars      = empty($widget['vars']) ? [] : $widget['vars'];
        $cssVars   = empty($widget['css']) ? [] : $widget['css'];

        $file      = ThemeFileModel::where('id', $fileId)->find();
        $oldMore   = $file['more'];
        $oldWidget = $oldMore['widgets_blocks'][$blockName]['widgets'][$widgetId];

        $theme          = $file['theme'];
        $widgetManifest = file_get_contents(WEB_ROOT . "themes/$theme/public/widgets/{$oldWidget['name']}/manifest.json");
        $widgetInFile   = json_decode($widgetManifest, true);

        foreach ($vars as $varName => $varValue) {
            if (isset($widgetInFile['vars'][$varName])) {
                if (isset($vars[$varName . '_text_'])) {
                    $oldWidget['vars'][$varName . '_text_'] = $vars[$varName . '_text_'];
                }
                if (in_array($widgetInFile['vars'][$varName]['type'], ['rich_text'])) {
                    $varValue = htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($varValue), true));

                    $oldWidget['vars'][$varName . '_type_'] = $widgetInFile['vars'][$varName]['type'];
                }
                $oldWidget['vars'][$varName] = $varValue;
            }
        }

        foreach ($cssVars as $varName => $varValue) {
            if (isset($widgetInFile['css'][$varName]) || in_array($varName, ['margin-top', 'margin-bottom', 'margin-left', 'margin-right'])) {
                $oldWidget['css'][$varName] = $varValue;
            }
        }

        $oldWidget['display'] = isset($widget['display']) && !empty($widget['display']) ? 1 : 0;
        if (isset($widget['title'])) {
            $oldWidget['title'] = $widget['title'];
        }

        $oldMore['widgets_blocks'][$blockName]['widgets'][$widgetId] = $oldWidget;

        $more = json_encode($oldMore);
        ThemeFileModel::where('id', $fileId)->update(['more' => $more]);
        cmf_clear_cache();
        $this->success(lang('EDIT_SUCCESS'));
    }

    /**
     * 自由模板控件排序
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/theme/widgets/sort",
     *     summary="自由模板控件排序",
     *     description="自由模板控件排序",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminThemeWidgetsSortRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function widgetsSort()
    {
        $files   = $this->request->param();
        $widgets = [];

        foreach ($files as $fileId => $widgetsBlocks) {
            $fileId     = str_replace('file', '', $fileId);
            $file       = ThemeFileModel::where('id', $fileId)->find();
            $configMore = $file['more'];
            if (!empty($configMore['widgets_blocks'])) {
                foreach ($configMore['widgets_blocks'] as $widgetsBlockName => $widgetsBlock) {
                    if (!empty($configMore['widgets_blocks'][$widgetsBlockName]['widgets'])) {
                        foreach ($configMore['widgets_blocks'][$widgetsBlockName]['widgets'] as $widgetId => $widget) {
                            $widgets[$widgetId] = $widget;
                        }
                    }
                }
            }
        }

        foreach ($files as $fileId => $widgetsBlocks) {
            $fileId     = str_replace('file', '', $fileId);
            $file       = ThemeFileModel::where('id', $fileId)->find();
            $configMore = $file['more'];

            foreach ($widgetsBlocks as $widgetsBlockName => $widgetIds) {
                $mWidgets = [];
                foreach ($widgetIds as $widgetIdInfo) {
                    $widgetId = $widgetIdInfo['widget_id'];

                    if (!empty($widgets[$widgetId])) {
                        $mWidgets[$widgetId] = $widgets[$widgetId];
                    }
                }
                $configMore['widgets_blocks'][$widgetsBlockName]['widgets'] = $mWidgets;
            }

            if (!empty($configMore['widgets_blocks'])) {
                foreach ($configMore['widgets_blocks'] as $widgetsBlockName => $widgetsBlock) {
                    if (!isset($widgetsBlocks[$widgetsBlockName])) {
                        $configMore['widgets_blocks'][$widgetsBlockName]['widgets'] = [];
                    }
                }
            }

            $configMore['edited_by_designer'] = 1;
            $more                             = json_encode($configMore);
            ThemeFileModel::where('id', $fileId)->update(['more' => $more]);
        }
        cmf_clear_cache();
        $this->success('排序成功！');
    }

    /**
     * 获取模板文件模板变量数组数据
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/theme/file/var/array",
     *     summary="获取模板文件模板变量数组数据",
     *     description="获取模板文件模板变量数组数据",
     *     @OA\Parameter(
     *         name="file_id",
     *         in="query",
     *         example="1",
     *         description="模板文件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="var",
     *         in="query",
     *         example="var1",
     *         description="变量名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "items":{
     *                  {"title": "xxxx","content": "xxxxxxx"}
     *              },
     *              "item":{"title": {"title": "标题","value": "","type": "text","rule": {"require": true}},"content": {"title": "内容","value": "","type": "text"}}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/theme/file/widget/array",
     *     summary="获取模板文件控件数组数据",
     *     description="获取模板文件控件数组数据",
     *     @OA\Parameter(
     *         name="file_id",
     *         in="query",
     *         example="1",
     *         description="模板文件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="widget",
     *         in="query",
     *         example="widget1",
     *         description="控件名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="var",
     *         in="query",
     *         example="var1",
     *         description="变量名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "items":{
     *                  {"title": "xxxx","content": "xxxxxxx"}
     *              },
     *              "item":{"title": {"title": "标题","value": "","type": "text","rule": {"require": true}},"content": {"title": "内容","value": "","type": "text"}}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/theme/file/block/widget/array",
     *     summary="获取模板文件块控件数组数据",
     *     description="获取模板文件块控件数组数据",
     *     @OA\Parameter(
     *         name="file_id",
     *         in="query",
     *         example="1",
     *         description="模板文件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="block_name",
     *         in="query",
     *         example="block1",
     *         description="块名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="widget_id",
     *         in="query",
     *         example="xxxxxx",
     *         description="自由控件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="var",
     *         in="query",
     *         example="var1",
     *         description="变量名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "items":{
     *                  {"title": "xxxx","content": "xxxxxxx"}
     *              },
     *              "item":{"title": {"title": "标题","value": "","type": "text","rule": {"require": true}},"content": {"title": "内容","value": "","type": "text"}}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function fileArrayData()
    {
        $tab        = $this->request->param('tab', 'widget');
        $varName    = $this->request->param('var', '');
        $widgetName = $this->request->param('widget', '');
        $fileId     = $this->request->param('file_id', 0, 'intval');
        $widgetId   = $this->request->param('widget_id', ''); //自由控件编辑
        $blockName  = $this->request->param('block_name', '');//自由控件编辑
        $file       = ThemeFileModel::where('id', $fileId)->find();
        $oldMore    = $file['more'];


        $items = [];
        $item  = [];

        $tab = ($tab == 'public_var') ? 'var' : $tab;

        if ($tab == 'var' && !empty($oldMore['vars']) && is_array($oldMore['vars'])) {

            if (isset($oldMore['vars'][$varName]) && is_array($oldMore['vars'][$varName])) {
                $items = $oldMore['vars'][$varName]['value'];
            }

            if (isset($oldMore['vars'][$varName]['item'])) {
                $item = $oldMore['vars'][$varName]['item'];
            }

        }

        if ($tab == 'widget' && !empty($oldMore['widgets'][$widgetName]) && is_array($oldMore['widgets'][$widgetName])) {
            $widget = $oldMore['widgets'][$widgetName];
            if (!empty($widget['vars']) && is_array($widget['vars'])) {
                foreach ($widget['vars'] as $mVarName => $mVar) {
                    if ($mVarName == $varName) {
                        if (is_array($mVar['value'])) {
                            $items = $mVar['value'];
                        }

                        if (isset($mVar['item'])) {
                            $item = $mVar['item'];
                        }
                    }
                }
            }
        }

        if ($tab == 'block_widget' && isset($oldMore['widgets_blocks'][$blockName]['widgets'][$widgetId])) {
            $widget = $file->fillBlockWidgetValue($blockName, $widgetId);

            if (!empty($widget['vars'][$varName])) {
                $mVar = $widget['vars'][$varName];
                if (is_array($mVar['value'])) {
                    $items = $mVar['value'];
                }

                if (isset($mVar['item'])) {
                    $item = $mVar['item'];
                }
            }
        }

        $this->success('success', ['items' => $items, 'item' => $item]);
    }

    /**
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/theme/file/var/array",
     *     summary="模板文件模板变量数组数据添加编辑提交保存",
     *     description="获取模板文件模板变量数组数据添加编辑提交保存",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminThemeFileArrayDataEditPostVar")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/theme/file/widget/array",
     *     summary="模板文件控件数组数据添加编辑提交保存",
     *     description="模板文件控件数组数据添加编辑提交保存",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminThemeFileArrayDataEditPostWidget")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "items":{
     *                  {"id": 141,"is_public": 1,"list_order": 0,"theme": "default","name": "模板全局配置","action": "public/Config","file": "public/config","description": "模板全局配置文件","more": {"vars": {"enable_mobile": {"title": "手机注册","type": "select","value": 1,"options": {"0": "关闭","1": "开启"},"tip": ""}}}}
     *              },
     *              "item":{}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/theme/file/block/widget/array",
     *     summary="模板文件块控件数组数据添加编辑提交保存",
     *     description="模板文件块控件数组数据添加编辑提交保存",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminThemeFileArrayDataEditPostBlockWidget")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "items":{
     *                  {"id": 141,"is_public": 1,"list_order": 0,"theme": "default","name": "模板全局配置","action": "public/Config","file": "public/config","description": "模板全局配置文件","more": {"vars": {"enable_mobile": {"title": "手机注册","type": "select","value": 1,"options": {"0": "关闭","1": "开启"},"tip": ""}}}}
     *              },
     *              "item":{}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function fileArrayDataEditPost()
    {
        if (!$this->request->isPost()) {
            $this->error(lang('illegal request'));
        }
        $tab        = $this->request->param('tab', 'widget');
        $varName    = $this->request->param('var');
        $widgetName = $this->request->param('widget', '');
        $widgetId   = $this->request->param('widget_id', ''); //自由控件编辑
        $blockName  = $this->request->param('block_name', '');//自由控件编辑
        $fileId     = $this->request->param('file_id', 0, 'intval');
        $itemIndex  = $this->request->param('item_index', '');

        $file = ThemeFileModel::where('id', $fileId)->find();

        if ($this->request->isPost()) {

            $post = $this->request->param();

            $more = $file['more'];

            if ($tab == 'var') {
                if (isset($more['vars'][$varName])) {
                    $mVar = $more['vars'][$varName];
                    if ($mVar['type'] == 'array') {

                        $messages = [];
                        $rules    = [];

                        foreach ($mVar['item'] as $varItemKey => $varItem) {
                            if (!empty($varItem['rule'])) {
                                $rules[$varItemKey] = $this->_parseRules($varItem['rule']);
                            }

                            if (!empty($varItem['message'])) {
                                foreach ($varItem['message'] as $rule => $msg) {
                                    $messages[$varItemKey . '.' . $rule] = $msg;
                                }
                            }
                        }

                        $validate = new Validate();
                        $validate->rule($rules);
                        $validate->message($messages);
                        $result   = $validate->check($post['item']);
                        if (!$result) {
                            $this->error($validate->getError());
                        }

                        if ($itemIndex === '') {
                            if (!empty($mVar['value']) && is_array($mVar['value'])) {
                                array_push($more['vars'][$varName]['value'], $post['item']);
                            } else {
                                $more['vars'][$varName]['value'] = [$post['item']];
                            }
                        } else {
                            if (!empty($mVar['value']) && is_array($mVar['value']) && isset($mVar['value'][$itemIndex])) {
                                $more['vars'][$varName]['value'][$itemIndex] = $post['item'];
                            }
                        }
                    }
                }
            }

            if ($tab == 'widget') {
                if (isset($more['widgets'][$widgetName])) {
                    $widget = $more['widgets'][$widgetName];
                    if (!empty($widget['vars']) && is_array($widget['vars'])) {
                        if (isset($widget['vars'][$varName])) {
                            $widgetVar = $widget['vars'][$varName];
                            if ($widgetVar['type'] == 'array') {
                                $messages = [];
                                $rules    = [];

                                foreach ($widgetVar['item'] as $widgetArrayVarItemKey => $widgetArrayVarItem) {
                                    if (!empty($widgetArrayVarItem['rule'])) {
                                        $rules[$widgetArrayVarItemKey] = $this->_parseRules($widgetArrayVarItem['rule']);
                                    }

                                    if (!empty($widgetArrayVarItem['message'])) {
                                        foreach ($widgetArrayVarItem['message'] as $rule => $msg) {
                                            $messages[$widgetArrayVarItemKey . '.' . $rule] = $msg;
                                        }
                                    }
                                }

                                $validate = new Validate();
                                $validate->rule($rules);
                                $validate->message($messages);
                                $result   = $validate->check($post['item']);
                                if (!$result) {
                                    $this->error($validate->getError());
                                }

                                if ($itemIndex === '') {
                                    if (!empty($widgetVar['value']) && is_array($widgetVar['value'])) {
                                        array_push($more['widgets'][$widgetName]['vars'][$varName]['value'], $post['item']);
                                    } else {
                                        $more['widgets'][$widgetName]['vars'][$varName]['value'] = [$post['item']];
                                    }
                                } else {
                                    if (!empty($widgetVar['value']) && is_array($widgetVar['value']) && isset($widgetVar['value'][$itemIndex])) {
                                        $more['widgets'][$widgetName]['vars'][$varName]['value'][$itemIndex] = $post['item'];
                                    }
                                }
                            }

                        }
                    }
                }
            }

            if ($tab == 'block_widget') {
                $widget = $file->fillBlockWidgetValue($blockName, $widgetId);
                if (!empty($widget['vars']) && is_array($widget['vars'])) {
                    if (isset($widget['vars'][$varName])) {
                        $widgetVar = $widget['vars'][$varName];
                        if ($widgetVar['type'] == 'array') {
                            $messages = [];
                            $rules    = [];

                            foreach ($widgetVar['item'] as $widgetArrayVarItemKey => $widgetArrayVarItem) {
                                if (!empty($widgetArrayVarItem['rule'])) {
                                    $rules[$widgetArrayVarItemKey] = $this->_parseRules($widgetArrayVarItem['rule']);
                                }

                                if (!empty($widgetArrayVarItem['message'])) {
                                    foreach ($widgetArrayVarItem['message'] as $rule => $msg) {
                                        $messages[$widgetArrayVarItemKey . '.' . $rule] = $msg;
                                    }
                                }
                            }

                            $validate = new Validate();
                            $validate->rule($rules);
                            $validate->message($messages);
                            $result   = $validate->check($post['item']);
                            if (!$result) {
                                $this->error($validate->getError());
                            }

                            if ($itemIndex === '') {
                                if (!empty($widgetVar['value']) && is_array($widgetVar['value'])) {
                                    array_push($more['widgets_blocks'][$blockName]['widgets'][$widgetId]['vars'][$varName], $post['item']);
                                } else {
                                    $more['widgets_blocks'][$blockName]['widgets'][$widgetId]['vars'][$varName] = [$post['item']];
                                }
                            } else {
                                if (!empty($widgetVar['value']) && is_array($widgetVar['value']) && isset($widgetVar['value'][$itemIndex])) {
                                    $more['widgets_blocks'][$blockName]['widgets'][$widgetId]['vars'][$varName][$itemIndex] = $post['item'];
                                }
                            }
                        }

                    }
                }
            }

            $more = json_encode($more);
            ThemeFileModel::where('id', $fileId)->update(['more' => $more]);
            cmf_clear_cache();
            $this->success(lang('EDIT_SUCCESS'), url('Theme/fileArrayData', ['tab' => $tab, 'var' => $varName, 'file_id' => $fileId, 'widget' => $widgetName, 'widget_id' => $widgetId, 'block_name' => $blockName]));

        }

    }

    /**
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/theme/file/var/array",
     *     summary="删除模板文件模板变量数组数据",
     *     description="删除模板文件模板变量数组数据",
     *     @OA\Parameter(
     *         name="file_id",
     *         in="query",
     *         example="1",
     *         description="模板文件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="var",
     *         in="query",
     *         example="var1",
     *         description="变量名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="item_index",
     *         in="query",
     *         example="0",
     *         description="变量索引",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "items":{
     *                  {"title": "xxxx","content": "xxxxxxx"}
     *              },
     *              "item":{"title": {"title": "标题","value": "","type": "text","rule": {"require": true}},"content": {"title": "内容","value": "","type": "text"}}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/theme/file/widget/array",
     *     summary="获取模板文件控件数组数据",
     *     description="获取模板文件控件数组数据",
     *     @OA\Parameter(
     *         name="file_id",
     *         in="query",
     *         example="1",
     *         description="模板文件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="widget",
     *         in="query",
     *         example="widget1",
     *         description="控件名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="var",
     *         in="query",
     *         example="var1",
     *         description="变量名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="item_index",
     *         in="query",
     *         example="0",
     *         description="变量索引",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "items":{
     *                  {"title": "xxxx","content": "xxxxxxx"}
     *              },
     *              "item":{"title": {"title": "标题","value": "","type": "text","rule": {"require": true}},"content": {"title": "内容","value": "","type": "text"}}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/theme/file/block/widget/array",
     *     summary="获取模板文件块控件数组数据",
     *     description="获取模板文件块控件数组数据",
     *     @OA\Parameter(
     *         name="file_id",
     *         in="query",
     *         example="1",
     *         description="模板文件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="block_name",
     *         in="query",
     *         example="block1",
     *         description="块名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="widget_id",
     *         in="query",
     *         example="xxxxxx",
     *         description="自由控件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="var",
     *         in="query",
     *         example="var1",
     *         description="变量名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="item_index",
     *         in="query",
     *         example="0",
     *         description="变量索引",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "items":{
     *                  {"title": "xxxx","content": "xxxxxxx"}
     *              },
     *              "item":{"title": {"title": "标题","value": "","type": "text","rule": {"require": true}},"content": {"title": "内容","value": "","type": "text"}}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function fileArrayDataDelete()
    {
        if (!$this->request->isDelete()) {
            $this->error(lang('illegal request'));
        }
        $tab        = $this->request->param('tab', 'widget');
        $varName    = $this->request->param('var');
        $widgetName = $this->request->param('widget', '');
        $fileId     = $this->request->param('file_id', 0, 'intval');
        $widgetId   = $this->request->param('widget_id', ''); //自由控件编辑
        $blockName  = $this->request->param('block_name', '');//自由控件编辑
        $itemIndex  = $this->request->param('item_index', '');

        if ($itemIndex === '') {
            $this->error('未指定删除元素!');
        }

        $file = ThemeFileModel::where('id', $fileId)->find();

        $more = $file['more'];
        if ($tab == 'var') {
            foreach ($more['vars'] as $mVarName => $mVar) {

                if ($mVarName == $varName && $mVar['type'] == 'array') {
                    if (!empty($mVar['value']) && is_array($mVar['value']) && isset($mVar['value'][$itemIndex])) {
                        array_splice($more['vars'][$mVarName]['value'], $itemIndex, 1);
                    } else {
                        $this->error('指定数据不存在!');
                    }
                    break;
                }
            }
        }

        if ($tab == 'widget') {
            foreach ($more['widgets'] as $mWidgetName => $widget) {
                if ($mWidgetName == $widgetName) {
                    if (!empty($widget['vars']) && is_array($widget['vars'])) {
                        foreach ($widget['vars'] as $widgetVarName => $widgetVar) {
                            if ($widgetVarName == $varName && $widgetVar['type'] == 'array') {
                                if (!empty($widgetVar['value']) && is_array($widgetVar['value']) && isset($widgetVar['value'][$itemIndex])) {
                                    array_splice($more['widgets'][$widgetName]['vars'][$widgetVarName]['value'], $itemIndex, 1);
                                } else {
                                    $this->error('指定数据不存在!');
                                }
                                break;
                            }
                        }
                    }
                    break;
                }
            }
        }

        if ($tab == 'block_widget') {
            $widget = $file->fillBlockWidgetValue($blockName, $widgetId);
            if (!empty($widget['vars']) && is_array($widget['vars'])) {
                if (isset($widget['vars'][$varName])) {
                    $widgetVar = $widget['vars'][$varName];
                    if ($widgetVar['type'] == 'array') {
                        if ($itemIndex !== '') {
                            if (!empty($widgetVar['value']) && is_array($widgetVar['value']) && isset($widgetVar['value'][$itemIndex])) {
                                array_splice($more['widgets_blocks'][$blockName]['widgets'][$widgetId]['vars'][$varName], $itemIndex, 1);
                            }
                        }
                    }

                }
            }
        }

        $more = json_encode($more);
        ThemeFileModel::where('id', $fileId)->update(['more' => $more]);
        cmf_clear_cache();
        $this->success(lang('DELETE_SUCCESS'));
    }


}
