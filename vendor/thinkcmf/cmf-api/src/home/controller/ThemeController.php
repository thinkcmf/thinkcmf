<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
namespace api\home\controller;

use api\home\model\ThemeFileModel;
use cmf\controller\RestBaseController;

class ThemeController extends RestBaseController
{
    /**
     * 获取模板扩展属性
     */
    public function more()
    {
        $theme          = $this->request->param('theme');
        $themeFileModel = new ThemeFileModel();
        $file           = $this->request->param('file');
        $file           = $themeFileModel->where('theme', $theme)->where('file', $file)->find();

        $vars    = [];
        $widgets = [];
        $oldMore = $file['more'];
        if (!empty($oldMore['vars'])) {
            foreach ($oldMore['vars'] as $varName => $var) {
                switch ($var['type']) {
                    case 'image':
                        $vars[$varName] = cmf_get_image_url($var['value']);
                        break;
                    case 'file':
                        $vars[$varName] = cmf_get_file_download_url($var['value']);
                        break;
                    case 'array':
                        foreach ($var['value'] as $varKey => $varValue) {

                            foreach ($varValue as $varValueKey => $varValueValue) {
                                switch ($var['item'][$varValueKey]['type']) {
                                    case 'image':
                                        $var['value'][$varKey][$varValueKey] = cmf_get_image_url($varValueValue);
                                        break;
                                    case 'file':
                                        $var['value'][$varKey][$varValueKey] = cmf_get_file_download_url($varValueValue);
                                        break;
                                    default:
                                        $var['value'][$varKey][$varValueKey] = $varValueValue;

                                }
                            }
                        }
                        $vars[$varName] = $var['value'];
                        break;
                    default:
                        $vars[$varName] = $var['value'];
                }

            }
        }

        if (!empty($oldMore['widgets'])) {
            foreach ($oldMore['widgets'] as $widgetName => $widget) {

                $widgetVars = [];
                if (!empty($widget['vars'])) {
                    foreach ($widget['vars'] as $varName => $var) {

                        switch ($var['type']) {
                            case 'image':
                                $widgetVars[$varName] = cmf_get_image_url($var['value']);
                                break;
                            case 'file':
                                $widgetVars[$varName] = cmf_get_file_download_url($var['value']);
                                break;
                            case 'array':
                                foreach ($var['value'] as $varKey => $varValue) {

                                    foreach ($varValue as $varValueKey => $varValueValue) {
                                        switch ($var['item'][$varValueKey]['type']) {
                                            case 'image':
                                                $var['value'][$varKey][$varValueKey] = cmf_get_image_url($varValueValue);
                                                break;
                                            case 'file':
                                                $var['value'][$varKey][$varValueKey] = cmf_get_file_download_url($varValueValue);
                                                break;
                                            default:
                                                $var['value'][$varKey][$varValueKey] = $varValueValue;

                                        }
                                    }
                                }
                                $widgetVars[$varName]=$var['value'];
                                break;
                            default:
                                $widgetVars[$varName] = $var['value'];
                        }
                    }
                }

                $widget['vars']       = $widgetVars;
                $widgets[$widgetName] = $widget;
            }
        }

        $this->success('success', [
            'vars'    => $vars,
            'widgets' => $widgets
        ]);
    }

}
