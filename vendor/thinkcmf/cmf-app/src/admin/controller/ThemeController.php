<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\ThemeFileModel;
use cmf\controller\AdminBaseController;
use app\admin\model\ThemeModel;
use think\Validate;
use tree\Tree;

class ThemeController extends AdminBaseController
{
    /**
     * 模板管理
     * @adminMenu(
     *     'name'   => '模板管理',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 20,
     *     'icon'   => '',
     *     'remark' => '模板管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        $themeModel = new ThemeModel();
        $themes     = $themeModel->select();
        $this->assign("themes", $themes);

        $defaultTheme = config('template.cmf_default_theme');
        if ($temp = session('cmf_default_theme')) {
            $defaultTheme = $temp;
        }
        $this->assign('default_theme', $defaultTheme);
        return $this->fetch();
    }

    /**
     * 安装模板
     * @adminMenu(
     *     'name'   => '安装模板',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '安装模板',
     *     'param'  => ''
     * )
     */
    public function install()
    {
        $themesDirs = cmf_scan_dir("themes/*", GLOB_ONLYDIR);

        $themeModel = new ThemeModel();

        $themesInstalled = $themeModel->column('theme');

        $themesDirs = array_diff($themesDirs, $themesInstalled);

        $themes = [];
        foreach ($themesDirs as $dir) {
            $manifest = "themes/$dir/manifest.json";
            if (file_exists_case($manifest)) {
                $manifest       = file_get_contents($manifest);
                $theme          = json_decode($manifest, true);
                $theme['theme'] = $dir;
                array_push($themes, $theme);
            }
        }
        $this->assign('themes', $themes);

        return $this->fetch();
    }

    /**
     * 卸载模板
     * @adminMenu(
     *     'name'   => '卸载模板',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '卸载模板',
     *     'param'  => ''
     * )
     */
    public function uninstall()
    {
        if ($this->request->isPost()) {
            $theme = $this->request->param('theme');
            if ($theme == "simpleboot3" || config('template.cmf_default_theme') == $theme) {
                $this->error("官方自带模板或当前使用中的模板不可以卸载");
            }

            $themeModel = new ThemeModel();
            $themeModel->transaction(function () use ($theme, $themeModel) {
                $themeModel->where('theme', $theme)->delete();
                ThemeFileModel::where('theme', $theme)->delete();
            });

            $this->success("卸载成功", url("Theme/index"));

        }
    }

