<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;

class AppStoreAdminBaseController extends AdminBaseController
{
    protected function fetch($template = '', $vars = [], $config = [])
    {
        $templateFile = $this->parseTemplate($template);
        if (!is_file($templateFile)) {
            $request = $this->request;
            $depr    = config('view.view_depr');
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

                $template = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $template . '.' . ltrim(config('view.view_suffix'), '.');;
            }

            if (is_file($template)) {
                return $this->view->fetch($template);
            }
        }

        return $this->view->fetch($template);
    }

}
