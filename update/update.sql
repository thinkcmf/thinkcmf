-- 此文件为最新数据库更新文件,相对于 thinkcmf5.sql
-- 2017-05-09 18:16 老猫添加type字段
ALTER TABLE `cmf_route` ADD `type` TINYINT NOT NULL DEFAULT '1' COMMENT 'URL规则类型;1:用户自定义;2:别名添加' AFTER `status`;