

CREATE TABLE IF NOT EXISTS `sogo_plugins` (
  `spid` bigint(20) NOT NULL AUTO_INCREMENT,
  `sys_userid` int(11) NOT NULL DEFAULT '0',
  `sys_groupid` int(11) NOT NULL DEFAULT '0',
  `sys_perm_user` varchar(5) NOT NULL DEFAULT 'NULL',
  `sys_perm_group` varchar(5) NOT NULL DEFAULT 'NULL',
  `sys_perm_other` varchar(5) NOT NULL DEFAULT 'NULL',
  `active` varchar(255) NOT NULL DEFAULT 'y',
  `client_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT 'Plugin Name',
  `description` text,
  `filetype` enum('download','link') NOT NULL DEFAULT 'download',
  `file` text,
  PRIMARY KEY (`spid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 -- some default sample values
INSERT INTO `sogo_plugins` (`spid`, `sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other`, `active`, `client_id`, `name`, `description`, `filetype`, `file`) VALUES
(1, 1, 1, 'riud', 'riud', 'r', 'y', 0, 'Mozilla Thunderbird 31 - Lightning - Official release 3.3.1', 'Mozilla Lightning - Official release 3.3.1\r\nis a calendar plugin that woks well with SOGo integrator\r\n\r\nRequirements\r\nSOGo Connector Thunderbird extension, Version 31.0.0\r\nAND\r\nSOGo Integrator Thunderbird extension, Version 31.0.0', 'link', 'https://addons.mozilla.org/en-US/thunderbird/addon/lightning/versions/3.3.1'),
(2, 1, 1, 'riud', 'riud', 'r', 'y', 0, 'Thunderbird 31 extension - SOGo Connector', 'SOGo Connector Thunderbird extension\r\nVersion 31.0.0 (released on September 26th 2014)\r\n\r\nif you are going to use Thunderbird you need this extension along with "SOGo Integrator" to fully integrate with SOGo', 'download', 'sogo-connector-31.0.0.xpi'),
(3, 1, 1, 'riud', 'riud', 'r', 'y', 0, 'Thunderbird 31 - SOGo Integrator', 'SOGo Connector Thunderbird extension\r\nVersion 31.0.0\r\n\r\nif you are going to use Thunderbird you need this extension along with "SOGo Integrator" to fully integrate with SOGo', 'download', 'sogo-integrator-31.0.0-sogo-demo.xpi');


UPDATE 
    `sys_config`
SET
    `value` = '4' 
WHERE 
    CONVERT( `sys_config`.`group` USING utf8 ) = 'interface' 
    AND CONVERT( `sys_config`.`name` USING utf8 ) = 'sogo_interface' 
    AND CONVERT( `sys_config`.`value` USING utf8 ) = '3' LIMIT 1;