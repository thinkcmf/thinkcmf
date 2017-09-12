-- 2017-09-06 10:35更正 admin_menu表list_order 字段类型
ALTER TABLE `cmf_admin_menu` CHANGE `list_order` `list_order` FLOAT NOT NULL DEFAULT '10000' COMMENT '排序';

-- 2017-09-07 13:35 增加用户表 more 字段
ALTER TABLE `cmf_user` ADD `more` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '扩展属性' AFTER `mobile`;

-- 2017-09-07 14:03 增加after_content钩子
INSERT INTO `cmf_hook` ( `hook`, `type`, `once`, `name`, `description`) VALUES ( 'after_content', '3', '0', '主要内容之后', '主要内容之后');


-- 2017-09-12 12:38 更改字段长度
ALTER TABLE `cmf_hook` CHANGE `hook` `hook` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '钩子';
ALTER TABLE `cmf_hook` CHANGE `name` `name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '钩子名称';
ALTER TABLE `cmf_hook_plugin` CHANGE `hook` `hook` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '钩子名';