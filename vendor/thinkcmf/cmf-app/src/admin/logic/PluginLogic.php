<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\admin\logic;

use app\admin\model\AdminMenuModel;
use app\admin\model\AuthRuleModel;
use app\admin\model\HookPluginModel;
use app\admin\model\PluginModel;
use mindplay\annotations\Annotations;
use think\facade\Cache;

class PluginLogic
{
    /**
     * 安装应用
     */
    public static function install($pluginName)
    {
        $class = cmf_get_plugin_class($pluginName);
        if (!class_exists($class)) {
            return '插件不存在!';
        }

        $pluginModel = new PluginModel();
        $pluginCount = $pluginModel->where('name', $pluginName)->count();

        if ($pluginCount > 0) {
            return '插件已安装!';
        }

        $plugin = new $class;
        $info   = $plugin->info;
        if (!$info || !$plugin->checkInfo()) {//检测信息的正确性
            return '插件信息缺失!';
        }

        $installSuccess = $plugin->install();
        if (!$installSuccess) {
            return '插件预安装失败!';
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

        $pluginModel->save($info);

        foreach ($pluginHooks as $pluginHook) {
            $hookPluginModel = new HookPluginModel();
            $hookPluginModel->save(['hook' => $pluginHook, 'plugin' => $pluginName, 'status' => 1]);
        }

        self::getActions($pluginName);

        Cache::clear('init_hook_plugins');
        Cache::clear('admin_menus');// 删除后台菜单缓存

        return true;
    }

    public static function update($pluginName)
    {
        $class = cmf_get_plugin_class($pluginName);
        if (!class_exists($class)) {
            return '插件不存在!';
        }

        $plugin = new $class;
        $info   = $plugin->info;
        if (!$info || !$plugin->checkInfo()) {//检测信息的正确性
            return '插件信息缺失!';
        }

        if (method_exists($plugin, 'update')) {
            $updateSuccess = $plugin->update();
            if (!$updateSuccess) {
                return '插件预升级失败!';
            }
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

        $pluginModel->where('name', $pluginName)->update($info);

        $hookPluginModel = new HookPluginModel();

        $pluginHooksInDb = $hookPluginModel->where('plugin', $pluginName)->column('hook');

        $samePluginHooks = array_intersect($pluginHooks, $pluginHooksInDb);

        $shouldDeleteHooks = array_diff($samePluginHooks, $pluginHooksInDb);

        $newHooks = array_diff($pluginHooks, $samePluginHooks);

        if (count($shouldDeleteHooks) > 0) {
            $hookPluginModel->where('hook', 'in', $shouldDeleteHooks)->delete();
        }

        foreach ($newHooks as $pluginHook) {
            $hookPluginModel->save(['hook' => $pluginHook, 'plugin' => $pluginName]);
        }

        self::getActions($pluginName);

        Cache::clear('init_hook_plugins');
        Cache::clear('admin_menus');// 删除后台菜单缓存

        return true;
    }

    public static function getActions($pluginName)
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
                $controllerName  = preg_replace("/Controller$/", '', $controller);
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

                            $findParentAdminMenu = AdminMenuModel::where([
                                'app'        => $parentApp,
                                'controller' => $parentController,
                                'action'     => $parentAction
                            ])->find();

                            if (empty($findParentAdminMenu)) {
                                $parentId = AdminMenuModel::insertGetId([
                                    'app'        => $parentApp,
                                    'controller' => $parentController,
                                    'action'     => $parentAction,
                                    'name'       => '--new--'
                                ]);
                            } else {
                                $parentId = $findParentAdminMenu['id'];
                            }
                        }

                        $findAdminMenu = AdminMenuModel::where([
                            'app'        => $app,
                            'controller' => $controllerName,
                            'action'     => $action
                        ])->find();

                        if (empty($findAdminMenu)) {

                            AdminMenuModel::insert([
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
                                AdminMenuModel::where([
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
                                AdminMenuModel::where([
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
                        $findAuthRuleCount = AuthRuleModel::where([
                            'app'  => $app,
                            'name' => $authRuleName,
                            'type' => 'admin_url'
                        ])->count();

                        if ($findAuthRuleCount == 0) {
                            AuthRuleModel::insert([
                                'app'   => $app,
                                'name'  => $authRuleName,
                                'type'  => 'admin_url',
                                'param' => $param,
                                'title' => $menuName
                            ]);
                        } else {
                            AuthRuleModel::where([
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

                                    $findParentAdminMenu = AdminMenuModel::where([
                                        'app'        => $parentApp,
                                        'controller' => $parentController,
                                        'action'     => $parentAction
                                    ])->find();

                                    if (empty($findParentAdminMenu)) {
                                        $parentId = AdminMenuModel::insertGetId([
                                            'app'        => $parentApp,
                                            'controller' => $parentController,
                                            'action'     => $parentAction,
                                            'name'       => '--new--'
                                        ]);
                                    } else {
                                        $parentId = $findParentAdminMenu['id'];
                                    }
                                }

                                $findAdminMenu = AdminMenuModel::where([
                                    'app'        => $app,
                                    'controller' => $controllerName,
                                    'action'     => $action
                                ])->find();

                                if (empty($findAdminMenu)) {

                                    AdminMenuModel::insert([
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
                                        AdminMenuModel::where([
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
                                        AdminMenuModel::where([
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
                                $findAuthRuleCount = AuthRuleModel::where([
                                    'app'  => $app,
                                    'name' => $authRuleName,
                                    'type' => 'plugin_url'
                                ])->count();

                                if ($findAuthRuleCount == 0) {
                                    AuthRuleModel::insert([
                                        'app'   => $app,
                                        'name'  => $authRuleName,
                                        'type'  => 'plugin_url',
                                        'param' => $param,
                                        'title' => $menuName
                                    ]);
                                } else {
                                    AuthRuleModel::where([
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
}
