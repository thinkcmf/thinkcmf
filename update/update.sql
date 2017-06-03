-- 2017-06-03 12:43 增加插件链接
ALTER TABLE `cmf_plugin` ADD `demo_url` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '演示地址，带协议' AFTER `title`;
ALTER TABLE `cmf_plugin` ADD `author_url` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '作者网站链接' AFTER `author`;