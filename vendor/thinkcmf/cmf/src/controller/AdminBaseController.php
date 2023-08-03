<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use app\admin\model\UserModel;

class AdminBaseController extends BaseController
{

    protected function initialize()
    {
        // 监听admin_init
        hook('admin_init');
        parent::initialize();
        $sessionAdminId = session('ADMIN_ID');
        if (!empty($sessionAdminId)) {
            $user = UserModel::where('id', $sessionAdminId)->find();

            if (!$this->checkAccess($sessionAdminId)) {
                $this->error(lang('no access'));
            }
            $this->assign('admin', $user);
        } else {
            if ($this->request->isPost()) {
                $this->error(lang('You are not logged in'), url('admin/Public/login'));
            } else {
                return $this->redirect(url('admin/Public/login'));
            }
        }
    }

    public function _initializeView()
    {
        $this->updateViewConfig();
    }

    private function updateViewConfig($defaultTheme = '', $viewBase = '')
    {
        $cmfAdminThemePath = config('template.cmf_admin_theme_path');

        if (empty($defaultTheme)) {
            $cmfAdminDefaultTheme = cmf_get_current_admin_theme();
        } else {
            $cmfAdminDefaultTheme = $defaultTheme;
        }

        $themePath = "{$cmfAdminThemePath}{$cmfAdminDefaultTheme}";

        $root = cmf_get_root();

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

        if (empty($viewBase)) {
            $viewBase = WEB_ROOT . $themePath . '/';
        }

        $this->view->engine()->config([
            'view_base'          => $viewBase,
            'tpl_replace_string' => $viewReplaceStr
        ]);
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
        $content  = $this->view->fetch($template, $vars, $config);

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
        // 分析模板文件规则
        $request = $this->request;
        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨模块调用
            list($app, $template) = explode('@', $template);
        }

        $cmfAdminThemePath    = config('template.cmf_admin_theme_path');
        $cmfAdminDefaultTheme = cmf_get_current_admin_theme();
        $themePath            = WEB_ROOT . "{$cmfAdminThemePath}{$cmfAdminDefaultTheme}/";

        // 基础视图目录
        $app = isset($app) ? $app : $this->app->http->getName();
//        $path = $themePath . ($app ? $app . DIRECTORY_SEPARATOR : '');

        $depr = config('view.view_depr');
        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = cmf_parse_name($request->controller());
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . cmf_parse_name($request->action(false));
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }

        $file = $themePath . ($app ? $app . DIRECTORY_SEPARATOR : '') . ltrim($template, '/') . '.' . ltrim(config('view.view_suffix'), '.');

        if (!is_file($file)) {

            $adminDefaultTheme = 'admin_simpleboot3';

            $cmfAdminThemePath = config('template.cmf_admin_theme_path');
            $themePath         = "{$cmfAdminThemePath}{$adminDefaultTheme}";
            $viewBase          = WEB_ROOT . $themePath . '/';

            $defaultFile = $viewBase . ($app ? $app . DIRECTORY_SEPARATOR : '') . ltrim($template, '/') . '.' . ltrim(config('view.view_suffix'), '.');

            if (is_file($defaultFile)) {
                $file = $defaultFile;
                $this->updateViewConfig($adminDefaultTheme);
            }
        }

        return $file;
    }

    /**
     * 初始化后台菜单
     */
    public function initMenu()
    {
    }

    /**
     *  检查后台用户访问权限
     * @param int $userId 后台用户id
     * @return boolean 检查通过返回true
     */
    private function checkAccess($userId)
    {
        // 如果用户id是1，则无需判断
        if ($userId == 1) {
            return true;
        }

        $app        = $this->app->http->getName();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $rule       = $app . $controller . $action;

        $notRequire = ['adminIndexindex', 'adminMainindex'];
        if (!in_array($rule, $notRequire)) {
            return cmf_auth_check($userId);
        } else {
            return true;
        }
    }

}
