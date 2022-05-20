<?php

// 事件定义文件
return [
    'bind' => [
    ],

    'listen' => [
        'AppInit'    => [
            '\cmf\listener\InitHookListener'
        ],
        'ModuleInit' => [
            '\cmf\listener\ModuleInitListener',
        ],
        'HttpRun'    => [],
        'HttpEnd'    => [],
        'LogLevel'   => [],
        'LogWrite'   => [],
        'AdminInit'  => [
            '\cmf\listener\AdminInitListener',
        ],
        'HomeInit'   => [
            '\cmf\listener\HomeLangListener'
        ],
    ],

    'subscribe' => [
    ],
];
