-- 增缩略图字段
ALTER TABLE `cmf_portal_post` ADD `thumbnail` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '缩略图' AFTER `post_source`;

ALTER TABLE `cmf_auth_rule` CHANGE `app` `app` VARCHAR(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '规则所属app';