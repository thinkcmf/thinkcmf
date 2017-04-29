-- 此文件为最新数据库更新文件,相对于 thinkcmf5.sql
-- 2017-04-29 10:16 老猫添加delete_time字段
ALTER TABLE `cmf_comment` ADD `delete_time` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间' AFTER `create_time`;