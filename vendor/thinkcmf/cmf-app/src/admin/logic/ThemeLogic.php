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
use app\admin\model\ThemeModel;
use cmf\model\OptionModel;
use app\admin\model\PluginModel;
use app\user\logic\UserActionLogic;
use mindplay\annotations\Annotations;
use think\facade\Cache;

class ThemeLogic
{
    /**
     * 安装模板
     */
    public static function install($themeName)
    {
        $themeName    = strtolower($themeName);
        $themePath    = WEB_ROOT . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $themeName . DIRECTORY_SEPARATOR;
        $manifestFile = $themePath . 'manifest.json';

        if (!file_exists($manifestFile)) {
            return '模板描述文件缺失!';
        }

        $manifestContent = file_get_contents($manifestFile);
        $manifest        = json_decode($manifestContent, true);

        if (empty($manifest)) {
            return '模板描述文件内容格式不正确!';
        }

        if (strpos($manifest['name'], 'admin_') !== 0) {
            $themeModel = new ThemeModel();
            $themeCount = $themeModel->where('theme', $themeName)->count();

            if ($themeCount > 0) {
                return '模板已经安装!';
            }

            $result = $themeModel->installTheme($themeName);
        } else {
            $optionName       = "theme_manifest_" . $themeName;
            $findThemeSetting = OptionModel::where('option_name', $optionName)->find();

            if (!empty($findThemeSetting)) {
                return '模板已安装!';
            }

            $optionModel = new OptionModel();
            $optionModel->save([
                'option_name'  => $optionName,
                'option_value' => $manifest
            ]);
        }

        Cache::clear('init_hook_apps');
        Cache::clear('admin_menus');// 删除后台菜单缓存

        return true;
    }

    /**
     * 模板更新
     * @param $themeName
     * @return bool|string
     * @throws \Exception
     */
    public static function update($themeName)
    {
        $themeName    = strtolower($themeName);
        $themePath    = WEB_ROOT . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $themeName . DIRECTORY_SEPARATOR;
        $manifestFile = $themePath . 'manifest.json';

        if (!file_exists($manifestFile)) {
            return '模板描述文件缺失!';
        }

        $manifestContent = file_get_contents($manifestFile);
        $manifest        = json_decode($manifestContent, true);

        if (empty($manifest)) {
            return '模板描述文件内容格式不正确!';
        }

        if (strpos($manifest['name'], 'admin_') !== 0) {
            $themeModel = new ThemeModel();
            $themeCount = $themeModel->where('theme', $themeName)->count();

            if ($themeCount === 0) {
                return '模板未安装!';
            }

            $result = $themeModel->updateTheme($themeName);
        } else {
            $optionName       = "theme_manifest_" . $themeName;
            $findThemeSetting = OptionModel::where('option_name', $optionName)->find();

            if (!empty($findThemeSetting)) {
                cmf_set_option($optionName, $manifest);
            }
        }


        Cache::clear('init_hook_apps');
        Cache::clear('admin_menus');// 删除后台菜单缓存
        return true;
    }

}
