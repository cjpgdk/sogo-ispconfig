<?php

//* class name is not important
class UpdateClass8 extends PHPUpdateBaseClass {

    public function __construct() {/* INIT your php update here */}

    /**
     * method executed when updateing
     * @global db $db ISPConfig database object
     * @global string $ispchome ISPConfig install dir eg. /usr/local/ispconfig
     * @global array $conf ISPConfig config from "$ispchome . /server/lib/config.inc.php"
     * @global string $clientdb_host
     * @global string $clientdb_user
     * @global string $clientdb_password
     * return void
     */
    public function run() {
        //* default variables loaded/isset by the update script
        global $db, $ispchome, $conf, $clientdb_host, $clientdb_user, $clientdb_password;
        /* Perform all update taskes here */
        echo "\n\n".  str_repeat("=", 25)."\n\n";
        echo "The following new server configuration variables have\nbeen added";
        echo " to the scripts, it is your job to add them to your\nconfiguration";
        echo " in\n'/usr/local/ispconfig/server/lib/config.inc.local.php'";
        echo "\n\n";
        echo "/*
  method to use when generating the unique id for he domain
  \"\" sogo domain config key \"id\"

  Supported PHP default medthods are
  - md5, sha1, crypt, crc32
  propperply more but these are widely used.

  if you like to use the domain name as is
  without encoding use \"plain\"

  rule of thumb the encoding method must take one argument
  and be available as procedural code and return the result

  md5(\"domain-name.com\");
  sha1(\"domain-name.com\");
  crypt(\"domain-name.com\");

  if not isset md5 is used

 **** side note the resulting string is used with sogo-integrator to identify the domain 
 */
\$conf['sogo_unique_id_method'] = 'md5';

//* SOGo system user name
\$conf['sogo_system_user'] = 'sogo';
//* SOGo system group name
\$conf['sogo_system_group'] = 'sogo';

\$conf['sogo_system_default_conf'] = '/etc/sogo/sogo.conf';
";
        echo "\n\n".  str_repeat("=", 25)."\n\n";
    }

}

//* var named $updateClass must not change
$updateClass = new UpdateClass8();
