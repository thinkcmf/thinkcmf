ALTER TABLE `cmf_admin_menu` CHANGE `app` `app` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '应用名';
ALTER TABLE `cmf_admin_menu` CHANGE `action` `action` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作名称';
ALTER TABLE `cmf_admin_menu` CHANGE `name` `name` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '菜单名称';
ALTER TABLE `cmf_admin_menu` CHANGE `icon` `icon` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '菜单图标';

ALTER TABLE `cmf_asset` CHANGE `file_path` `file_path` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件路径,相对于upload目录,可以为url';

ALTER TABLE `cmf_auth_rule` CHANGE `app` `app` VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '规则所属module';
ALTER TABLE `cmf_auth_rule` CHANGE `param` `param` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '额外url参数';
ALTER TABLE `cmf_auth_rule` CHANGE `condition` `condition` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '规则附加条件';


ALTER TABLE `cmf_comment` CHANGE `table_name` `table_name` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '评论内容所在表，不带表前缀';
ALTER TABLE `cmf_comment` CHANGE `url` `url` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '原文地址' AFTER `path`;
ALTER TABLE `cmf_comment` CHANGE `path` `path` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '层级关系';

ALTER TABLE `cmf_hook` CHANGE `app` `app` VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用名(只有应用钩子才用)';


ALTER TABLE `cmf_link` CHANGE `target` `target` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '友情链接打开方式';
ALTER TABLE `cmf_link` CHANGE `name` `name` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '友情链接名称';
ALTER TABLE `cmf_link` CHANGE `image` `image` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '友情链接图标';
ALTER TABLE `cmf_link` CHANGE `rel` `rel` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '链接与网站的关系';
ALTER TABLE `cmf_nav` CHANGE `name` `name` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '导航位置名称';


ALTER TABLE `cmf_nav_menu` CHANGE `target` `target` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '打开方式';
ALTER TABLE `cmf_nav_menu` CHANGE `icon` `icon` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '图标';
ALTER TABLE `cmf_nav_menu` CHANGE `href` `href` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '链接';

ALTER TABLE `cmf_plugin` CHANGE `author` `author` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '插件作者';

ALTER TABLE `cmf_portal_category` CHANGE `path` `path` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分类层级关系路径';
ALTER TABLE `cmf_portal_post` CHANGE `post_title` `post_title` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'post标题';

ALTER TABLE `cmf_portal_tag` CHANGE `name` `name` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签名称';

ALTER TABLE `cmf_slide` CHANGE `remark` `remark` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分类备注';

ALTER TABLE `cmf_slide_item` CHANGE `title` `title` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '幻灯片名称';
ALTER TABLE `cmf_slide_item` CHANGE `target` `target` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '友情链接打开方式';

ALTER TABLE `cmf_theme` CHANGE `name` `name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '主题名称';
ALTER TABLE `cmf_theme` CHANGE `theme` `theme` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '主题目录名，用于主题的维一标识';

ALTER TABLE `cmf_theme_file` CHANGE `theme` `theme` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '模板名称';
ALTER TABLE `cmf_theme_file` CHANGE `name` `name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '模板文件名';
ALTER TABLE `cmf_third_party_user` ADD `nickname` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户昵称' AFTER `status`;

ALTER TABLE `cmf_user_favorite` CHANGE `title` `title` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '收藏内容的标题';

ALTER TABLE `cmf_user_favorite` CHANGE `table_name` `table_name` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收藏实体以前所在表,不带前缀';
ALTER TABLE `cmf_verification_code` CHANGE `account` `account` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号或者邮箱';











