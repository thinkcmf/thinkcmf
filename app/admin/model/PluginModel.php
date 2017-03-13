<?php
namespace app\admin\model;

use think\Model;

class PluginModel extends Model
{

    /**
     * 获取插件列表
     */
    public function getList()
    {
        $dirs = array_map('basename', glob(PLUGINS_PATH . '*', GLOB_ONLYDIR));
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

}