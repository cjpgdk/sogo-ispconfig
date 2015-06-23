UPDATE 
    `sys_config`
SET
    `value` = '8' 
WHERE 
    CONVERT( `sys_config`.`group` USING utf8 ) = 'interface' 
    AND CONVERT( `sys_config`.`name` USING utf8 ) = 'sogo_interface' 
    AND CONVERT( `sys_config`.`value` USING utf8 ) = '7' LIMIT 1;