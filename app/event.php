<?php

// 事件定义文件
return [
    'bind' => [

    ],

    'listen' => [
        'AppInit'   => ['\cmf\listener\InitHookListener'],
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
