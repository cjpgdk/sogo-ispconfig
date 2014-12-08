
-- CHANGE SOGoMailMessageCheck to SOGoRefreshViewCheck
ALTER TABLE `sogo_config` CHANGE `SOGoMailMessageCheck` `SOGoRefreshViewCheck` VARCHAR( 75 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'every_minute';
ALTER TABLE `sogo_domains` CHANGE `SOGoMailMessageCheck` `SOGoRefreshViewCheck` VARCHAR( 75 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'every_minute';
-- Add SOGoIMAPAclStyle
ALTER TABLE `sogo_config` ADD `SOGoIMAPAclStyle` ENUM( 'rfc2086', 'rfc4314' ) NOT NULL DEFAULT 'rfc4314';
ALTER TABLE `sogo_domains` ADD `SOGoIMAPAclStyle` ENUM( 'rfc2086', 'rfc4314' ) NOT NULL DEFAULT 'rfc4314';
-- Add/Change SOGoIMAPAclStyle
ALTER TABLE `sogo_domains` ADD `SOGoForwardEnabled` ENUM( 'YES', 'NO' ) NOT NULL DEFAULT 'NO';
ALTER TABLE `sogo_config` CHANGE `SOGoForwardEnabled` `SOGoForwardEnabled` ENUM( 'YES', 'NO' ) NOT NULL DEFAULT 'NO';
-- Add Microsoft ActiveSync settings
ALTER TABLE `sogo_config` ADD `SOGoMaximumSyncWindowSize` INT NOT NULL DEFAULT '0',
ADD `SOGoMaximumPingInterval` INT NOT NULL DEFAULT '10',
ADD `SOGoMaximumSyncInterval` INT NOT NULL DEFAULT '30',
ADD `SOGoInternalSyncInterval` INT NOT NULL DEFAULT '10';
-- Add SOGoMailShowSubscribedFoldersOnly
ALTER TABLE `sogo_config` ADD `SOGoMailShowSubscribedFoldersOnly` ENUM( 'YES', 'NO' ) NOT NULL DEFAULT 'NO';
ALTER TABLE `sogo_domains` ADD `SOGoMailShowSubscribedFoldersOnly` ENUM( 'YES', 'NO' ) NOT NULL DEFAULT 'NO';

UPDATE 
    `sys_config`
SET
    `value` = '6' 
WHERE 
    CONVERT( `sys_config`.`group` USING utf8 ) = 'interface' 
    AND CONVERT( `sys_config`.`name` USING utf8 ) = 'sogo_interface' 
    AND CONVERT( `sys_config`.`value` USING utf8 ) = '5' LIMIT 1;