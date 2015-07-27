<?php

/**
 * Copyright (C) 2015  Christian M. Jensen
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Christian M. Jensen <christian@cmjscripter.net>
 * @copyright 2015 Christian M. Jensen
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 * @link https://github.com/cmjnisse/sogo-ispconfig original source code for sogo-ispconfig
 * 
 * @todo Add ask for sogo system user and group name
 */
class Installer {

    /**
     * list of files to copy
     * @var array 
     */
    static public $files_copy = array();
    static private $mysql_tables_ispc = "_ins/tables.sql";

    /** @var string location of ISPConfig home dir */
    static public $ispc_home_dir = "";

    /** @var boolean have ISPConfig interface folder */
    static private $has_ispconfig_interface_folder = FALSE;

    /** @var boolean have ISPConfig server folder */
    static private $has_ispconfig_server_folder = FALSE;

    /** @var string location of SOGo init script */
    static private $sogo_init_script = "";

    /** @var string location of "sogo-tool" binary */
    static private $sogo_tool_binary = "";

    /** @var string location of SOGo home dir */
    static private $sogo_home_dir = "";

    /** @var array collection of errors doing this run */
    static public $errors = array();

    /** @var array last error */
    static public $error = "";

    /** @var boolean a var used as indicator if SOGo is located */
    static public $have_signs_of_sogo = FALSE;

    /** @var boolean a var used to check if errors for individual steps */
    static private $isError = FALSE;

    /** @var boolean a var used to detirming if server module and plugin shall be enabled */
    static public $config_server = FALSE;
    static public $type = "all";

    public function __construct($type = "all") {
        self::$type = strtolower($type); //* no significant use at this point but must be (slave|all|mysqltables)
        //* if sql tables only no need for the rest
        if (self::$type == 'mysqltables') {
            self::$isError = FALSE;
            return TRUE;
        }
        //* get sogo home dir
        self::$ispc_home_dir = Installer::getISPConfigHomeDir();
        if (!empty(self::$ispc_home_dir) && is_dir(self::$ispc_home_dir)) {
            if (is_dir(self::$ispc_home_dir . '/interface')) {
                self::$has_ispconfig_interface_folder = TRUE;
            } else {
                self::$error = "[FAIL]: Unable to locate ISPConfig interface folder in [" . self::$ispc_home_dir . "/interface]";
                self::$errors['step1'][] = self::$error;
                echo self::$error . PHP_EOL;
                self::$has_ispconfig_interface_folder = FALSE;
            }
            if (is_dir(self::$ispc_home_dir . '/server')) {
                self::$has_ispconfig_server_folder = TRUE;
            } else {
                self::$error = "[FAIL]: Unable to locate ISPConfig server folder in [" . self::$ispc_home_dir . "/server]";
                self::$errors['step1'][] = self::$error;
                echo self::$error . PHP_EOL;
                self::$has_ispconfig_server_folder = FALSE;
            }
            if (!self::$has_ispconfig_server_folder || !self::$has_ispconfig_interface_folder) {
                echo "Continue (Y/N) [N]: ";
                if (strtolower(self::readInput("n")) == "n") {
                    echo PHP_EOL;
                    echo "okay goodbye" . PHP_EOL;
                    exit();
                }
            }
        } else {
            die("Folder [" . self::$ispc_home_dir . "] is not valid ISPConfig installation." . PHP_EOL);
        }
        $insSoGo = TRUE;
        if (Installer::isSOGoOnServer()) {
            echo PHP_EOL . "I Found SOGo on your server is this true? (Y/N) [Y]: ";
            if (strtolower(self::readInput("y")) == "y")
                $insSoGo = FALSE;
        }
        if ($insSoGo) {
            echo PHP_EOL . "Do you whant me to install SOGo? (Y/N) [Y]: ";
            if (strtolower(self::readInput("y")) == "y") {
                $SOGo = new SOGo();
                $SOGo->run();
            }
        }
        unset($insSoGo, $SOGo);

        self::$isError = FALSE;

        echo "Do you intend to run SOGo from this server? (Y/N) [Y]: ";
        if (strtolower(self::readInput("y")) == "y") {
            //* get init script for sogo
            self::$sogo_init_script = Installer::getSOGoInitScript();
            //* locate sogo-tool binary
            self::$sogo_tool_binary = Installer::getSOGoToolBinary();
            //* get sogo home dir
            self::$sogo_home_dir = Installer::getSOGoHomeDir();
            self::$_server_config_local = str_replace('{SOGOTOOLBIN}', self::$sogo_tool_binary, self::$_server_config_local);
            self::$_server_config_local = str_replace('{SOGOHOMEDIR}', self::$sogo_home_dir, self::$_server_config_local);
            //* SOGo init script ?
            if (empty(self::$sogo_init_script)) {
                self::$error = "[FAIL]: Unable to locate SOGo init script";
                self::$errors['step1'][] = self::$error;
                self::$isError = TRUE;
                self::$have_signs_of_sogo = FALSE;
            } else if (file_exists(self::$sogo_init_script)) {
                self::$have_signs_of_sogo = TRUE;
            }
            //* sogo-tool binary?
            if (empty(self::$sogo_tool_binary)) {
                self::$error = "[FAIL]: Unable to locate sogo-tool";
                self::$errors['step1'][] = self::$error;
                self::$have_signs_of_sogo &= FALSE;
            } else if (file_exists(self::$sogo_tool_binary)) {
                self::$have_signs_of_sogo &= TRUE;
            }
            //* SOGo home dir ?s
            if (empty(self::$sogo_home_dir)) {
                self::$error = "[FAIL]: Unable to locate sogo home dir";
                self::$errors['step1'][] = self::$error;
                self::$isError = TRUE;
                self::$have_signs_of_sogo &= FALSE;
            } else if (file_exists(self::$sogo_home_dir) && is_dir(self::$sogo_home_dir)) {
                self::$have_signs_of_sogo &= TRUE;
            }
        }
    }

