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
use cmf\model\OptionModel;
use app\admin\model\PluginModel;
use app\user\logic\UserActionLogic;
use mindplay\annotations\Annotations;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\migration\Migrate;

class AppLogic
{
    /**
     * 安装应用
     */
    public static function install($appName)
    {
        $appName = strtolower($appName);
        $class   = cmf_get_app_class($appName);
        if (!class_exists($class)) {
            return '应用不存在!';
        }

        $appPath = APP_PATH . $appName . DIRECTORY_SEPARATOR;

        $manifestFile = $appPath . 'manifest.json';

        if (!file_exists($manifestFile)) {
            return '应用描述文件缺失!';
        }

        $manifestContent = file_get_contents($manifestFile);
        $manifest        = json_decode($manifestContent, true);

        if (empty($manifest)) {
            return '应用描述文件内容格式不正确!';
        }

        $optionName     = "app_manifest_" . $appName;
        $findAppSetting = OptionModel::where('option_name', "app_manifest_" . $appName)->find();

        if (!empty($findAppSetting)) {
            return '应用已安装!';
        }

        $app = new $class;

        $installSuccess = $app->install();
        if (!$installSuccess) {
            return '应用预安装失败!';
        }

        $migrate = new Migrate($appName);
        $migrate->migrate();

        // 导入后台菜单
        MenuLogic::importMenus($appName);
        // 导入应用钩子
        HookLogic::importHooks($appName);
        // 导入应用用户行为
        UserActionLogic::importUserActions($appName);

        $optionModel = new OptionModel();
        $optionModel->save([
            'option_name'  => $optionName,
            'option_value' => $manifest
        ]);

        Cache::clear('init_hook_apps');
        Cache::clear('admin_menus');// 删除后台菜单缓存

        return true;
    }

    /**
     * 应用更新
     * @param $appName
     * @return bool|string
     * @throws \Exception
     */
    public static function update($appName)
    {
        $appName = strtolower($appName);
        $class   = cmf_get_app_class($appName);
        if (!class_exists($class)) {
            return '应用不存在!';
        }

        $appPath = APP_PATH . $appName . DIRECTORY_SEPARATOR;

        $manifestFile = $appPath . 'manifest.json';

        if (!file_exists($manifestFile)) {
            return '应用描述文件缺失!';
        }

        $manifestContent = file_get_contents($manifestFile);
        $manifest        = json_decode($manifestContent, true);

        if (empty($manifest)) {
            return '应用描述文件内容格式不正确!';
        }

        $findAppSetting = OptionModel::where('option_name', "app_manifest_$appName")->find();

        if (empty($findAppSetting)) {
            return '应用未安装！';
        }

        if (!empty($findAppSetting)) {
            cmf_set_option("app_manifest_$appName", $manifest);
        }

        $app = new $class;

        if (method_exists($app, 'update')) {
            $updateSuccess = $app->update();
            if (!$updateSuccess) {
                return '应用预升级失败!';
            }
        }

        $migrate = new Migrate($appName);
        $migrate->migrate();

        // 导入后台菜单
        MenuLogic::importMenus($appName);
        // 导入应用钩子
        HookLogic::importHooks($appName);
        // 导入应用用户行为
        UserActionLogic::importUserActions($appName);
        $findAppSetting->save(['option_value' => $manifest]);

        Cache::clear('init_hook_apps');
        Cache::clear('admin_menus');// 删除后台菜单缓存
        return true;
    }

    public static function getList()
    {
        $dirs = array_map('basename', glob(APP_PATH . '*', GLOB_ONLYDIR));
        if ($dirs === false) {
            return '应用目录不可读';
        }
        $apps = [];

        $appManifestsIntalled = OptionModel::where('option_name', 'like', "app_manifest_%")->select();

        $appsIntalled = [];
        foreach ($appManifestsIntalled as $appManifestIntalled) {
            $appsIntalled[$appManifestIntalled['option_name']] = $appManifestIntalled['option_value'];
        }

        if (empty($dirs)) return $apps;


        foreach ($dirs as $appName) {
            $appPath      = APP_PATH . $appName . DIRECTORY_SEPARATOR;
            $manifestFile = $appPath . 'manifest.json';

            $formatWrong = false;
            if (!file_exists($manifestFile)) {
                $formatWrong = true;
                continue;
            }

            $manifestContent = file_get_contents($manifestFile);
            $manifest        = json_decode($manifestContent, true);

            if (empty($manifest)) {
                $formatWrong = true;
            }

            if (!$formatWrong) {
                $appInfo = [];
                if (!empty($appsIntalled["app_manifest_{$appName}"])) {
                    $appInfo                  = $appsIntalled["app_manifest_{$appName}"];
                    $appInfo['installed']     = 1;
                    $appInfo['local_verison'] = $manifest['version'];
                } else {
                    $appInfo                  = $manifest;
                    $appInfo['local_verison'] = $manifest['version'];
                }

                $apps[] = $appInfo;
            }


        }
        return $apps;
    }

    public static function uninstall($appName)
    {
        $appName = strtolower($appName);
        $class   = cmf_get_app_class($appName);
        if (!class_exists($class)) {
            return '应用不存在!';
        }

        $findAppSetting = OptionModel::where('option_name', "app_manifest_$appName")->find();

        if (empty($findAppSetting)) {
            return '应用未安装！';
        }


        $appPath = APP_PATH . $appName . DIRECTORY_SEPARATOR;
        $app     = new $class;

        Db::startTrans();
        try {
            OptionModel::where('option_name', "app_manifest_$appName")->delete();

            if (method_exists($app, 'uninstall')) {
                $updateSuccess = $app->uninstall();
                if ($updateSuccess !== true) {
                    Db::rollback();
                    return $updateSuccess;
                }
            }

            // 删除后台菜单
            AdminMenuModel::where([
                'app' => $appName,
            ])->delete();

            // 删除权限规则
            AuthRuleModel::where('app', $appName)->delete();

            try {
                $database = Config::get('database.connections.' . Config::get('database.default'));
                $prefix   = $database['prefix'];
                Db::name("{$appName}_migration")->whereRaw('1')->delete();
                Db::execute("drop table {$prefix}{$appName}_migration");
            } catch (\Exception $e) {
                // echo $e->getMessage();
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }

        Cache::clear('init_hook_apps');
        Cache::clear('admin_menus');// 删除后台菜单缓存
        return true;

    }
}
