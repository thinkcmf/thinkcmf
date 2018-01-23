<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

// 应用行为扩展定义文件
return [
    // 应用初始化
    'app_init'     => [
        'cmf\\behavior\\InitHookBehavior',
    ],
    // 应用开始
    'app_begin'    => [
        'cmf\\behavior\\LangBehavior',
    ],
    // 模块初始化
    'module_init'  => [],
    // 操作开始执行
    'action_begin' => [],
    // 视图内容过滤
    'view_filter'  => [],
    // 日志写入
    'log_write'    => [],
    //日志写入完成
    'log_write_done'=>[],
    // 应用结束
    'app_end'      => [],
    // 应用开始
    'admin_init'   => [
        'cmf\\behavior\\AdminLangBehavior',
    ],
    'home_init'    => [
        'cmf\\behavior\\HomeLangBehavior',
    ]
];
