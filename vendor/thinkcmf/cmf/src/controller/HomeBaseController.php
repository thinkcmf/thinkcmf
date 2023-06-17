<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use think\facade\Db;
use app\admin\model\ThemeModel;
use think\facade\View;

class HomeBaseController extends BaseController
{

    protected function initialize()
    {
        // 监听home_init
        hook('home_init');
        parent::initialize();
    }

    protected function _initializeView()
    {
        $cmfThemePath    = config('template.cmf_theme_path');
        $cmfDefaultTheme = cmf_get_current_theme();
        $root            = cmf_get_root();
        $themePath       = "{$cmfThemePath}{$cmfDefaultTheme}";
        //使cdn设置生效
        $cdnSettings = cmf_get_option('cdn_settings');
        if (empty($cdnSettings['cdn_static_root'])) {
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$root}/{$themePath}",
                '__STATIC__'   => "{$root}/static",
                '__WEB_ROOT__' => $root
            ];
        } else {
            $cdnStaticRoot  = rtrim($cdnSettings['cdn_static_root'], '/');
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$cdnStaticRoot}/{$themePath}",
                '__STATIC__'   => "{$cdnStaticRoot}/static",
                '__WEB_ROOT__' => $cdnStaticRoot
            ];
        }

        $this->view->engine()->config([
            'view_base'          => WEB_ROOT . $themePath . '/',
            'tpl_replace_string' => $viewReplaceStr
        ]);

