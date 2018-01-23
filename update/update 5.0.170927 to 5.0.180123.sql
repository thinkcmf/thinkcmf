-- 2018-01-03 08:00 改 app 字段长度
ALTER TABLE `cmf_admin_menu` CHANGE `app` `app` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '应用名';


ALTER TABLE `cmf_hook` CHANGE `type` `type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '钩子类型(1:系统钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)';

ALTER TABLE `cmf_hook_plugin` CHANGE `plugin` `plugin` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '插件';

ALTER TABLE `cmf_portal_category` CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分类描述';

-- 2017-12-17 23:25:07 增加操作人字段
ALTER TABLE `cmf_recycle_bin` ADD `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户id';

-- 2017-10-16 21:57 增加余额字段
ALTER TABLE `cmf_user` ADD `balance` DECIMAL(10,2) NOT NULL DEFAULT '0' COMMENT '余额' AFTER `coin`;

-- 2017-10-11 22:19 修复用户生日早于1970年报错
ALTER TABLE `cmf_user` CHANGE `birthday` `birthday` INT NOT NULL DEFAULT '0' COMMENT '生日';


--
-- 表的结构 `cmf_user_balance_log`
--

CREATE TABLE IF NOT EXISTS `cmf_user_balance_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '用户 id',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `change` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '更改余额',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '更改后余额',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户余额变更日志表';

-- 2018-01-12 11:12 更改 content，more 字段
ALTER TABLE `cmf_comment` CHANGE `content` `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '评论内容';
ALTER TABLE `cmf_comment` CHANGE `more` `more` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '扩展属性';

ALTER TABLE `cmf_comment` ADD `like_count` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞数' AFTER `object_id`;
ALTER TABLE `cmf_comment` ADD `dislike_count` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '不喜欢数' AFTER `like_count`;
ALTER TABLE `cmf_comment` ADD `floor` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '楼层数' AFTER `dislike_count`;
