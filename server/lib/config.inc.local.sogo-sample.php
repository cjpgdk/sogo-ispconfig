<?php

/*
  method to use when generating the unique id for he domain
  "" sogo domain config key "id"

  Supported PHP default medthods are
  - md5, sha1, crypt, crc32
  propperply more but these are widely used.

  if you like to use the domain name as is
  without encoding use "plain"

  rule of thumb the encoding method must take one argument
  and be available as procedural code and return the result

  md5("domain-name.com");
  sha1("domain-name.com");
  crypt("domain-name.com");

  if not isset md5 is used

 * *** side note the resulting string is used with sogo-integrator to identify the domain 
 */
$conf['sogo_unique_id_method'] = 'md5';

//* SOGo system user name
$conf['sogo_system_user'] = 'sogo';
//* SOGo system group name
$conf['sogo_system_group'] = 'sogo';
/*
  SOGo sudo command to use when executing a SOGo binary
  eg.
  su -p -c '{command}' sogo
  sudo -u sogo {command}
 * *** if you must quote the command ONLY USE ' (Single quote) NOT " (Double quote)
 */
$conf['sogo_su_command'] = 'sudo -u ' . $conf['sogo_system_user'] . ' {command}';
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
    /*
      password algorithm default is CRYPT
      Possible algorithms are: plain, MD5, CRYPT-MD5, SHA, SSHA (including 256/512 variants)
     */
    'userPasswordAlgorithm' => 'CRYPT',
    /*
      The default behaviour is to store newly set
      passwords with out the scheme (default: NO).
      This can be overridden by setting to YES
      and will result in passwords stored as {scheme}encryptedPass
     */
    'prependPasswordScheme' => 'NO',
    //* human identification name of the address book
    'displayName' => 'Users in {domain}',
    /*
      needs patched SOGo.
      allows the users to change password from SOGo Web Interface
      i created a patch for sogo located in the doc/extra/pwchange
      folder, the patch will add this setting to SOGo config users
      source directive this will allow SOGo to update the password
      in ISPConfig and SOGo, no pasword sync needed.
     */
    /* 'ISPConfigUrlPassword'=>"{$conf['db_type']}://{$conf['db_user']}:{$conf['db_password']}@{$conf['db_host']}:3306/{$conf['db_database']}/mail_user" */
);
//* sogo default configuration file(s)
$conf['sogo_gnu_step_defaults'] = '/var/lib/sogo/GNUstep/Defaults/.GNUstepDefaults';
$conf['sogo_gnu_step_defaults_sogod.plist'] = '/var/lib/sogo/GNUstep/Defaults/sogod.plist';
$conf['sogo_system_default_conf'] = '/etc/sogo/sogo.conf';

//* template to use for table names in sogo db
$conf['sogo_domain_table_tpl'] = "{domain}_users";

//* define if we use the old way for domain tables.
$conf['sogo_tb_compatibility'] = false;
