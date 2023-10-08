<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------
namespace cmf\listener;

class HomeLangListener
{
    private $app;

    // 行为扩展的执行入口必须是run
    public function handle()
    {
        /**--start LangListener--------------------------------------*/
        $this->app = app();
        $langSet   = $this->app->lang->getLangSet();

        $this->app->lang->load([
            root_path() . "vendor/thinkcmf/cmf/src/lang/{$langSet}.php",
        ]);

        // 加载应用公共语言包
        $apps = cmf_scan_dir($this->app->getAppPath() . '*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            $this->app->lang->load([
                $this->app->getAppPath() . $app . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'common.php',
            ]);
        }
        /**--end LangListener--------------------------------------*/

        /**--start InitAppHookListener--------------------------------------*/
        $this->app = app();
        $appName   = $this->app->http->getName();

        if (!is_dir($this->app->getAppPath() . $appName) && !is_dir(root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}")) {
            return;
        }

        $langSet = $this->app->lang->getLangSet();

        // 加载核心应用语言包
        $this->app->lang->load([
            root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}/lang/{$langSet}.php",
        ]);

        // 加载应用语言包
        $this->app->lang->load([
            $this->app->getAppPath() . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . '.php',
        ]);

        $app       = app();
        $langSet   = $app->lang->getLangSet();
        $langFiles = [];

        // 加载应用前台语言包
        $apps = cmf_scan_dir($app->getAppPath() . '*', GLOB_ONLYDIR);
        foreach ($apps as $appName) {
            $langFiles[] = $app->getAppPath() . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'home.php';
        }

        $app->lang->load($langFiles);

        $request = request();
        $param   = $request->param();
        if (!empty($param['_plugin'])) {
            $plugin = $param['_plugin'];
            // 加载应用语言包
            $this->app->lang->load([
                WEB_ROOT . "plugins/$plugin/lang/$langSet.php",
                WEB_ROOT . "plugins/$plugin/lang/$langSet/home.php",
            ]);
        }

        // 监听home_lang_load
        hook('home_lang_load', ['lang' => $langSet]);
    }
}
