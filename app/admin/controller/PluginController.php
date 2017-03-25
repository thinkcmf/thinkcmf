<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\PluginModel;
use app\admin\model\HookPluginModel;

class PluginController extends AdminBaseController
{

    protected $pluginModel;

    public function _initialize()
    {
        parent::_initialize();
    }

    // 后台插件列表
    public function index()
    {
        $this->pluginModel = new PluginModel();
        $plugins           = $this->pluginModel->getList();
        $this->assign("plugins", $plugins);
        return $this->fetch();
    }

    // 插件启用/禁用
    public function toggle()
    {
        $this->pluginModel = new PluginModel();

        if ($this->request->param('enable')) {
            $id = $this->request->param('id', 0, 'intval');

            $this->pluginModel->save(['status' => 1], ['id' => $id]);

            $this->success("启用成功！");
        }

        if ($this->request->param('disable')) {
            $id = $this->request->param('id', 0, 'intval');

            $this->pluginModel->save(['status' => 0], ['id' => $id]);

            $this->success("禁用成功！");
        }
    }

    // 插件设置
    public function setting()
    {
        $id = $this->request->param('id', 0, 'intval');

        $this->pluginModel = new PluginModel();
        $plugin            = $this->pluginModel->find($id)->toArray();

        if (!$plugin) {
            $this->error('插件未安装!');
        }

        $pluginClass = cmf_get_plugin_class($plugin['name']);
        if (!class_exists($pluginClass)) {
            $this->error('插件不存在!');
        }

        $pluginObj = new $pluginClass;
        //$plugin['plugin_path']   = $pluginObj->plugin_path;
        //$plugin['custom_config'] = $pluginObj->custom_config;
        $pluginConfigInDb = $plugin['config'];
        $plugin['config'] = include $pluginObj->getConfigFilePath();
        if ($pluginConfigInDb) {
            $pluginConfigInDb = json_decode($pluginConfigInDb, true);
            foreach ($plugin['config'] as $key => $value) {
                if ($value['type'] != 'group') {
                    $plugin['config'][$key]['value'] = isset($pluginConfigInDb[$key]) ? $pluginConfigInDb[$key] : $value;
                } else {
                    foreach ($value['options'] as $gourp => $options) {
                        foreach ($options['options'] as $gkey => $value) {
                            $plugin['config'][$key]['options'][$gourp]['options'][$gkey]['value'] = isset($pluginConfigInDb[$gkey]) ? $pluginConfigInDb[$gkey] : $value;
                        }
                    }
                }
            }
        }
        $this->assign('data', $plugin);
//        if ($plugin['custom_config']) {
//            $this->assign('custom_config', $this->fetch($plugin['plugin_path'] . $plugin['custom_config']));
//        }

        $this->assign('id', $id);
        return $this->fetch();

    }

    // 插件设置提交
    public function settingPost()
    {
        if ($this->request->isPost()) {
            $id     = $this->request->param('id', 0, 'intval');
            $config = $this->request->param('config/a');

            $this->pluginModel = new PluginModel();
            $this->pluginModel->save(['config' => json_encode($config)], ['id' => $id]);
            $this->success('保存成功');
        }
    }

    // 插件安装
    public function install()
    {
        $pluginName = $this->request->param('name', '', 'trim');
        $class      = cmf_get_plugin_class($pluginName);
        if (!class_exists($class)) {
            $this->error('插件不存在!');
        }

        $this->pluginModel = new PluginModel();
        $pluginCount       = $this->pluginModel->where('name', $pluginName)->count();

        if ($pluginCount > 0) {
            $this->error('插件已安装!');
        }

        $plugin = new $class;
        $info   = $plugin->info;
        if (!$info || !$plugin->checkInfo()) {//检测信息的正确性
            $this->error('插件信息缺失!');
        }

        $installSuccess = $plugin->install();
        if (!$installSuccess) {
            $this->error('插件预安装失败!');
        }

        $methods     = get_class_methods($plugin);
        $systemHooks = cmf_get_hooks(true);

        $pluginHooks = array_intersect($systemHooks, $methods);

        $info['hooks'] = implode(",", $pluginHooks);

        if (!empty($plugin->hasAdmin)) {
            $info['has_admin'] = 1;
        } else {
            $info['has_admin'] = 0;
        }

        $info['config'] = json_encode($plugin->getConfig());

        $this->pluginModel->data($info)->allowField(true)->save();

        $hookPluginModel = new HookPluginModel();
        foreach ($pluginHooks as $pluginHook) {
            $hookPluginModel->data(['hook' => $pluginHook, 'plugin' => $pluginName])->isUpdate(false)->save();
        }

        $this->success('安装成功!');
    }

    // 插件更新
    public function update()
    {
        $pluginName = $this->request->param('name', '', 'trim');
        $class      = cmf_get_plugin_class($pluginName);
        if (!class_exists($class)) {
            $this->error('插件不存在!');
        }

        $plugin = new $class;
        $info   = $plugin->info;
        if (!$info || !$plugin->checkInfo()) {//检测信息的正确性
            $this->error('插件信息缺失!');
        }

        $methods = get_class_methods($plugin);

        $systemHooks = cmf_get_hooks(true);

        $pluginHooks = array_intersect($systemHooks, $methods);

        $info['hooks'] = implode(",", $pluginHooks);

        if (!empty($plugin->hasAdmin)) {
            $info['has_admin'] = 1;
        } else {
            $info['has_admin'] = 0;
        }

        $config = $plugin->getConfig();

        $defaultConfig = $plugin->getDefaultConfig();

        $this->pluginModel = new PluginModel();

        $config = array_merge($defaultConfig, $config);

        $info['config'] = json_encode($config);

        $this->pluginModel->allowField(true)->save($info, ['name' => $pluginName]);

        $this->success('更新成功!');
    }

    // 卸载插件
    public function uninstall()
    {
        $this->pluginModel = new PluginModel();
        $id                = $this->request->param('id', 0, 'intval');
        $findPlugin        = $this->pluginModel->find($id);
        $class             = cmf_get_plugin_class($findPlugin['name']);

        if (class_exists($class)) {
            $plugins = new $class;

            $uninstallSuccess = $plugins->uninstall();
            if (!$uninstallSuccess) {
                $this->error('插件预卸载失败');
            }
        }

        $this->pluginModel->where(['name' => $findPlugin['name']])->delete();
        $this->success('卸载成功');
    }


}