    public function run() {
        //* any errors!?
        if (self::$isError) {
            echo PHP_EOL;
            echo "One or more errors were found during initialization" . PHP_EOL;
            self::dumbErrors('step1');
            echo "Continue (Y/N) [N]: ";
            if (strtolower(self::readInput("n")) == "n") {
                echo PHP_EOL;
                echo "okay goodbye" . PHP_EOL;
                exit();
            }
            echo PHP_EOL;
            self::$isError = FALSE; //* reset
        }
        if (self::$type == "all" || self::$type == "slave") {
            $this->installInterface();
            $this->installServer();
            echo PHP_EOL . "Setup basic Apache vhost (Y/N) [Y]: ";
            if (strtolower(self::readInput("y")) == "y")
                ApacheVhost::Run();
            else {
                echo PHP_EOL . "Setup basic Nginx vhost (Y/N) [Y]: ";
                if (strtolower(self::readInput("y")) == "y")
                    NginxVhost::Run();
            }
        } elseif (self::$type == 'mysqltables') {
            $this->installTablesMySQL();
        }
    }

    /**
     * copy files for server
     */
    public function installServer() {
        if (self::$has_ispconfig_server_folder) {
            echo "Installing server files" . PHP_EOL;
            self::copyFiles('server');

            echo "Enable SOGo Module and Plugin?" . PHP_EOL;
            echo "\t - Only do this if SOGo is install on this server" . PHP_EOL;
            echo "? (Y/N) [Y]: ";
            self::$config_server = (strtolower(self::readInput("y")) == 'y' ? TRUE : FALSE);
            if (self::$config_server) {
                if (!is_link(self::$ispc_home_dir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
                    if (!symlink(self::$ispc_home_dir . '/server/plugins-available/sogo_plugin.inc.php', self::$ispc_home_dir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
                        echo str_repeat('=', 8) . PHP_EOL;
                        echo "Unable to enable sogo_plugin" . PHP_EOL;
                        echo "Try!" . PHP_EOL;
                        echo "ln -s " . self::$ispc_home_dir . "/server/plugins-available/sogo_plugin.inc.php " . self::$ispc_home_dir . "server/plugins-enabled/sogo_plugin.inc.php" . PHP_EOL;
                        echo str_repeat('=', 8) . PHP_EOL;
                    }
                }
                if (!is_link(self::$ispc_home_dir . '/server/mods-enabled/sogo_module.inc.php')) {
                    if (!symlink(self::$ispc_home_dir . '/server/mods-available/sogo_module.inc.php', self::$ispc_home_dir . '/server/mods-enabled/sogo_module.inc.php')) {
                        echo str_repeat('=', 8) . PHP_EOL;
                        echo "Unable to enable sogo_module" . PHP_EOL;
                        echo "Try!" . PHP_EOL;
                        echo "ln -s " . self::$ispc_home_dir . "/server/mods-available/sogo_module.inc.php " . self::$ispc_home_dir . "/server/mods-enabled/sogo_module.inc.php" . PHP_EOL;
                        echo str_repeat('=', 8) . PHP_EOL;
                    }
                }
                //* create sogo local config in server
                @file_put_contents(self::$ispc_home_dir . '/server/lib/config.inc.local.sogo-sample.php', self::$_server_config_local);
            }
        } else {
            echo "Not installing server files, server folder not found" . PHP_EOL;
        }
    }

    public static function copyFiles($index) {
        if (isset(self::$files_copy[$index])) {
            if ($index == "interface" && !is_dir(self::$ispc_home_dir . '/' . $index . '/web/mail/lib/menu.d/')) {
                @mkdir(self::$ispc_home_dir . '/' . $index . '/web/mail/lib/menu.d/');
            }
            if ($index == "interface" && !is_dir(self::$ispc_home_dir . '/' . $index . '/web/admin/lib/menu.d/')) {
                @mkdir(self::$ispc_home_dir . '/' . $index . '/web/admin/lib/menu.d/');
            }
            foreach (self::$files_copy[$index] as $file) {
                if (file_exists($index . '/' . $file)) {
                    if (!copy($index . '/' . $file, self::$ispc_home_dir . '/' . $index . '/' . $file)) {
                        self::$error = "Faild to copy file [{$index}/{$file}] to " . self::$ispc_home_dir . "/{$index}/{$file}";
                        self::$errors['file_copy_' . $index][] = self::$error;
                        self::$isError = TRUE;
                    }
                } else {
                    self::$error = "File [{$index}/{$file}] not found in plugin folder";
                    self::$errors['file_copy_' . $index][] = self::$error;
                    self::$isError = TRUE;
                }
            }
        } else {
            echo "Files index [{$index}] doesn't exists" . PHP_EOL;
        }
        //* errors?
        if (self::$isError) {
            echo "One or more errors were found during file copy" . PHP_EOL;
            self::dumbErrors('file_copy_' . $index);
            echo "Continue (Y/N) [N]: ";
            if (strtolower(self::readInput("n")) == "n") {
                echo PHP_EOL;
                die("okay goodbye" . PHP_EOL);
            }
            self::$isError = FALSE;
        }
    }

    public function dumbErrors($step) {
        if (isset(self::$errors[$step])) {
            echo str_repeat('=', 8) . PHP_EOL;
            echo implode(PHP_EOL, self::$errors[$step]) . PHP_EOL;
            echo str_repeat('=', 8) . PHP_EOL;
        }
    }

    /**
     * copy files for interface
     */
    public function installInterface() {
        if (self::$has_ispconfig_interface_folder) {
            echo "Installing interface files" . PHP_EOL;
            self::copyFiles('interface');
        } else {
            echo "Not installing interface files, interface folder not found" . PHP_EOL;
        }
        $this->installTablesMySQL();
    }

    public function installTablesMySQL() {
        if (file_exists(self::$mysql_tables_ispc)) {
            //* add mysql tables
            echo "MySQL Host? [127.0.0.1]: ";
            $mysql_host = self::readInput("127.0.0.1");
            echo PHP_EOL;
            echo "MySQL admin user? [root]: ";
            $mysql_admin = self::readInput("root");
            echo PHP_EOL;
            echo "MySQL password? []: ";
            $mysql_password = str_replace('"', '\"', self::readInput(""));
            echo PHP_EOL;
            echo "ISPConfig database? [dbispconfig]: ";
            $mysql_database = self::readInput("dbispconfig");
            echo PHP_EOL;
            $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\" {$mysql_database} < " . self::$mysql_tables_ispc;
            echo exec($command) . PHP_EOL;


            echo PHP_EOL . "Add SOGo database? (Y/N) [Y]:";
            if (strtolower(self::readInput("y")) == "y") {
                echo PHP_EOL . "SOGo database? [dbsogo]: ";
                $sogo_database = self::readInput("dbsogo");

                echo PHP_EOL . "SOGo database user? [sogo]: ";
                $sogo_user = self::readInput("sogo");

                $passwd = sha1(md5('Jeg er en Nisse og mit navn er Udvikler?-' . microtime(true)));
                echo PHP_EOL . "SOGo database user password? [{$passwd}]: ";
                $sogo_passwd = self::readInput($passwd);

                $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\"  -e \"CREATE USER '{$sogo_user}'@'localhost' IDENTIFIED BY '{$sogo_passwd}';\"";
                echo exec($command) . PHP_EOL;

                $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\"  -e \"GRANT USAGE ON * . * TO '{$sogo_user}'@'localhost' IDENTIFIED BY '{$sogo_passwd}' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;\"";
                echo exec($command) . PHP_EOL;

                $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\"  -e \"CREATE DATABASE IF NOT EXISTS {$sogo_database} ;\"";
                echo exec($command) . PHP_EOL;

                $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\"  -e \"GRANT ALL PRIVILEGES ON {$sogo_database}. * TO '{$sogo_user}'@'localhost';\"";
                echo exec($command) . PHP_EOL;

                self::$_server_config_local = str_replace('{SOGODB}', $sogo_database, self::$_server_config_local);
                self::$_server_config_local = str_replace('{SOGODBUSER}', $sogo_user, self::$_server_config_local);
                self::$_server_config_local = str_replace('{SOGODBPW}', $sogo_passwd, self::$_server_config_local);
            }
        } else {
            self::$error = "[FAIL]: Unable to locate mysql tables file (interface, plugin and module WILL NOT WORK without them)" . PHP_EOL . "Redownload the tables and import them manualy before using";
            self::$errors['mysql'][] = self::$error;
            self::$isError = TRUE;
        }
        if (self::$isError) {
            echo "One or more errors were found importing mysql tables" . PHP_EOL;
            self::dumbErrors('mysql');
            echo "Continue (Y/N) [N]: ";
            if (strtolower(self::readInput("n")) == "n") {
                echo PHP_EOL;
                die("okay goodbye" . PHP_EOL);
            }
            self::$isError = FALSE;
        }
    }

    /**
     * look for SOGo binarys
     * @return boolean
     */
    public static function isSOGoOnServer() {
        $sogo_tool_binary = exec("which sogo-tool");
        $sogo_binary = exec("which sogod");
        if ((!empty($sogo_tool_binary) && file_exists($sogo_tool_binary)) && (!empty($sogo_binary) && file_exists($sogo_binary))) {
            return true;
        }
        return false;
    }

    /**
     * locate ISP Config home dir
     * @return string
     */
    public static function getISPConfigHomeDir() {
        echo "location of ISPConfig folder? [/usr/local/ispconfig]: ";
        $ispcdir = self::readInput("/usr/local/ispconfig");
        return $ispcdir;
    }

    /**
     * get sogo home dir location
     * @return string
     */
    public static function getSOGoHomeDir() {
        $sogo_home_dir = exec("getent passwd sogo | cut -d: -f6");
        echo "location of SOGo home dir [{$sogo_home_dir}]: ";
        $sogo_home_dir = self::readInput($sogo_home_dir);
        return $sogo_home_dir;
    }

    /**
     * get sogo-tool location
     * @return string
     */
    public static function getSOGoToolBinary() {
        $sogo_tool_binary = exec("which sogo-tool");
        echo "location of sogo-tool [{$sogo_tool_binary}]: ";
        $sogo_tool_binary = self::readInput($sogo_tool_binary);
        return $sogo_tool_binary;
    }

    /**
     * get init script location for SOGo
     * @return string
     */
    public static function getSOGoInitScript() {
        if (file_exists("/etc/init.d/sogo"))
            $sogo_init_script = "/etc/init.d/sogo";
        else if (file_exists("/etc/init.d/sogod"))
            $sogo_init_script = "/etc/init.d/sogod";
        else
            $sogo_init_script = "";
        echo "location of SOGo init script [{$sogo_init_script}]: ";
        $sogo_init_script = self::readInput($sogo_init_script);
        return $sogo_init_script;
    }

    /**
     * Read input from stdin
     * @param string $default
     * @return string
     */
    public static function readInput($default = "") {
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        return (!empty($line) && trim($line) != "" ? trim($line) : $default);
    }

    private static $_server_config_local = <<< EOF
<?php

/*
method to use when generating the unique id for the domain
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

 **** side note the resulting string is used with sogo-integrator to identify the domain 
 */ 
\$conf['sogo_unique_id_method'] = 'md5';

//* SOGo system user name
\$conf['sogo_system_user'] = 'sogo';
//* SOGo system group name
\$conf['sogo_system_group'] = 'sogo';
/*
 SOGo sudo command to use when executing a SOGo binary
 eg. 
 su -p -c '{command}' sogo
 sudo -u sogo {command}
 **** if you must quote the command ONLY USE ' (Single quote) NOT " (Double quote)
*/
\$conf['sogo_su_command'] = 'sudo -u ' . \$conf['sogo_system_user'] . ' {command}';
//* full path to sogo-tool binary 
\$conf['sogo_tool_binary'] = '{SOGOTOOLBIN}';
//* name of the database used for SOGo
\$conf['sogo_database_name'] = '{SOGODB}';
//* name of the database user used for SOGo db
\$conf['sogo_database_user'] = '{SOGODBUSER}';
//* name of the database user password used for SOGo db
\$conf['sogo_database_passwd'] = '{SOGODBPW}';
//* database host where SOGo db is hosted
\$conf['sogo_database_host'] = '127.0.0.1';
//* database port number
\$conf['sogo_database_port'] = '3306';
//* vars added to the domain template
\$conf['sogo_domain_extra_vars'] = array(
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
);
//* sogo default configuration file(s)
\$conf['sogo_gnu_step_defaults'] = '{SOGOHOMEDIR}/GNUstep/Defaults/.GNUstepDefaults';
\$conf['sogo_gnu_step_defaults_sogod.plist'] = '{SOGOHOMEDIR}/GNUstep/Defaults/sogod.plist';
\$conf['sogo_system_default_conf'] = '/etc/sogo/sogo.conf';

//* template to use for table names in sogo db
\$conf['sogo_domain_table_tpl'] = "{domain}_users";

//* define if we use the old way for domain tables.
\$conf['sogo_tb_compatibility'] = false;
EOF;

}
