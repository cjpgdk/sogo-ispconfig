<?php

//* class name is not important
class UpdateClass extends PHPUpdateBaseClass {

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
    }

}

//* var named $updateClass must not change
$updateClass = new UpdateClass();
