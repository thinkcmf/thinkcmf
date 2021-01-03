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

use app\admin\logic\PluginLogic;
use cmf\controller\AdminBaseController;
use app\admin\model\PluginModel;
use app\admin\model\HookPluginModel;
use mindplay\annotations\Annotations;
use think\facade\Cache;
use think\Validate;

/**
 * Class PluginController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   =>'插件中心',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 20,
 *     'icon'   =>'cloud',
 *     'remark' =>'插件中心'
 * )
 */
class PluginController extends AdminBaseController
{

    protected $pluginModel;

    /**
     * 插件列表
     * @adminMenu(
     *     'name'   => '插件列表',
     *     'parent' => 'admin/Plugin/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '插件列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $pluginModel = new PluginModel();
        $plugins     = $pluginModel->getList();
        $this->assign("plugins", $plugins);
        return $this->fetch();
    }

    /**
     * 插件启用/禁用
     * @adminMenu(
     *     'name'   => '插件启用禁用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '插件启用禁用',
     *     'param'  => ''
     * )
     */
    public function toggle()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');

            $pluginModel = PluginModel::find($id);

            if (empty($pluginModel)) {
                $this->error('插件不存在！');
            }

            $status         = 1;
            $successMessage = "启用成功！";

            if ($this->request->param('disable')) {
                $status         = 0;
                $successMessage = "禁用成功！";
            }

            $pluginModel->startTrans();

            try {
                $pluginModel->save(['status' => $status]);

                $hookPluginModel = new HookPluginModel();

                $hookPluginModel->where(['plugin' => $pluginModel->name])->update(['status' => $status]);

                $pluginModel->commit();

            } catch (\Exception $e) {

                $pluginModel->rollback();

                $this->error('操作失败！');

            }

            Cache::clear('init_hook_plugins');

            $this->success($successMessage);
        }
    }

    /**
     * 插件设置
     * @adminMenu(
     *     'name'   => '插件设置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '插件设置',
     *     'param'  => ''
     * )
     */
    public function setting()
    {
        $id = $this->request->param('id', 0, 'intval');

        $pluginModel = new PluginModel();
        $plugin      = $pluginModel->find($id);

        if (empty($plugin)) {
            $this->error('插件未安装!');
        }

        $plugin = $plugin->toArray();

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
                    if (isset($pluginConfigInDb[$key])) {
                        $plugin['config'][$key]['value'] = $pluginConfigInDb[$key];
                    }
                } else {
                    foreach ($value['options'] as $group => $options) {
                        foreach ($options['options'] as $gkey => $value) {
                            if (isset($pluginConfigInDb[$gkey])) {
                                $plugin['config'][$key]['options'][$group]['options'][$gkey]['value'] = $pluginConfigInDb[$gkey];
                            }
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

    /**
     * 插件设置提交
     * @adminMenu(
     *     'name'   => '插件设置提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '插件设置提交',
     *     'param'  => ''
     * )
     */
    public function settingPost()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');

            $pluginModel = new PluginModel();
            $plugin      = $pluginModel->find($id)->toArray();

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

            $rules    = [];
            $messages = [];

            foreach ($plugin['config'] as $key => $value) {
                if ($value['type'] != 'group') {
                    if (isset($value['rule'])) {
                        $rules[$key] = $this->_parseRules($value['rule']);
                    }

                    if (isset($value['message'])) {
                        foreach ($value['message'] as $rule => $msg) {
                            $messages[$key . '.' . $rule] = $msg;
                        }
                    }

                } else {
                    foreach ($value['options'] as $group => $options) {
                        foreach ($options['options'] as $gkey => $value) {
                            if (isset($value['rule'])) {
                                $rules[$gkey] = $this->_parseRules($value['rule']);
                            }

                            if (isset($value['message'])) {
                                foreach ($value['message'] as $rule => $msg) {
                                    $messages[$gkey . '.' . $rule] = $msg;
                                }
                            }
                        }
                    }
                }
            }

            $config = $this->request->param('config/a');

            $validate = new Validate($rules, $messages);
            $result   = $validate->check($config);
            if ($result !== true) {
                $this->error($validate->getError());
            }

            $pluginModel = PluginModel::where('id', $id)->find();
            $pluginModel->save(['config' => json_encode($config)]);
            $this->success('保存成功', '');
        }
    }

    /**
     * 解析插件配置验证规则
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
     * 插件安装
     * @adminMenu(
     *     'name'   => '插件安装',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '插件安装',
     *     'param'  => ''
     * )
     */
    public function install()
    {
        if ($this->request->isPost()) {
            $pluginName = $this->request->param('name', '', 'trim');
            $result     = PluginLogic::install($pluginName);

            if ($result !== true) {
                $this->error($result);
            }

            $this->success('安装成功!');
        }
    }

    /**
     * 插件更新
     * @adminMenu(
     *     'name'   => '插件更新',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '插件更新',
     *     'param'  => ''
     * )
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $pluginName = $this->request->param('name', '', 'trim');
            $result     = PluginLogic::update($pluginName);

            if ($result !== true) {
                $this->error($result);
            }
            $this->success('更新成功!');
        }
    }

    /**
     * 卸载插件
     * @adminMenu(
     *     'name'   => '卸载插件',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '卸载插件',
     *     'param'  => ''
     * )
     */
    public function uninstall()
    {
        if ($this->request->isPost()) {
            $pluginModel = new PluginModel();
            $id          = $this->request->param('id', 0, 'intval');

            $result = $pluginModel->uninstall($id);

            if ($result !== true) {
                $this->error('卸载失败!');
            }

            Cache::clear('init_hook_plugins');
            Cache::clear('admin_menus');// 删除后台菜单缓存

            $this->success('卸载成功!');
        }
    }


}