    /**
     * 模板安装
     * @adminMenu(
     *     'name'   => '模板安装',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板安装',
     *     'param'  => ''
     * )
     */
    public function installTheme()
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
            $this->success("安装成功", url("Theme/index"));
        }
    }

    /**
     * 模板更新
     * @adminMenu(
     *     'name'   => '模板更新',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板更新',
     *     'param'  => ''
     * )
     */
    public function update()
    {
        if ($this->request->isPost()) {
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
            $this->success("更新成功");
        }
    }

    /**
     * 启用模板
     * @adminMenu(
     *     'name'   => '启用模板',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '启用模板',
     *     'param'  => ''
     * )
     */
    public function active()
    {
        if ($this->request->isPost()) {
            $theme = $this->request->param('theme');

            if ($theme == config('template.cmf_default_theme')) {
                $this->error('模板已启用', url("theme/index"));
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

            $this->success("模板启用成功", url("Theme/index"));
        }
    }

    /**
     * 模板文件列表
     * @adminMenu(
     *     'name'   => '模板文件列表',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '启用模板',
     *     'param'  => ''
     * )
     */
    public function files()
    {
        $theme = $this->request->param('theme');
        $files = ThemeFileModel::where('theme', $theme)->order('list_order ASC')->select();
        $this->assign('files', $files);
        return $this->fetch();
    }

    /**
     * 模板文件设置
     * @adminMenu(
     *     'name'   => '模板文件设置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板文件设置',
     *     'param'  => ''
     * )
     */
    public function fileSetting()
    {
        $tab    = $this->request->param('tab', 'widget');
        $fileId = $this->request->param('file_id', 0, 'intval');
        if (empty($fileId)) {
            $file  = $this->request->param('file');
            $theme = $this->request->param('theme');
            $files = ThemeFileModel::where('theme', $theme)
                ->where(function ($query) use ($file) {
                    $query->where('is_public', 1)->whereOr('file', $file);
                })->order('list_order ASC')->select();
            $file  = ThemeFileModel::where(['file' => $file, 'theme' => $theme])->find();

        } else {
            $file  = ThemeFileModel::where('id', $fileId)->find();
            $files = ThemeFileModel::where('theme', $file['theme'])
                ->where(function ($query) use ($fileId) {
                    $query->where('id', $fileId)->whereOr('is_public', 1);
                })->order('list_order ASC')->select();
        }

        $tpl     = 'file_widget_setting';
        $hasFile = false;
        if (!empty($file)) {
            $hasFile = true;
            $fileId  = $file['id'];

            $hasPublicVar = false;
            $hasWidget    = false;
            foreach ($files as $key => $mFile) {
                if (!empty($mFile['is_public']) && !empty($mFile['more']['vars'])) {
                    $hasPublicVar = true;
                }

                if (!empty($mFile['more']['widgets'])) {
                    $hasWidget = true;
                }

                $files[$key] = $mFile;
            }

            $this->assign('tab', $tab);
            $this->assign('files', $files);
            $this->assign('file', $file);
            $this->assign('file_id', $fileId);
            $this->assign('has_public_var', $hasPublicVar);
            $this->assign('has_widget', $hasWidget);

            if ($tab == 'var') {
                $tpl = 'file_var_setting';
            } else if ($tab == 'public_var') {
                $tpl = 'file_public_var_setting';
            }

        }
        $this->assign('has_file', $hasFile);
        return $this->fetch($tpl);
    }

    /**
     * 模板文件数组数据列表
     * @adminMenu(
     *     'name'   => '模板文件数组数据列表',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板文件数组数据列表',
     *     'param'  => ''
     * )
     */
    public function fileArrayData()
    {
        $tab        = $this->request->param('tab', 'widget');
        $varName    = $this->request->param('var');
        $widgetName = $this->request->param('widget', '');
        $fileId     = $this->request->param('file_id', 0, 'intval');
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

        $this->assign('tab', $tab);
        $this->assign('var', $varName);
        $this->assign('widget', $widgetName);
        $this->assign('file_id', $fileId);
        $this->assign('array_items', $items);
        $this->assign('array_item', $item);

        return $this->fetch('file_array_data');
    }

    /**
     * 模板文件数组数据添加编辑
     * @adminMenu(
     *     'name'   => '模板文件数组数据添加编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板文件数组数据添加编辑',
     *     'param'  => ''
     * )
     */
    public function fileArrayDataEdit()
    {
        $tab        = $this->request->param('tab', 'widget');
        $varName    = $this->request->param('var');
        $widgetName = $this->request->param('widget', '');
        $fileId     = $this->request->param('file_id', 0, 'intval');
        $itemIndex  = $this->request->param('item_index', '');

        $file = ThemeFileModel::where('id', $fileId)->find();

        $oldMore = $file['more'];

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

        if ($tab == 'widget') {

            if (empty($widgetName)) {
                $this->error('未指定控件!');
            }

            if (!empty($oldMore['widgets']) && is_array($oldMore['widgets'])) {
                foreach ($oldMore['widgets'] as $mWidgetName => $widget) {
                    if ($mWidgetName == $widgetName) {
                        if (!empty($widget['vars']) && is_array($widget['vars'])) {
                            foreach ($widget['vars'] as $widgetVarName => $widgetVar) {
                                if ($widgetVarName == $varName && $widgetVar['type'] == 'array') {

                                    if (is_array($widgetVar['value'])) {
                                        $items = $widgetVar['value'];
                                    }

                                    if (isset($widgetVar['item'])) {
                                        $item = $widgetVar['item'];
                                    }

                                    break;
                                }
                            }
                        }
                        break;
                    }

                }
            }
        }

        if ($itemIndex !== '') {
            $itemIndex = intval($itemIndex);
            if (!isset($items[$itemIndex])) {
                $this->error('数据不存在!');
            }

            foreach ($item as $itemName => $vo) {
                if (isset($items[$itemIndex][$itemName])) {
                    $item[$itemName]['value'] = $items[$itemIndex][$itemName];
                }
            }
        }

        $this->assign('tab', $tab);
        $this->assign('var', $varName);
        $this->assign('widget', $widgetName);
        $this->assign('file_id', $fileId);
        $this->assign('array_items', $items);
        $this->assign('array_item', $item);
        $this->assign('item_index', $itemIndex);

        return $this->fetch('file_array_data_edit');
    }

    /**
     * 模板文件数组数据添加编辑提交保存
     * @adminMenu(
     *     'name'   => '模板文件数组数据添加编辑提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板文件数组数据添加编辑提交保存',
     *     'param'  => ''
     * )
     */
    public function fileArrayDataEditPost()
    {
        if (!$this->request->isPost()) {
            $this->error('非法请求！');
        }
        $tab        = $this->request->param('tab', 'widget');
        $varName    = $this->request->param('var');
        $widgetName = $this->request->param('widget', '');
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

                        $validate = new Validate($rules, $messages);
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

                                $validate = new Validate($rules, $messages);
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

            $more = json_encode($more);
            ThemeFileModel::where('id', $fileId)->update(['more' => $more]);

            $this->success("保存成功！", url('theme/fileArrayData', ['tab' => $tab, 'var' => $varName, 'file_id' => $fileId, 'widget' => $widgetName]));

        }

    }

    /**
     * 模板文件数组数据删除
     * @adminMenu(
     *     'name'   => '模板文件数组数据删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板文件数组数据删除',
     *     'param'  => ''
     * )
     */
    public function fileArrayDataDelete()
    {
        if (!$this->request->isPost()) {
            $this->error('非法请求！');
        }
        $tab        = $this->request->param('tab', 'widget');
        $varName    = $this->request->param('var');
        $widgetName = $this->request->param('widget', '');
        $fileId     = $this->request->param('file_id', 0, 'intval');
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

        $more = json_encode($more);
        ThemeFileModel::where('id', $fileId)->update(['more' => $more]);

        $this->success("删除成功！", url('theme/fileArrayData', ['tab' => $tab, 'var' => $varName, 'file_id' => $fileId, 'widget' => $widgetName]));
    }

    /**
     * 模板文件编辑提交保存
     * @adminMenu(
     *     'name'   => '模板文件编辑提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板文件编辑提交保存',
     *     'param'  => ''
     * )
     */
    public function settingPost()
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

                        $validate = new Validate($rules, $messages);
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
                                $validate   = new Validate($rules, $messages);
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
            $this->success("保存成功！", '');
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
     * 模板文件设置数据源
     * @adminMenu(
     *     'name'   => '模板文件设置数据源',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板文件设置数据源',
     *     'param'  => ''
     * )
     */
    public function dataSource()
    {
        $dataSource = $this->request->param('data_source');
        $this->assign('data_source', $dataSource);

        $ids         = $this->request->param('ids');
        $selectedIds = [];

        if (!empty($ids)) {
            $selectedIds = explode(',', $ids);
        }

        if (empty($dataSource)) {
            $this->error('数据源不能为空!');
        }

        $dataSource = json_decode(base64_decode($dataSource), true);

        if ($dataSource === null || !isset($dataSource['api'])) {
            $this->error('数据源格式不正确!');
        }

        $filters = [];
        if (isset($dataSource['filters']) && is_array($dataSource['filters'])) {
            $filters = $dataSource['filters'];

            foreach ($filters as $key => $filter) {
                if ($filter['type'] == 'select' && !empty($filter['api'])) {
                    $filterData = [];
                    try {
                        $filterData = action($filter['api'], [], 'api');
                        if (!is_array($filterData)) {
                            $filterData = $filterData->toArray();
                        }
                    } catch (\Exception $e) {

                    }

                    if (empty($filterData)) {
                        $filters[$key] = null;
                    } else {
                        $filters[$key]['options'] = $filterData;
                    }
                }
            }

            if (count($filters) > 3) {
                $filters = array_slice($filters, 0, 3);
            }
        }

        $vars = [];

        if ($this->request->isPost()) {
            $form    = $this->request->param();
            $vars[0] = $form;
            $this->assign('form', $form);
        }

        $items = action($dataSource['api'], $vars, 'api');

        if ($items instanceof \think\Collection) {
            $items = $items->toArray();
        }

        $multi = empty($dataSource['multi']) ? false : $dataSource['multi'];

        foreach ($items as $key => $item) {
            if (empty($item['parent_id'])) {
                $item['parent_id'] = 0;
            }
            $item['checked'] = in_array($item['id'], $selectedIds) ? 'checked' : '';
            $items[$key]     = $item;
        }

        $tree = new Tree();
        $tree->init($items);

        $tpl = "<tr class='data-item-tr'>
					<td>
                        <input type='radio' class='js-select-box' 
                           name='ids[]'
                           value='\$id' data-name='\$name' \$checked>
					</td>
					<td>\$id</td>
					<td>\$spacer \$name</td>
				</tr>";
        if ($multi) {
            $tpl = "<tr class='data-item-tr'>
					<td>
					    <input type='checkbox' class='js-check js-select-box' data-yid='js-check-y'
                                   data-xid='js-check-x'
                                   name='ids[]'
                                   value='\$id' data-name='\$name' \$checked>
					</td>
					<td>\$id</td>
					<td>\$spacer \$name</td>
				</tr>";
        }

        $itemsTree = $tree->getTree(0, $tpl);
        $this->assign('multi', $multi);
        $this->assign('items_tree', $itemsTree);
        $this->assign('selected_ids', $selectedIds);
        $this->assign('filters', $filters);
        return $this->fetch();

    }

    /**
     * 模板设计
     * @adminMenu(
     *     'name'   => '模板设计',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模板设计',
     *     'param'  => ''
     * )
     */
    public function design()
    {
        $theme = $this->request->param('theme');
        cookie('cmf_design_theme', $theme, 3);
        if ($this->request->isAjax()) {
            $this->success('success');
        } else {
            $content = hook_one('admin_theme_design_view');
            if (empty($content)) {
                $content = $this->fetch();
            }
            return $content;
        }
    }

}
