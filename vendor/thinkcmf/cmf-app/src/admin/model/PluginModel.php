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
namespace app\admin\model;

use think\Model;

class PluginModel extends Model
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'plugin';

    /**
     * 获取插件列表
     */
    public function getList()
    {
        $dirs = array_map('basename', glob(WEB_ROOT . 'plugins/*', GLOB_ONLYDIR));
        if ($dirs === false) {
            $this->error = '插件目录不可读';
            return false;
        }
        $plugins = [];

        if (empty($dirs)) return $plugins;

        $list = $this->select();
        foreach ($list as $plugin) {
            $plugins[$plugin['name']] = $plugin;
        }

        foreach ($dirs as $pluginDir) {
            $pluginDir = cmf_parse_name($pluginDir, 1);
            if (!isset($plugins[$pluginDir])) {
                $class = cmf_get_plugin_class($pluginDir);
                if (!class_exists($class)) { // 实例化插件失败忽略
                    //TODO 加入到日志中
                    continue;
                }
                $obj                 = new $class;
                $plugins[$pluginDir] = $obj->info;

                if (!isset($obj->info['type']) || $obj->info['type'] == 1) {//只获取普通插件
                    if ($plugins[$pluginDir]) {
                        $plugins[$pluginDir]['status'] = 3;//未安装
                    }
                } else {
                    unset($plugins[$pluginDir]);
                }

            }
        }
        return $plugins;
    }

    /**
     * @TODO
     * 获取所有钩子，包括系统，应用，模板
     * @param bool $refresh 是否刷新缓存
     * @return array
     */
    public function getHooks($refresh = false)
    {
        if (!$refresh) {
            // TODO 加入缓存
        }

        $returnHooks = [];
        $systemHooks = [
            //系统钩子
            "app_init", "app_begin", "module_init", "action_begin", "view_filter",
            "app_end", "log_write", "log_write_done", "response_end",
            "admin_init",
            "home_init",
            "send_mobile_verification_code",
            //系统钩子结束

            //前台登录钩子
            "user_login_start",

            //模板钩子
            "body_start", "before_head_end", "before_footer", "footer_start", "before_footer_end", "before_body_end",
            "left_sidebar_start",
            "before_left_sidebar_end",
            "right_sidebar_start",
            "before_right_sidebar_end",
            "comment",
            "guestbook",

        ];

        $dbHooks = HookModel::column('hook');

        $returnHooks = array_unique(array_merge($systemHooks, $dbHooks));


        return $returnHooks;

    }

    public function uninstall($id)
    {
        $findPlugin = $this->find($id);

        if (empty($findPlugin)) {
            return -1; //插件不存在;
        }
        $class = cmf_get_plugin_class($findPlugin['name']);

        HookPluginModel::startTrans();
        try {
            $this->where('name', $findPlugin['name'])->delete();
            HookPluginModel::where('plugin', $findPlugin['name'])->delete();

            if (class_exists($class)) {
                $plugin = new $class;

                $uninstallSuccess = $plugin->uninstall();
                if (!$uninstallSuccess) {
                    HookPluginModel::rollback();
                    return -2;
                }
            }

            // 删除后台菜单
            AdminMenuModel::where([
                'app' => "plugin/{$findPlugin['name']}",
            ])->delete();

            // 删除权限规则
            AuthRuleModel::where('app', "plugin/{$findPlugin['name']}")->delete();

            HookPluginModel::commit();
        } catch (\Exception $e) {
            HookPluginModel::rollback();
            return false;
        }

        return true;

    }

}
