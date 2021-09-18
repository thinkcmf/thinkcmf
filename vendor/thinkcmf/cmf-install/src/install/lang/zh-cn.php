<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
$software_agreement = <<<'AGREEMENT'
ThinkCMF软件使用协议

版权所有 ©2013-{:copyright_date}，ThinkCMF开源社区

感谢您选择ThinkCMF内容管理框架, 希望我们的产品能够帮您把网站发展的更快、更好、更强！

ThinkCMF遵循MIT开源协议发布，并提供免费使用。

ThinkCMF建站系统由简约风网络科技（以下简称简约风，官网 http://www.thinkcmf.com）发起并开源发布。
简约风网络科技包含以下网站：
ThinkCMF官网： http://www.thinkcmf.com

ThinkCMF免责声明
    1、使用ThinkCMF构建的网站的任何信息内容以及导致的任何版权纠纷和法律争议及后果，ThinkCMF官方不承担任何责任。
    2、您一旦安装使用ThinkCMF，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。
AGREEMENT;

$finished_tip = <<<'FINISHED'
为了您的站点安全，安装完成后您有两种方式删除安装程序：<br>
1、将"vendor/thinkcmf/cmf-install"文件夹删除；<br>
2、最好执行"composer remove thinkcmf/cmf-install"删除！<br>
另请对 "data/config/database.php" 文件做好备份，以防丢失！
FINISHED;

