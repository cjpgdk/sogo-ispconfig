DROP TABLE IF EXISTS `sogo_config`;
DROP TABLE IF EXISTS `sogo_domains`;
DROP TABLE IF EXISTS `sogo_module`;
DROP TABLE IF EXISTS `sogo_plugins`;

DELETE FROM `sys_config` WHERE `group`='interface' AND `name`='sogo_interface';