<?php

// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        'AppInit'   => ['\cmf\listener\InitHookListener'],
        'ModuleInit'   => ['\cmf\listener\LangListener'],
        'HttpRun'   => [],
        'HttpEnd'   => [],
        'LogLevel'  => [],
        'LogWrite'  => [],
        'AdminInit' => [
            '\cmf\listener\AdminLangListener'
        ],
    ],

    'subscribe' => [
    ],
];