return [
    'ACCEPT'                    => '接 受',
    'SITE_HAS_INSTALLED'        => '网站已经安装。',
    'DIRECTORY_CANNOT_WRITE'    => '目录{:directory}无法写入！',
    'INSTALLATION'              => '安装',
    'INSTALL_WIZARD'            => '安装向导',
    'SOFTWARE_AGREEMENT'        => $software_agreement,
    'ENVIRONMENT_DETECT'        => '检测环境',
    'CREATE_DATA'               => '创建数据',
    'INSTALL_FINISH'            => '完成安装',
    'RECOMMENDED_CONFIG'        => '推荐配置',
    'CURRENT_STATE'             => '当前状态',
    'REQUIREMENTS'              => '最低要求',
    'OS'                        => '操作系统',
    'UNIX_LIKE'                 => '类UNIX',
    'UNLIMITED'                 => '不限制',
    'PHP_VERSION'               => 'PHP版本',
    'MODULE_DETECT'             => '模块检测',
    'ENABLE'                    => '开启',
    'ENABLED'                   => '已开启',
    'DISABLED'                  => '未开启',
    'SUPPORT'                   => '支持',
    'NOT_SUPPORT'               => '不支持',
    'OPEN_EXT'                  => '开启 {:extension} 扩展',
    'RAW_POST_DATA'             => '关闭检测',
    'CLOSE'                     => '关闭',
    'CLOSED'                    => '已关闭',
    'NOT_CLOSED'                => '未关闭',
    'RAW_POST_SOLUTION'         => '未关闭解决',
    'RAW_POST_SOLUTION1'        => '找到 {:item} 设置如下：',
    'REWRITE_DETECT'            => 'rewrite检测（开启rewrite更利于网站SEO优化）',
    'SERVER'                    => '服务器',
    'DETECTING'                 => '正在检测',
    'SIZE_DETECT'               => '大小限制检测',
    'UPLOAD_FILES'              => '附件上传',
    'DIR_PERMISSION'            => '目录、文件权限检查',
    'WRITE'                     => '写入',
    'READ'                      => '读取',
    'WRITABLE'                  => '可写',
    'NOT_WRITABLE'              => '不可写',
    'READABLE'                  => '可读',
    'NOT_READABLE'              => '不可读',
    'TEST_AGAIN'                => '重新检测',
    'NEXT'                      => '下一步',
    'PREVIOUS'                  => '上一步',
    'DATABASE_INFO'             => '数据库信息',
    'DATABASE_SERVER'           => '数据库服务器',
    'DATABASE_SERVER_TIP'       => '数据库服务器地址，一般为127.0.0.1或localhost',
    'DATABASE_PORT'             => '数据库端口',
    'DATABASE_PORT_TIP'         => '数据库服务器端口，MySQL一般为3306',
    'DATABASE_USERNAME'         => '数据库用户名',
    'DATABASE_PASSWORD'         => '数据库密码',
    'DATABASE_NAME'             => '数据库名',
    'DATABASE_NAME_TIP'         => '最好小写字母',
    'TABLE_PREFIX'              => '数据库表前缀',
    'TABLE_PREFIX_TIP'          => '建议使用默认，当同一数据库安装多个ThinkCMF时需修改',
    'DATABASE_CHARSET'          => '数据库编码',
    'DATABASE_CHARSET_TIP'      => '如果您的服务器是虚拟空间不支持 uft8mb4，请选择 utf8',
    'WEBSITE_CONFIG'            => '网站配置',
    'WEBSITE_NAME'              => '网站名称',
    'WEBSITE_NAME_TIP'          => 'ThinkCMF内容管理框架',
    'WEBSITE_KEYWORD'           => 'SEO关键词',
    'WEBSITE_KEYWORD_TIP'       => 'ThinkCMF,php,内容管理框架,cmf,cms,简约风, simplewind,framework',
    'SEO_DESCRIPTION'           => 'SEO描述',
    'SEO_DESCRIPTION_TIP'       => 'ThinkCMF是简约风网络科技发布的一款用于快速开发的内容管理框架',
    'ADMIN_INFO'                => '管理员信息',
    'ADMIN_ACCOUNT'             => '创始人帐号',
    'ADMIN_ACCOUNT_TIP'         => '创始人帐号，拥有站点后台所有管理权限',
    'PASSWORD'                  => '密码',
    'RE_PASSWORD'               => '确认密码',
    'PASSWORD_TIP'              => '密码长度不低于6位,不高于32位。',
    'DATABASE_LINK_FAILED'      => '数据库链接配置失败',
    'URL_TIP'                   => '请以“/”结尾',
    'DB_HOST_ERROR'             => '数据库服务器地址不能为空',
    'DB_PORT_ERROR'             => '数据库服务器端口不能为空',
    'DB_USERNAME_ERROR'         => '数据库用户名不能为空',
    'DB_PASSWORD_ERROR'         => '数据库密码不能为空',
    'DB_NAME_ERROR'             => '数据库名不能为空',
    'DB_PREFIX_ERROR'           => '数据库表前缀不能为空',
    'DB_ADMIN_ERROR'            => '管理员帐号不能为空',
    'DB_PASSWORD_ERROR'         => '密码不能为空',
    'DB_PASSWORD_ERROR1'        => '密码长度不能低于{0}位',
    'DB_PASSWORD_ERROR2'        => '密码长度不能超过{0}位',
    'RE_PASSWORD_ERROR'         => '确认密码不能为空',
    'RE_PASSWORD_ERROR1'        => '两次输入的密码不一致，请重新输入',
    'EMAIL_ERROR'               => 'Email不能为空',
    'EMAIL_ERROR1'              => '请输入正确的电子邮箱地址',
    'INSTALLING'                => '正在安装',
    'CONGRATULATION'            => '恭喜您，安装完成！',
    'FINISHED_TIP'              => $finished_tip,
    'FRONTEND'                  => '进入前台',
    'BACKEND'                   => '进入后台',
    'UPLOAD_PROHIBITED'         => '禁止上传',
    'PASSWORD_6'                => '密码长度最少6位',
    'PASSWORD_32'               => '密码长度最多32位',
    'ILLEGAL_INSTALL'           => '非法安装！',
    'INSTALL_FINISHED'          => '安装完成！',
    'DB_CONFIG_WRITE_SUCCESS'   => '数据配置文件写入成功！',
    'DB_CONFIG_WRITE_FAILED'    => '数据配置文件写入失败！',
    'SITE_CREATE_SUCCESS'       => '网站创建完成！',
    'SITE_CREATE_FAILED'        => '网站创建失败！',
    'TEMPLATE_NOT_EXIST'        => '模板不存在！',
    'TEMPLATE_INSTALL_SUCCESS'  => '模板安装成功！',
    'MENU_IMPORT_SUCCESS'       => '应用后台菜单导入成功！',
    'HOOK_IMPORT_SUCCESS'       => '应用钩子导入成功！',
    'BEHAVIOR_IMPORT_SUCCESS'   => '应用用户行为成功！',
    'DB_USER_PASS_ERROR'        => '数据库账号或密码不正确！',
    'VERIFY_SUCCESS'            => '验证成功！',
    'INNODB_ERROR'              => '数据库账号密码验证通过，但不支持InnoDb！',
    'REQUIRE_ILLEGAL'           => '非法请求方式！',
    'SUCCESS'                   => '成功！',
    'FAILED'                    => '失败！',
    'CREATE_DATA_TABLE'         => '创建数据表',
    'SQL_SUCCESS'               => 'SQL执行成功！',
    'SQL_FAILED'                => 'SQL执行失败！',
    'SITE_INFO_CONFIG_SUCCESS'  => '网站信息配置成功！',
    'ADMIN_ACCOUNT_CREATED'     => '管理员账号创建成功！',
    'DATABASE_INSTALL_FINISHED' => '数据库安装完成！',
    'INSTALL_ERROR_TIP1'        => '安装过程，共',
    'INSTALL_ERROR_TIP2'        => '个SQL执行错误，可能您在此数据库下已经安装过 CMF，请查看问题后重新安装，或者',
    'FEEDBACK_PROBLEM'          => '反馈问题',
    'OK'                        => '确定',
    'CANCEL'                    => '取消'
];
