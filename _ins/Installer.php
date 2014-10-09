<?php

/*
 * Copyright (C) 2014  Christian M. Jensen
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
 *  @author Christian M. Jensen <christian@cmjscripter.net>
 *  @copyright 2014 Christian M. Jensen
 *  @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
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
    static public $have_sins_of_sogo = FALSE;

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


        self::$isError = FALSE;
        //* get init scriupt for sogo
        self::$sogo_init_script = Installer::getSOGoInitScript();
        //* locate sogo-tool binary
        self::$sogo_tool_binary = Installer::getSOGoToolBinary();
        //* get sogo home dir
        self::$sogo_home_dir = Installer::getSOGoHomeDir();
        //* SOGo init script ?
        if (empty(self::$sogo_init_script)) {
            self::$error = "[FAIL]: Unable to locate SOGo init script";
            self::$errors['step1'][] = self::$error;
            self::$isError = TRUE;
            self::$have_sins_of_sogo = FALSE;
        } else if (file_exists(self::$sogo_init_script)) {
            self::$have_sins_of_sogo = TRUE;
        }
        //* sogo-tool binary?
        if (empty(self::$sogo_tool_binary)) {
            self::$error = "[FAIL]: Unable to locate sogo-tool";
            self::$errors['step1'][] = self::$error;
            self::$have_sins_of_sogo &= FALSE;
        } else if (file_exists(self::$sogo_tool_binary)) {
            self::$have_sins_of_sogo &= TRUE;
        }
        //* SOGo home dir ?
        if (empty(self::$sogo_home_dir)) {
            self::$error = "[FAIL]: Unable to locate sogo home dir";
            self::$errors['step1'][] = self::$error;
            self::$isError = TRUE;
            self::$have_sins_of_sogo &= FALSE;
        } else if (file_exists(self::$sogo_home_dir) && is_dir(self::$sogo_home_dir)) {
            self::$have_sins_of_sogo &= TRUE;
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
            }
        } else {
            echo "Not installing server files, server folder not found" . PHP_EOL;
        }
    }
    
    //* quick lazy alias
    public static function copyFiles($index) {
        if (isset(self::$files_copy[$index])) {
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
            $mysql_password = self::readInput("");
            echo PHP_EOL;
            echo "ISPConfig database? [dbispconfig]: ";
            $mysql_database = self::readInput("dbispconfig");
            echo PHP_EOL;
            $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p{$mysql_password} {$mysql_database} < " . self::$mysql_tables_ispc;
            echo exec($command) . PHP_EOL;
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
        else if (file_exists("/etc/init.d/sogo"))
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

}
