<?php
/**
 * Created by PhpStorm.
 * User: kuaX
 * Date: 2017/5/5
 * Time: 上午11:44
 */
//return [
//    // 数据库类型
//    'type'           => 'mysql',
//    // 服务器地址
//    'hostname'       => 'localhost',
//    // 数据库名
//    'database'       => 'tp5cmf',
//    // 用户名
//    'username'       => 'root',
//    // 密码
//    'password'       => '123123',
//    // 端口
//    'hostport'       => '3306',
//    // 数据库编码默认采用utf8
//    'charset'        => 'utf8mb4',
//    // 数据库表前缀
//    'prefix'         => 'cmf_',
//    "authcode" => 'CviMdXkZ3vUxyJCwNt',
//];

// 这里不起作用
// api 模块DB 配置
return [
    // 数据库类型
    'type'        => 'mysql',
    // 数据库连接DSN配置
    'dsn'         => '',
    // 服务器地址
    'hostname'    => '127.0.0.1',
    // 数据库名
    'database'    => 'tp5cmf2',
    // 数据库用户名
    'username'    => 'root',
    // 数据库密码
    'password'    => '123123111',
    // 数据库连接端口
    'hostport'    => '3306',
    // 数据库连接参数 具体的连接参数可以参考PHP手册
    'params'      => [],
    // 数据库编码默认采用utf8
    'charset'     => 'utf8mb4',
    // 数据库表前缀
    'prefix'      => 'cmf_',
    // 数据库调试模式
    'debug'       => false,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'      => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate' => false,
    // 读写分离后 主服务器数量
    'master_num'  => 1,
    // 指定从服务器序号
    'slave_no'    => '',
    // 是否严格检查字段是否存在
    'fields_strict'  => true,
];