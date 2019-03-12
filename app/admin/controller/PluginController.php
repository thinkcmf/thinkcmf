<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\PluginModel;
use app\admin\model\HookPluginModel;
use mindplay\annotations\Annotations;
use think\Db;
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
        $id = $this->request->param('id', 0, 'intval');

        $pluginModel = PluginModel::get($id);

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
            $pluginModel->save(['status' => $status], ['id' => $id]);

            $hookPluginModel = new HookPluginModel();

            $hookPluginModel->save(['status' => $status], ['plugin' => $pluginModel->name]);

            $pluginModel->commit();

        } catch (\Exception $e) {

            $pluginModel->rollback();

            $this->error('操作失败！');

        }

        Cache::clear('init_hook_plugins');

        $this->success($successMessage);
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

            $pluginModel = new PluginModel();
            $pluginModel->save(['config' => json_encode($config)], ['id' => $id]);
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
        $pluginName = $this->request->param('name', '', 'trim');
        $class      = cmf_get_plugin_class($pluginName);
        if (!class_exists($class)) {
            $this->error('插件不存在!');
        }

        $pluginModel = new PluginModel();
        $pluginCount = $pluginModel->where('name', $pluginName)->count();

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

        $methods = get_class_methods($plugin);

        foreach ($methods as $methodKey => $method) {
            $methods[$methodKey] = cmf_parse_name($method);
        }

        $systemHooks = $pluginModel->getHooks(true);

        $pluginHooks = array_intersect($systemHooks, $methods);

        //$info['hooks'] = implode(",", $pluginHooks);

        if (!empty($plugin->hasAdmin)) {
            $info['has_admin'] = 1;
        } else {
            $info['has_admin'] = 0;
        }

        $info['config'] = json_encode($plugin->getConfig());

        $pluginModel->data($info)->allowField(true)->save();

        $hookPluginModel = new HookPluginModel();
        foreach ($pluginHooks as $pluginHook) {
            $hookPluginModel->data(['hook' => $pluginHook, 'plugin' => $pluginName, 'status' => 1])->isUpdate(false)->save();
        }

        $this->_getActions($pluginName);

        Cache::clear('init_hook_plugins');
        Cache::clear('admin_menus');// 删除后台菜单缓存

        $this->success('安装成功!');
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

        foreach ($methods as $methodKey => $method) {
            $methods[$methodKey] = cmf_parse_name($method);
        }

        $pluginModel = new PluginModel();
        $systemHooks = $pluginModel->getHooks(true);

        $pluginHooks = array_intersect($systemHooks, $methods);

        if (!empty($plugin->hasAdmin)) {
            $info['has_admin'] = 1;
        } else {
            $info['has_admin'] = 0;
        }

        $config = $plugin->getConfig();

        $defaultConfig = $plugin->getDefaultConfig();

        $pluginModel = new PluginModel();

        $config = array_merge($defaultConfig, $config);

        $info['config'] = json_encode($config);

        $pluginModel->allowField(true)->save($info, ['name' => $pluginName]);

        $hookPluginModel = new HookPluginModel();

        $pluginHooksInDb = $hookPluginModel->where('plugin', $pluginName)->column('hook');

        $samePluginHooks = array_intersect($pluginHooks, $pluginHooksInDb);

        $shouldDeleteHooks = array_diff($samePluginHooks, $pluginHooksInDb);

        $newHooks = array_diff($pluginHooks, $samePluginHooks);

        if (count($shouldDeleteHooks) > 0) {
            $hookPluginModel->where('hook', 'in', $shouldDeleteHooks)->delete();
        }

        foreach ($newHooks as $pluginHook) {
            $hookPluginModel->data(['hook' => $pluginHook, 'plugin' => $pluginName])->isUpdate(false)->save();
        }

        $this->_getActions($pluginName);

        Cache::clear('init_hook_plugins');
        Cache::clear('admin_menus');// 删除后台菜单缓存

        $this->success('更新成功!');
    }

    private function _getActions($pluginName)
    {
        Annotations::$config['cache']                 = false;
        $annotationManager                            = Annotations::getManager();
        $annotationManager->registry['adminMenu']     = 'app\admin\annotation\AdminMenuAnnotation';
        $annotationManager->registry['adminMenuRoot'] = 'app\admin\annotation\AdminMenuRootAnnotation';
        $newMenus                                     = [];

        $pluginDir = cmf_parse_name($pluginName);

        $filePatten = WEB_ROOT . 'plugins/' . $pluginDir . '/controller/Admin*Controller.php';

        $controllers = cmf_scan_dir($filePatten);

        $app = 'plugin/' . $pluginName;

        if (!empty($controllers)) {
            foreach ($controllers as $controller) {
                $controller      = preg_replace('/\.php$/', '', $controller);
                $controllerName  = preg_replace('/\Controller$/', '', $controller);
                $controllerClass = "plugins\\$pluginDir\\controller\\$controller";

                $menuAnnotations = Annotations::ofClass($controllerClass, '@adminMenuRoot');

                if (!empty($menuAnnotations)) {
                    foreach ($menuAnnotations as $menuAnnotation) {

                        $name      = $menuAnnotation->name;
                        $icon      = $menuAnnotation->icon;
                        $type      = 0;//1:有界面可访问菜单,2:无界面可访问菜单,0:只作为菜单
                        $action    = $menuAnnotation->action;
                        $status    = empty($menuAnnotation->display) ? 0 : 1;
                        $listOrder = floatval($menuAnnotation->order);
                        $param     = $menuAnnotation->param;
                        $remark    = $menuAnnotation->remark;

                        if (empty($menuAnnotation->parent)) {
                            $parentId = 0;
                        } else {

                            $parent      = explode('/', $menuAnnotation->parent);
                            $countParent = count($parent);
                            if ($countParent > 3) {
                                throw new \Exception($controllerClass . ':' . $action . '  @adminMenuRoot parent格式不正确!');
                            }

                            $parentApp        = $app;
                            $parentController = $controllerName;
                            $parentAction     = '';

                            switch ($countParent) {
                                case 1:
                                    $parentAction = $parent[0];
                                    break;
                                case 2:
                                    $parentController = $parent[0];
                                    $parentAction     = $parent[1];
                                    break;
                                case 3:
                                    $parentApp        = $parent[0];
                                    $parentController = $parent[1];
                                    $parentAction     = $parent[2];
                                    break;
                            }

                            $findParentAdminMenu = Db::name('admin_menu')->where([
                                'app'        => $parentApp,
                                'controller' => $parentController,
                                'action'     => $parentAction
                            ])->find();

                            if (empty($findParentAdminMenu)) {
                                $parentId = Db::name('admin_menu')->insertGetId([
                                    'app'        => $parentApp,
                                    'controller' => $parentController,
                                    'action'     => $parentAction,
                                    'name'       => '--new--'
                                ]);
                            } else {
                                $parentId = $findParentAdminMenu['id'];
                            }
                        }

                        $findAdminMenu = Db::name('admin_menu')->where([
                            'app'        => $app,
                            'controller' => $controllerName,
                            'action'     => $action
                        ])->find();

                        if (empty($findAdminMenu)) {

                            Db::name('admin_menu')->insert([
                                'parent_id'  => $parentId,
                                'type'       => $type,
                                'status'     => $status,
                                'list_order' => $listOrder,
                                'app'        => $app,
                                'controller' => $controllerName,
                                'action'     => $action,
                                'param'      => $param,
                                'name'       => $name,
                                'icon'       => $icon,
                                'remark'     => $remark
                            ]);

                            $menuName = $name;

//                            array_push($newMenus, $app . "/$controllerName/$action 已导入");

                        } else {

                            if ($findAdminMenu['name'] == '--new--') {
                                Db::name('admin_menu')->where([
                                    'app'        => $app,
                                    'controller' => $controllerName,
                                    'action'     => $action
                                ])->update([
                                    'parent_id'  => $parentId,
                                    'type'       => $type,
                                    'status'     => $status,
                                    'list_order' => $listOrder,
                                    'param'      => $param,
                                    'name'       => $name,
                                    'icon'       => $icon,
                                    'remark'     => $remark
                                ]);
                                $menuName = $name;
                            } else {
                                // 只关注菜单层级关系,是否有视图
                                Db::name('admin_menu')->where([
                                    'app'        => $app,
                                    'controller' => $controllerName,
                                    'action'     => $action
                                ])->update([
                                    //'parent_id' => $parentId,
                                    'type' => $type,
                                ]);
                                $menuName = $findAdminMenu['name'];
                            }

//                            array_push($newMenus, $app."/$controllerName/$action 层级关系已更新");
                        }

                        $authRuleName      = "plugin/{$pluginName}/{$controllerName}/{$action}";
                        $findAuthRuleCount = Db::name('auth_rule')->where([
                            'app'  => $app,
                            'name' => $authRuleName,
                            'type' => 'admin_url'
                        ])->count();

                        if ($findAuthRuleCount == 0) {
                            Db::name('auth_rule')->insert([
                                'app'   => $app,
                                'name'  => $authRuleName,
                                'type'  => 'admin_url',
                                'param' => $param,
                                'title' => $menuName
                            ]);
                        } else {
                            Db::name('auth_rule')->where([
                                'app'  => $app,
                                'name' => $authRuleName,
                                'type' => 'admin_url',
                            ])->update([
                                'param' => $param,
                                'title' => $menuName
                            ]);
                        }

                    }
                }

                $reflect = new \ReflectionClass($controllerClass);
                $methods = $reflect->getMethods(\ReflectionMethod::IS_PUBLIC);

                if (!empty($methods)) {
                    foreach ($methods as $method) {

                        if ($method->class == $controllerClass && strpos($method->name, '_') !== 0) {
                            $menuAnnotations = Annotations::ofMethod($controllerClass, $method->name, '@adminMenu');

                            if (!empty($menuAnnotations)) {

                                $menuAnnotation = $menuAnnotations[0];

                                $name      = $menuAnnotation->name;
                                $icon      = $menuAnnotation->icon;
                                $type      = $menuAnnotation->hasView ? 1 : 2;//1:有界面可访问菜单,2:无界面可访问菜单,0:只作为菜单
                                $action    = $method->name;
                                $status    = empty($menuAnnotation->display) ? 0 : 1;
                                $listOrder = floatval($menuAnnotation->order);
                                $param     = $menuAnnotation->param;
                                $remark    = $menuAnnotation->remark;

                                if (empty($menuAnnotation->parent)) {
                                    $parentId = 0;
                                } else {
                                    $parent      = explode('/', $menuAnnotation->parent);
                                    $countParent = count($parent);
                                    if ($countParent > 3) {
                                        throw new \Exception($controllerClass . ':' . $action . '  @menuRoot parent格式不正确!');
                                    }

                                    $parentApp        = $app;
                                    $parentController = $controllerName;
                                    $parentAction     = '';

                                    switch ($countParent) {
                                        case 1:
                                            $parentAction = $parent[0];
                                            break;
                                        case 2:
                                            $parentController = $parent[0];
                                            $parentAction     = $parent[1];
                                            break;
                                        case 3:
                                            $parentApp        = $parent[0];
                                            $parentController = $parent[1];
                                            $parentAction     = $parent[2];
                                            break;
                                    }

                                    $findParentAdminMenu = Db::name('admin_menu')->where([
                                        'app'        => $parentApp,
                                        'controller' => $parentController,
                                        'action'     => $parentAction
                                    ])->find();

                                    if (empty($findParentAdminMenu)) {
                                        $parentId = Db::name('admin_menu')->insertGetId([
                                            'app'        => $parentApp,
                                            'controller' => $parentController,
                                            'action'     => $parentAction,
                                            'name'       => '--new--'
                                        ]);
                                    } else {
                                        $parentId = $findParentAdminMenu['id'];
                                    }
                                }

                                $findAdminMenu = Db::name('admin_menu')->where([
                                    'app'        => $app,
                                    'controller' => $controllerName,
                                    'action'     => $action
                                ])->find();

                                if (empty($findAdminMenu)) {

                                    Db::name('admin_menu')->insert([
                                        'parent_id'  => $parentId,
                                        'type'       => $type,
                                        'status'     => $status,
                                        'list_order' => $listOrder,
                                        'app'        => $app,
                                        'controller' => $controllerName,
                                        'action'     => $action,
                                        'param'      => $param,
                                        'name'       => $name,
                                        'icon'       => $icon,
                                        'remark'     => $remark
                                    ]);

                                    $menuName = $name;

                                    //array_push($newMenus, "$app/$controllerName/$action 已导入");

                                } else {
                                    if ($findAdminMenu['name'] == '--new--') {
                                        Db::name('admin_menu')->where([
                                            'app'        => $app,
                                            'controller' => $controllerName,
                                            'action'     => $action
                                        ])->update([
                                            'parent_id'  => $parentId,
                                            'type'       => $type,
                                            'status'     => $status,
                                            'list_order' => $listOrder,
                                            'param'      => $param,
                                            'name'       => $name,
                                            'icon'       => $icon,
                                            'remark'     => $remark
                                        ]);
                                        $menuName = $name;
                                    } else {
                                        // 只关注是否有视图
                                        Db::name('admin_menu')->where([
                                            'app'        => $app,
                                            'controller' => $controllerName,
                                            'action'     => $action
                                        ])->update([
                                            //'parent_id' => $parentId,
                                            'type' => $type,
                                        ]);
                                        $menuName = $findAdminMenu['name'];
                                    }


//                                    array_push($newMenus, "$app/$controllerName/$action 已更新");
                                }

                                $authRuleName      = "plugin/{$pluginName}/{$controllerName}/{$action}";
                                $findAuthRuleCount = Db::name('auth_rule')->where([
                                    'app'  => $app,
                                    'name' => $authRuleName,
                                    'type' => 'plugin_url'
                                ])->count();

                                if ($findAuthRuleCount == 0) {
                                    Db::name('auth_rule')->insert([
                                        'app'   => $app,
                                        'name'  => $authRuleName,
                                        'type'  => 'plugin_url',
                                        'param' => $param,
                                        'title' => $menuName
                                    ]);
                                } else {
                                    Db::name('auth_rule')->where([
                                        'app'  => $app,
                                        'name' => $authRuleName,
                                        'type' => 'plugin_url',
                                    ])->update([
                                        'param' => $param,
                                        'title' => $menuName
                                    ]);
                                }
                            }

                        }
                    }
                }

            }
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