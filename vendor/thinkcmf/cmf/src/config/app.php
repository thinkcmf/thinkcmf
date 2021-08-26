<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'              => env('APP_HOST', ''),
    // 应用的命名空间
    'app_namespace'         => '',
    // 是否启用路由
    'with_route'            => true,
    // 开启应用快速访问
    'app_express'           => true,
    // 默认应用
    'default_app'           => 'portal',
    // 默认时区
    'default_timezone'      => env('APP_DEFAULT_TIMEZONE', 'Asia/Shanghai'),

    // 应用映射（自动多应用模式有效）
    'app_map'               => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'           => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'         => [],

    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => __DIR__ . '/../tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'   => __DIR__ . '/../tpl/dispatch_jump.tpl',
    // 异常页面的模板文件
    'exception_tmpl'        => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'         => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'        => false,
];
