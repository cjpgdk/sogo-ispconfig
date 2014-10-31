 -- change localhost and 127.0.0.1 to {SERVERNAME}
ALTER TABLE `sogo_config` CHANGE `SOGoSieveServer` `SOGoSieveServer` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'sieve://{SERVERNAME}:4190',
CHANGE `SOGoIMAPServer` `SOGoIMAPServer` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'imaps://{SERVERNAME}:143/?tls=YES',
CHANGE `SOGoSMTPServer` `SOGoSMTPServer` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '{SERVERNAME}'


UPDATE 
    `sys_config`
SET
    `value` = '5' 
WHERE 
    CONVERT( `sys_config`.`group` USING utf8 ) = 'interface' 
    AND CONVERT( `sys_config`.`name` USING utf8 ) = 'sogo_interface' 
    AND CONVERT( `sys_config`.`value` USING utf8 ) = '4' LIMIT 1;