<?php
/**
 * 配置文件
 */
return [
    // 默认使用的数据库连接配置
    'default'         => env('DATABASE_DRIVER', 'mysql'),

    // 自定义时间查询规则
    'time_query_rule' => [],

    // 自动写入时间戳字段
    // true为自动识别类型 false关闭
    // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
    'auto_timestamp'  => false,

    // 时间字段取出后的默认时间格式
    'datetime_format' => false,
    'authcode'        => env('DATABASE_AUTHCODE', '#authcode#'),

    // 数据库连接配置信息
    'connections'     => [
        'mysql' => [
            // 数据库类型
            'type'              => env('DATABASE_TYPE', 'mysql'),
            // 服务器地址
            'hostname'          => env('DATABASE_HOSTNAME', '#hostname#'),
            // 数据库名
            'database'          => env('DATABASE_DATABASE', '#database#'),
            // 用户名
            'username'          => env('DATABASE_USERNAME', '#username#'),
            // 密码
            'password'          => env('DATABASE_PASSWORD', '#password#'),
            // 端口
            'hostport'          => env('DATABASE_HOSTPORT', '#hostport#'),
            // 数据库连接参数
            'params'            => [],
            // 数据库编码默认采用utf8
            'charset'           => env('DATABASE_CHARSET', '#charset#'),
            // 数据库表前缀
            'prefix'            => env('DATABASE_PREFIX', '#prefix#'),

            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'            => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'       => false,
            // 读写分离后 主服务器数量
            'master_num'        => 1,
            // 指定从服务器序号
            'slave_no'          => '',
            // 是否严格检查字段是否存在
            'fields_strict'     => true,
            // 是否需要断线重连
            'break_reconnect'   => false,
            // 监听SQL
            'trigger_sql'       => env('APP_DEBUG', false),
            // 开启字段缓存
            'fields_cache'      => false,
            // 字段缓存路径
            'schema_cache_path' => app()->getRuntimePath() . 'schema' . DIRECTORY_SEPARATOR,
        ],

        // 更多的数据库配置信息
    ],
];
