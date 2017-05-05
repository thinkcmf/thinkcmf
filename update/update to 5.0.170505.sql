-- 此文件为最新数据库更新文件,相对于 thinkcmf5.sql
-- 2017-04-29 10:16 老猫添加delete_time字段
ALTER TABLE `cmf_comment` ADD `delete_time` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间' AFTER `create_time`;

-- 2017-05-04 15:14 老猫更改 hook字段长度
ALTER TABLE `cmf_hook` CHANGE `hook` `hook` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '钩子';

-- 2017-05-04 20:05 老猫增加钩子
DROP TABLE IF EXISTS `cmf_hook`;
CREATE TABLE `cmf_hook` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '钩子类型(1:系统钩子;2:应用钩子;3:模板钩子)',
  `once` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否只允许一个插件运行(0:多个;1:一个)',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `hook` varchar(30) NOT NULL DEFAULT '' COMMENT '钩子',
  `app` varchar(15) NOT NULL DEFAULT '' COMMENT '应用名(只有应用钩子才用)',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COMMENT='系统钩子表';
BEGIN;
INSERT INTO `cmf_hook` VALUES ('1', '1', '0', '应用初始化', 'app_init', 'cmf', '应用初始化'), ('2', '1', '0', '应用开始', 'app_begin', 'cmf', '应用开始'), ('3', '1', '0', '模块初始化', 'module_init', 'cmf', '模块初始化'), ('4', '1', '0', '控制器开始', 'action_begin', 'cmf', '控制器开始'), ('5', '1', '0', '视图输出过滤', 'view_filter', 'cmf', '视图输出过滤'), ('6', '1', '0', '应用结束', 'app_end', 'cmf', '应用结束'), ('7', '1', '0', '日志write方法', 'log_write', 'cmf', '日志write方法'), ('8', '1', '0', '输出结束', 'response_end', 'cmf', '输出结束'), ('9', '1', '0', '后台控制器初始化', 'admin_init', 'cmf', '后台控制器初始化'), ('10', '1', '0', '前台控制器初始化', 'home_init', 'cmf', '前台控制器初始化'), ('11', '1', '1', '发送手机验证码', 'send_mobile_verification_code', 'cmf', '发送手机验证码'), ('12', '3', '0', '模板 body标签开始', 'body_start', '', '模板 body标签开始'), ('13', '3', '0', '模板 head标签结束前', 'before_head_end', '', '模板 head标签结束前'), ('14', '3', '0', '模板底部开始', 'footer_start', '', '模板底部开始'), ('15', '3', '0', '模板底部开始之前', 'before_footer', '', '模板底部开始之前'), ('16', '3', '0', '模板底部结束之前', 'before_footer_end', '', '模板底部结束之前'), ('17', '3', '0', '模板 body 标签结束之前', 'before_body_end', '', '模板 body 标签结束之前'), ('18', '3', '0', '模板左边栏开始', 'left_sidebar_start', '', '模板左边栏开始'), ('19', '3', '0', '模板左边栏结束之前', 'before_left_sidebar_end', '', '模板左边栏结束之前'), ('20', '3', '0', '模板右边栏开始', 'right_sidebar_start', '', '模板右边栏开始'), ('21', '3', '0', '模板右边栏结束之前', 'before_right_sidebar_end', '', '模板右边栏结束之前'), ('22', '3', '0', '评论区', 'comment', '', '评论区'), ('23', '3', '0', '留言区', 'guestbook', '', '留言区');
COMMIT;

-- 2017-05-05 07:14 老猫更改 hook字段长度
ALTER TABLE `cmf_hook_plugin` CHANGE `hook` `hook` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '钩子';


