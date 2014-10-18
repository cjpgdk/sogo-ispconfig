<?php

/*
 SOGo sudo command to use when executing a SOGo binary
 eg.
 su -p -c '{command}' sogo
 sudo -u sogo {command}
*/
$conf['sogo_su_command'] = 'sudo -u sogo {command}';
/*
  //* full path to sogo binary
  $conf['sogo_binary'] = '/usr/sbin/sogod';
 */
//* full path to sogo-tool binary 
$conf['sogo_tool_binary'] = '/usr/sbin/sogo-tool';
//* name of the database used for SOGo
$conf['sogo_database_name'] = 'dbsogo';
//* name of the database user used for SOGo db
$conf['sogo_database_user'] = 'dbsogo';
//* name of the database user password used for SOGo db
$conf['sogo_database_passwd'] = 'dbsogo';
//* database host where SOGo db is hosted
$conf['sogo_database_host'] = '127.0.0.1';
//* database port number
$conf['sogo_database_port'] = '3306';
//* vars added to the domain template
$conf['sogo_domain_extra_vars'] = array(
    //* password algorithm default is crypt
    //* Possible algorithms are: plain, md5, crypt-md5, sha, ssha (including 256/512 variants),
    'userPasswordAlgorithm' => 'crypt',
    /*
      The default behaviour is to store newly set
      passwords with out the scheme (default: NO). 
      This can be overridden by setting to YES
      and will result in passwords stored as {scheme}encryptedPass
     */
    'prependPasswordScheme' => 'NO',
    //* human identification name of the address book
    'displayName' => 'Users in {domain}',
);
//* sogo default configuration file(s)
$conf['sogo_gnu_step_defaults'] = '/var/lib/sogo/GNUstep/Defaults/.GNUstepDefaults';
$conf['sogo_gnu_step_defaults_sogod.plist'] = '/var/lib/sogo/GNUstep/Defaults/sogod.plist';

/* 
 not integraded but will be 
 template to use for table names in sogo db
*/
$conf['sogo_domain_table_tpl'] = "{domain}_users";
/*
SOGoEncryptionKey ?? if password change shall be enabled.!
*/
