UPDATE 
    `sys_config` 
SET 
    `group` = 'interface',
    `name` = 'sogo_interface',
    `value` = '2' 
WHERE 
    `sys_config`.`config_id` =1 
AND CONVERT( `sys_config`.`group` USING utf8 ) = 'addons' 
AND CONVERT( `sys_config`.`name` USING utf8 ) = 'sogo' 
AND CONVERT( `sys_config`.`value` USING utf8 ) = '0.1' LIMIT 1;