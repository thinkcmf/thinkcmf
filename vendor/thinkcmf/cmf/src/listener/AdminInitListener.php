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

use think\App;
use think\Cookie;
use think\Lang;
use think\Request;

class AdminInitListener
{
    protected $config;

    public function __construct(protected App $app, protected Lang $lang)
    {
        $this->config = $lang->getConfig();
    }

    // 行为扩展的执行入口必须是run
    public function handle($param)
    {
        /**--start LangListener--------------------------------------*/
        $request = request();
        $langSet = $this->detect($request);

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
        $appName = $this->app->http->getName();

        if (!is_dir($this->app->getAppPath() . $appName) && !is_dir(root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}")) {
            return;
        }

        // 加载核心应用语言包
        $this->app->lang->load([
            root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}/lang/{$langSet}.php",
        ]);

        // 加载应用语言包
        $this->app->lang->load([
            $this->app->getAppPath() . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . '.php',
        ]);

        /**--start AdminMenuLangListener--------------------------------------*/
        $langFiles = [];

        // 加载核心应用后台菜单语言包
        $coreApps = ['admin', 'user'];
        foreach ($coreApps as $appName) {
            $langFiles[] = root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}/lang/{$langSet}/admin_menu.php";
        }

        // 加载应用后台菜单语言包
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);
        foreach ($apps as $appName) {
            $langFiles[] = APP_PATH . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'admin_menu.php';
        }

        $plugins = db('plugin')->where('status', 1)->select();
        if (!$plugins->isEmpty()) {
            foreach ($plugins as $plugin) {
                $pluginDir   = cmf_parse_name($plugin['name']);
                $langFiles[] = WEB_ROOT . "plugins/$pluginDir/lang/$langSet/admin_menu.php";
            }
        }

        // 加后台菜单动态语言包
        $defaultLangDir = $this->app->lang->defaultLangSet();
        $langFiles[]    = CMF_DATA . "lang/" . $defaultLangDir . "/admin_menu.php";

        $this->app->lang->load($langFiles);
        /**--end AdminMenuLangListener--------------------------------------*/

        /**--start AdminLangListener--------------------------------------*/
        $appName = app()->http->getName();

        // 加载核心应用后台语言包
        $coreApps  = ['admin', 'user'];
        $langFiles = [];
        if (in_array($appName, $coreApps)) {
            $langFiles[] = root_path() . "vendor/thinkcmf/cmf-app/src/{$appName}/lang/{$langSet}/admin.php";
        }

        // 加载应用后台菜单语言包
        $langFiles[] = $this->app->getAppPath() . $appName . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . 'admin_menu.php';


        /**--end AdminLangListener--------------------------------------*/

        $param = $request->param();
        if (!empty($param['_plugin'])) {
            $plugin = $param['_plugin'];
            // 加载插件语言包
            $langFiles[] = WEB_ROOT . "plugins/$plugin/lang/$langSet.php";
            $langFiles[] = WEB_ROOT . "plugins/$plugin/lang/$langSet/admin.php";
        }

        $this->app->lang->load($langFiles);
        // 监听admin_lang_load
        hook('admin_lang_load', ['lang' => $langSet]);
    }

    /**
     * 自动侦测设置获取语言选择
     * @access protected
     * @param Request $request
     * @return string
     */
    protected function detect(Request $request): string
    {
        // 自动侦测设置获取语言选择
        $langSet = '';
        if (empty($this->config['admin_default_lang'])) {
            $adminDefaultLangSet = $this->lang->defaultLangSet();
        } else {
            $adminDefaultLangSet = $this->config['admin_default_lang'];
        }
        if (empty($this->config['admin_multi_lang'])) {
            // 合法的语言
            $this->lang->setLangSet($adminDefaultLangSet);
            return $adminDefaultLangSet;
        }

        if ($request->get($this->config['detect_var'])) {
            // url中设置了语言变量
            $langSet = $request->get($this->config['detect_var']);
        } elseif ($request->header($this->config['header_var'])) {
            // Header中设置了语言变量
            $langSet = $request->header($this->config['header_var']);
        } elseif ($request->cookie('cmf_admin_lang')) {
            // Cookie中设置了语言变量
            $langSet = $request->cookie('cmf_admin_lang');
        } elseif ($request->server('HTTP_ACCEPT_LANGUAGE')) {
            // 自动侦测浏览器语言
            $langSet = $request->server('HTTP_ACCEPT_LANGUAGE');
        }

        if (preg_match('/^([a-z\d\-]+)/i', $langSet, $matches)) {
            $langSet = strtolower($matches[1]);
            if (isset($this->config['accept_language'][$langSet])) {
                $langSet = $this->config['accept_language'][$langSet];
            }
        } else {
            $langSet = $this->lang->getLangSet();
        }

        if (empty($this->config['admin_allow_lang_list']) || in_array($langSet, $this->config['admin_allow_lang_list'])) {
            // 合法的语言
            $this->lang->setLangSet($langSet);
        } else {
            $langSet = $adminDefaultLangSet;
        }

        $this->saveToCookie($this->app->cookie, $langSet);

        return $langSet;
    }

    /**
     * 保存当前语言到Cookie
     * @access protected
     * @param Cookie $cookie  Cookie对象
     * @param string $langSet 语言
     * @return void
     */
    protected function saveToCookie(Cookie $cookie, string $langSet): void
    {
        $cookie->set('cmf_admin_lang', $langSet);
    }
}