//        $themeErrorTmpl = "{$themePath}/error.html";
//        if (file_exists_case($themeErrorTmpl)) {
//            config('dispatch_error_tmpl', $themeErrorTmpl);
//        }
//
//        $themeSuccessTmpl = "{$themePath}/success.html";
//        if (file_exists_case($themeSuccessTmpl)) {
//            config('dispatch_success_tmpl', $themeSuccessTmpl);
//        }


    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $config = [])
    {
        $template = $this->parseTemplate($template);
        $more     = $this->getThemeFileMore($template);
        $this->assign($more);
        $content        = $this->view->fetch($template, $vars, $config);
        $designingTheme = cookie('cmf_design_theme');

        if ($designingTheme) {
            $app        = $this->app->http->getName();
            $controller = $this->request->controller();
            $action     = $this->request->action();

            $output = <<<hello
<script>
var _themeDesign=true;
var _themeTest="test";
var _app='{$app}';
var _controller='{$controller}';
var _action='{$action}';
var _themeFile='{$more['_theme_file']}';
if(parent && parent.simulatorRefresh){
  parent.simulatorRefresh();  
}
</script>
hello;

            $pos = strripos($content, '</body>');
            if (false !== $pos) {
                $content = substr($content, 0, $pos) . $output . substr($content, $pos);
            } else {
                $content = $content . $output;
            }
        }

        return $content;
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    protected function parseTemplate($template)
    {
        $siteInfo = cmf_get_site_info();
        $this->view->assign('site_info', $siteInfo);

        // 分析模板文件规则
        $request = $this->request;
        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨模块调用
            list($module, $template) = explode('@', $template);
        }

        $cmfThemePath    = config('template.cmf_theme_path');
        $cmfDefaultTheme = cmf_get_current_theme();
        $themePath       = WEB_ROOT . "{$cmfThemePath}{$cmfDefaultTheme}/";

        // 基础视图目录
        $module = isset($module) ? $module : $this->app->http->getName();
        $path   = $themePath . ($module ? $module . DIRECTORY_SEPARATOR : '');

        $depr = config('view.view_depr');
        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = cmf_parse_name($request->controller());
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . cmf_parse_name($request->action(true));
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }

        return $path . ltrim($template, '/') . '.' . ltrim(config('view.view_suffix'), '.');
    }

    /**
     * 获取模板文件变量
     * @param string $file
     * @param string $theme
     * @return array
     */
    private function getThemeFileMore($file, $theme = '')
    {
        //TODO 增加缓存
        $theme = empty($theme) ? cmf_get_current_theme() : $theme;

        // 调试模式下自动更新模板
        if (APP_DEBUG) {
            $themeModel = new ThemeModel();
            $themeModel->updateTheme($theme);
        }

        $themePath = config('template.cmf_theme_path');
        $file      = str_replace('\\', '/', $file);
        $file      = str_replace('//', '/', $file);
        $webRoot   = str_replace('\\', '/', WEB_ROOT);
        $themeFile = str_replace(['.html', '.php', $themePath . $theme . '/', $webRoot], '', $file);

        $files = Db::name('theme_file')->field('more,file,id')->where('theme', $theme)
            ->where(function ($query) use ($themeFile) {
                $query->where('is_public', 1)->whereOr('file', $themeFile);
            })->order('is_public desc')->select();

        $vars           = [];
        $widgets        = [];
        $widgetsBlocks  = [];
        $widgetsInBlock = [];

        foreach ($files as $file) {
            $oldMore = json_decode($file['more'], true);
            if (!empty($oldMore['vars'])) {
                foreach ($oldMore['vars'] as $varName => $var) {
                    $vars[$varName] = $var['value'];
                }
            }

            if (!empty($oldMore['widgets'])) {
                foreach ($oldMore['widgets'] as $widgetName => $widget) {

                    $widgetVars = [];
                    if (!empty($widget['vars'])) {
                        foreach ($widget['vars'] as $varName => $var) {
                            $widgetVars[$varName] = $var['value'];
                        }
                    }

                    $widget['vars'] = $widgetVars;
                    //如果重名，则合并配置
                    if (empty($widgets[$widgetName])) {
                        $widgets[$widgetName] = $widget;
                    } else {
                        foreach ($widgets[$widgetName] as $key => $value) {
                            if (is_array($widget[$key])) {
                                $widgets[$widgetName][$key] = array_merge($widgets[$widgetName][$key], $widget[$key]);
                            } else {
                                $widgets[$widgetName][$key] = $widget[$key];
                            }
                        }
                    }
                }
            }

            if ($themeFile == $file['file'] && !empty($oldMore['widgets_blocks'])) {

                if (!empty($oldMore['widgets_blocks'])) {
                    foreach ($oldMore['widgets_blocks'] as $widgetsBlockName => $widgetsBlock) {
                        $widgetsBlock['_file_id'] = $file['id'];
                        if (!empty($widgetsBlock['widgets'])) {
                            foreach ($widgetsBlock['widgets'] as $widgetId => $widget) {

                                if (!empty($widget['vars'])) {
                                    foreach ($widget['vars'] as $varName => $varValue) {
                                        if (isset($widget['vars'][$varName . '_type_']) && $widget['vars'][$varName . '_type_'] == 'rich_text') {
                                            $widget['vars'][$varName] = cmf_replace_content_file_url(htmlspecialchars_decode($varValue));
                                        }
                                    }
                                }

                                $widgetsBlock['widgets'][$widgetId]['vars'] = $widget['vars'];

                                $widgetsInBlock[$widget['name']] = [
                                    'name'    => $widget['name'],
                                    'display' => $widget['display']
                                ];
                            }
                        }
                        $widgetsBlocks[$widgetsBlockName] = $widgetsBlock;
                    }
                }
            }
        }


        return [
            'theme_vars'           => $vars,
            'theme_widgets'        => $widgets,
            'theme_widgets_blocks' => $widgetsBlocks,
            '_theme_widgets'       => $widgetsInBlock,
            '_theme_file'          => $themeFile
        ];
    }

    public function checkUserLogin($isreurl = false)
    {
        $refer  = $this->request->server('HTTP_REFERER');
        $userId = cmf_get_current_user_id();
        if (empty($userId)) {
            if ($isreurl !== false) {
                $tourl = cmf_url('user/Login/index', ['redirect' => $refer]);
            } else {
                $tourl = cmf_url('user/Login/index');
            }
            if ($this->request->isAjax()) {
                $this->error('您尚未登录', $tourl);
            } else {
                $this->redirect($tourl);
            }
        }
    }

}
