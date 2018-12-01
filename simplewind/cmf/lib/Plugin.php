<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------
namespace cmf\lib;

use think\exception\TemplateNotFoundException;
use think\Lang;
use think\Loader;
use think\Request;
use think\View;
use think\Config;
use think\Db;

/**
 * 插件类
 */
abstract class Plugin
{
    /**
     * 视图实例对象
     * @var view
     * @access protected
     */
    private $view = null;

    /**
     * $info = array(
     *  'name'=>'Helloworld',
     *  'title'=>'Helloworld',
     *  'description'=>'Helloworld',
     *  'status'=>1,
     *  'author'=>'thinkcmf',
     *  'version'=>'1.0'
     *  )
     */
    public $info = [];
    private $pluginPath = '';
    private $name = '';
    private $configFilePath = '';
    private $themeRoot = "";

    public function __construct()
    {

        $request = Request::instance();

        $engineConfig = Config::get('template');

        $this->name = $this->getName();

        $nameCStyle = Loader::parseName($this->name);

        $this->pluginPath     = PLUGINS_PATH . $nameCStyle . '/';
        $this->configFilePath = $this->pluginPath . 'config.php';

        $config = $this->getConfig();

        $theme = isset($config['theme']) ? $config['theme'] : '';

        $depr = "/";

        $root = cmf_get_root();

        $themeDir = empty($theme) ? "" : '/' . $theme;

        $themePath = 'view' . $themeDir;

        $this->themeRoot = $this->pluginPath . $themePath . '/';

        $engineConfig['view_base'] = $this->themeRoot;

        $pluginRoot = "plugins/{$nameCStyle}";

        $cmfAdminThemePath    = config('cmf_admin_theme_path');
        $cmfAdminDefaultTheme = config('cmf_admin_default_theme');

        $adminThemePath = "{$cmfAdminThemePath}{$cmfAdminDefaultTheme}";

        //使cdn设置生效
        $cdnSettings = cmf_get_option('cdn_settings');
        if (empty($cdnSettings['cdn_static_root'])) {
            $replaceConfig = [
                '__PLUGIN_TMPL__' => $root . '/' . $pluginRoot . '/' . $themePath,
                '__PLUGIN_ROOT__' => $root . '/' . $pluginRoot,
                '__ADMIN_TMPL__'  => "{$root}/{$adminThemePath}",
                '__STATIC__'      => "{$root}/static",
                '__WEB_ROOT__'    => $root
            ];
        } else {
            $cdnStaticRoot = rtrim($cdnSettings['cdn_static_root'], '/');
            $replaceConfig = [
                '__PLUGIN_TMPL__' => $cdnStaticRoot . '/' . $pluginRoot . '/' . $themePath,
                '__PLUGIN_ROOT__' => $cdnStaticRoot . '/' . $pluginRoot,
                '__ADMIN_TMPL__'  => "{$cdnStaticRoot}/{$adminThemePath}",
                '__STATIC__'      => "{$cdnStaticRoot}/static",
                '__WEB_ROOT__'    => $cdnStaticRoot
            ];
        }

        $this->view = new View($engineConfig, $replaceConfig);

        //加载多语言
        $langSet   = $request->langset();
        $lang_file = $this->pluginPath . "lang/" . $langSet . ".php";
        Lang::load($lang_file);

    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @return mixed
     */
    final protected function fetch($template)
    {
        if (!is_file($template)) {
            $engineConfig = Config::get('template');
            $template     = $this->themeRoot . $template . '.' . $engineConfig['view_suffix'];
        }

        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new TemplateNotFoundException('template not exists:' . $template, $template);
        }

        return $this->view->fetch($template);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param string $content 模板内容
     * @return mixed
     */
    final protected function display($content = '')
    {
        return $this->view->display($content);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    final protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * 获取插件名
     * @return string
     */
    final public function getName()
    {
        if (empty($this->name)) {
            $class = get_class($this);

            $this->name = substr($class, strrpos($class, '\\') + 1, -6);
        }

        return $this->name;

    }

    /**
     * 检查插件信息完整性
     * @return bool
     */
    final public function checkInfo()
    {
        $infoCheckKeys = ['name', 'title', 'description', 'status', 'author', 'version'];
        foreach ($infoCheckKeys as $value) {
            if (!array_key_exists($value, $this->info))
                return false;
        }
        return true;
    }

    /**
     * 获取插件根目录绝对路径
     * @return string
     */
    final public function getPluginPath()
    {

        return $this->pluginPath;
    }

    /**
     * 获取插件配置文件绝对路径
     * @return string
     */
    final public function getConfigFilePath()
    {
        return $this->configFilePath;
    }

    /**
     *
     * @return string
     */
    final public function getThemeRoot()
    {
        return $this->themeRoot;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * 获取插件的配置数组
     * @return array
     */
    final public function getConfig()
    {
        static $_config = [];
        $name = $this->getName();
        if (isset($_config[$name])) {
            return $_config[$name];
        }

        $config = Db::name('plugin')->where('name', $name)->value('config');

        if (!empty($config) && $config != "null") {
            $config = json_decode($config, true);
        } else {
            $config = $this->getDefaultConfig();

        }
        $_config[$name] = $config;
        return $config;
    }

    /**
     * 获取插件的配置数组
     * @return array
     */
    final public function getDefaultConfig()
    {
        $config = [];
        if (file_exists($this->configFilePath)) {
            $tempArr = include $this->configFilePath;
            if (!empty($tempArr) && is_array($tempArr)) {
                foreach ($tempArr as $key => $value) {
                    if ($value['type'] == 'group') {
                        foreach ($value['options'] as $gkey => $gvalue) {
                            foreach ($gvalue['options'] as $ikey => $ivalue) {
                                $config[$ikey] = $ivalue['value'];
                            }
                        }
                    } else {
                        $config[$key] = $tempArr[$key]['value'];
                    }
                }
            }
        }


        return $config;
    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();
}
