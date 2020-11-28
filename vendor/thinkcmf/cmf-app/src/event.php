<?php

// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        'AppInit'    => [
            '\cmf\listener\LangListener',
            '\cmf\listener\InitHookListener'
        ],
        'ModuleInit' => [
            '\cmf\listener\InitAppHookListener'
        ],
        'HttpRun'    => [],
        'HttpEnd'    => [],
        'LogLevel'   => [],
        'LogWrite'   => [],
        'AdminInit'  => [
            '\cmf\listener\AdminMenuLangListener',
            '\cmf\listener\AdminLangListener'
        ],
        'HomeInit'   => [
            '\cmf\listener\HomeLangListener'
        ],
    ],

    'subscribe' => [
    ],
];
