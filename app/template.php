<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
// 请不要随意修改此文件内容
return [
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写
    'auto_rule'               => 1,
    // 模板引擎类型 支持 php think 支持扩展
    'type'                    => 'Think',
    // 视图基础目录，配置目录为所有模块的视图起始目录
    'view_base'               => '',
    // 当前模板的视图目录 留空为自动获取
    'view_path'               => '',
    // 模板后缀
    'view_suffix'             => 'html',
    // 模板文件名分隔符
    'view_depr'               => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'               => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'                 => '}',
    // 标签库标签开始标记
    'taglib_begin'            => '<',
    // 标签库标签结束标记
    'taglib_end'              => '>',
    'taglib_build_in'         => 'cmf\lib\taglib\Cmf,cx',
    'default_filter'          => '',
    // +----------------------------------------------------------------------
    // | CMF 设置
    // +----------------------------------------------------------------------
    'cmf_theme_path'          => 'themes/',
    'cmf_default_theme'       => 'default',
    'cmf_admin_theme_path'    => 'themes/',
    'cmf_admin_default_theme' => 'admin_simpleboot3',
    'tpl_replace_string'      => [
        '__STATIC__' => '/static',
        '__ROOT__'   => '',
    ]
];