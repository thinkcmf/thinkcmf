-- 更正 admin_menu表list_order 字段类型
ALTER TABLE `cmf_admin_menu` CHANGE `list_order` `list_order` FLOAT NOT NULL DEFAULT '10000' COMMENT '排序';