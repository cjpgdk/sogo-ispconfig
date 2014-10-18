
CREATE TABLE IF NOT EXISTS `sogo_module` (
  `smid` int(2) NOT NULL AUTO_INCREMENT,
  `sys_userid` int(11) unsigned NOT NULL,
  `sys_groupid` int(11) unsigned NOT NULL,
  `sys_perm_user` varchar(5) DEFAULT NULL,
  `sys_perm_group` varchar(5) DEFAULT NULL,
  `sys_perm_other` varchar(5) DEFAULT NULL,
  `all_domains` enum('y','n') NOT NULL DEFAULT 'y',
  `allow_same_instance` enum('y','n') NOT NULL DEFAULT 'y',
  `sql_of_mail_server` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`smid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `sogo_module` (`smid` ,`sys_userid` ,`sys_groupid` ,`sys_perm_user` ,`sys_perm_group` ,`sys_perm_other` ,`all_domains` ,`allow_same_instance` ,`sql_of_mail_server`) VALUES ('1', '1', '0', 'riu', 'riu', NULL , 'y', 'y', 'n');


UPDATE 
    `sys_config`
SET
    `value` = '3' 
WHERE 
    CONVERT( `sys_config`.`group` USING utf8 ) = 'interface' 
    AND CONVERT( `sys_config`.`name` USING utf8 ) = 'sogo_interface' 
    AND CONVERT( `sys_config`.`value` USING utf8 ) = '2' 
    OR CONVERT( `sys_config`.`value` USING utf8 ) = '0.2' LIMIT 1;