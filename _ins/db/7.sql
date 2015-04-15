

-- Add server_id to sogo_module
ALTER TABLE `sogo_module` ADD `server_id` INT( 11 ) NOT NULL AFTER `sys_perm_other` ,
ADD UNIQUE (`server_id`);
-- Remove 'sql_of_mail_server' form sogo_module
ALTER TABLE `sogo_module` DROP `sql_of_mail_server`;

UPDATE 
    `sys_config`
SET
    `value` = '7' 
WHERE 
    CONVERT( `sys_config`.`group` USING utf8 ) = 'interface' 
    AND CONVERT( `sys_config`.`name` USING utf8 ) = 'sogo_interface' 
    AND CONVERT( `sys_config`.`value` USING utf8 ) = '6' LIMIT 1;