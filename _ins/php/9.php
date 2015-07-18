<?php

//* class name is not important
class UpdateClass9 extends PHPUpdateBaseClass {

    public function __construct() {/* INIT your php update here */
    }

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

        $del_files = array(
            $ispchome . '/interface/web/mail/templates/sogo_domains_reseller_edit.htm'
        );

        echo PHP_EOL;
        echo "Some file have bean removed! i need your comfimation bere deleting them" . PHP_EOL;
        echo "Delete the files? [y/n] (y)" . PHP_EOL;
        if (strtolower(Installer::readInput('y')) == 'y') {
            foreach ($del_files as $value) {
                echo "{$value}" . PHP_EOL;
                echo "Delete: [y/n] (y)" . PHP_EOL;
                if (strtolower(Installer::readInput('y')) == 'y') {
                    @unlink($value);
                }
                echo PHP_EOL;
            }
        }
    }

}

//* var named $updateClass must not change
$updateClass = new UpdateClass9();
