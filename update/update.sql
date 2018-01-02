-- 2017-10-11 22:19 修复用户生日早于1970年报错
ALTER TABLE `cmf_user` CHANGE `birthday` `birthday` INT NOT NULL DEFAULT '0' COMMENT '生日';

-- 2017-10-16 21:57 增加余额字段
ALTER TABLE `cmf_user` ADD `balance` DECIMAL(10,2) NOT NULL DEFAULT '0' COMMENT '余额' AFTER `coin`;

INSERT INTO `cmf_hook` (`id`, `type`, `once`, `name`, `hook`, `app`, `description`)
VALUES
	(NULL , 1, 0, '日志写入完成', 'log_write_done', 'cmf', '日志写入完成');


-- 2017-12-17 23:25:07 增加操作人字段
ALTER TABLE `cmf_recycle_bin` ADD `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户id';

-- 2018-01-03 08:00 改 app 字段长度
ALTER TABLE `cmf_admin_menu` CHANGE `app` `app` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '应用名';