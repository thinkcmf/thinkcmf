-- 2018年06月25日07:44:44 增加后天失败次数限制。避免暴力破解
CREATE TABLE `cmf_failedlogins` (
  `ip` char(15) CHARACTER SET gbk NOT NULL DEFAULT '',
  `count` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;