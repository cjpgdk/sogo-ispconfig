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
 */
class Installer {

    //* folder containing os specific install instructions
    const os_installers_folder = "operating_systems";
    //* file containing Mysql tables
    const mysql_tables = "tables.sql";

    /**
     * list of supported operating systems<br>
     * @var array key=OS Name, Value=
     */
    public $os_supported = array();

    /**
     * name of the user selected os to install
     * @var string 
     */
    public $os = null;

    /**
     * name of the user selected os release to install
     * @var string 
     */
    public $os_release = null;
    private $osObject = null;
    private $_folder = "";
    public static $copy_files = "";

    public function __construct() {
        $this->_folder = __DIR__ . '/';
        $this->os_supported = array();

        //* get supported operating systems
        if (is_dir($this->_folder . Installer::os_installers_folder)) {
            $oss = scandir($this->_folder . Installer::os_installers_folder);
            foreach ($oss as $value) {
                if ($value == '.' || $value == '..')
                    continue;
                $tmp = str_replace('.php', '', $value);
                $this->os_supported[strtolower($tmp)] = $this->_folder . Installer::os_installers_folder . '/' . $value;
            }
        } else
            Installer::exitError("Unable to locate folder: " . Installer::os_installers_folder . PHP_EOL . "Plase run the installer from: " . realpath(__DIR__ . '/../'));

        require 'copy_files.php';
        if (isset($files_copy) && is_array($files_copy) && isset($files_copy['interface']) && isset($files_copy['server'])) {
            self::$copy_files = $files_copy;
        }

        //* get user selected os
        if ($this->_get_user_select_os()) {
            require_once $this->os_supported[$this->os];
            $this->osObject = new $osInstallerName();

            echo str_repeat('=', 50) . PHP_EOL;
            echo "= Installing SOGO-ISPConfig : " . $this->osObject->getOSName() . PHP_EOL;
            echo str_repeat('=', 50) . PHP_EOL . PHP_EOL;

            //* get user selected os release
            if ($this->_get_user_select_osrelease()) {
                //* init requirements
                if ($this->osObject->initVars($this->os_release)) {
                    //* install
                    $this->osObject->installAddon($this->os_release);
                    //* install vhost?
                    $this->osObject->installVhost($this->os, $this->os_release);
                    //* end of install message
                    $this->osObject->endOfInstall();
                } else
                    Installer::exitError("Installer exit?, doing Initialization");
            } else
                Installer::exitError("Installer exit?, doing OS Release Select");
        } else
            Installer::exitError("Installer exit?, doing OS Select");
    }

    public function printReadInput($msg, $default_value, $to_lower = false) {
        echo $msg;
        $read_val = Installer::readInput($default_value);
        if ($to_lower)
            return strtolower($read_val);
        return $read_val;
    }

    public static function installMySQLTables($base_dir) {
        $_error = false;
        $errors = array();
        if (file_exists($base_dir . "/" . Installer::mysql_tables)) {
            echo PHP_EOL . "Installing MySQL tables" . PHP_EOL;
            //* add mysql tables
            echo PHP_EOL . "MySQL Host? [127.0.0.1]: ";
            $mysql_host = self::readInput("127.0.0.1");
            echo PHP_EOL . "MySQL admin user? [root]: ";
            $mysql_admin = self::readInput("root");
            echo PHP_EOL . "MySQL password? []: ";
            $mysql_password = str_replace('"', '\"', self::readInput(""));
            echo PHP_EOL . "ISPConfig database? [dbispconfig]: ";
            $mysql_database = self::readInput("dbispconfig");
            echo PHP_EOL;
            $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\" {$mysql_database} < " . $base_dir . "/" . Installer::mysql_tables;
            echo exec($command) . PHP_EOL;
        } else {
            $errors[] = "[FAIL]: Unable to locate mysql tables file (interface, plugin and module WILL NOT WORK without them)" .
                    PHP_EOL . "File: " . $base_dir . "/" . Installer::mysql_tables .
                    PHP_EOL . "Redownload the tables and import them manualy before using";
            $_error = TRUE;
        }
        if ($_error) {
            echo "One or more errors were found importing mysql tables" . PHP_EOL;
            echo str_repeat('=', 50) . PHP_EOL;
            echo implode(PHP_EOL, $errors) . PHP_EOL;
            echo str_repeat('=', 50) . PHP_EOL;
            echo "Continue (Y/N) [N]: ";
            if (strtolower(self::readInput("n")) == "n") {
                echo PHP_EOL;
                die("okay goodbye" . PHP_EOL);
            }
        }
    }

    /**
     * get sogo home dir location
     * @return string
     */
    public static function getSOGoHomeDir() {
        $sogo_home_dir = exec("getent passwd sogo | cut -d: -f6");
        echo "location of SOGo home dir [{$sogo_home_dir}]: ";
        $sogo_home_dir = Installer::readInput($sogo_home_dir);
        return $sogo_home_dir;
    }

    /**
     * get sogo-tool location
     * @return string
     */
    public static function getSOGoToolBinary() {
        $sogo_tool_binary = exec("which sogo-tool");
        echo "location of sogo-tool [{$sogo_tool_binary}]: ";
        $sogo_tool_binary = Installer::readInput($sogo_tool_binary);
        return $sogo_tool_binary;
    }

    /**
     * get init script location for SOGo
     * @return string
     */
    public static function getSOGoInitScript() {
        $sogo_init_script = "";
        if (file_exists("/etc/init.d/sogo"))
            $sogo_init_script = "/etc/init.d/sogo";
        else if (file_exists("/etc/init.d/sogod"))
            $sogo_init_script = "/etc/init.d/sogod";
        echo "location of SOGo init script [{$sogo_init_script}]: ";
        $sogo_init_script = Installer::readInput($sogo_init_script);
        return $sogo_init_script;
    }

    /**
     * copy files from $index to $base_dir
     * @param string $index
     * @param string $base_dir
     */
    public static function copyFiles($index, $base_dir) {
        $_error = false;
        $errors = array();
        if (isset(self::$copy_files[$index])) {
            if ($index == "interface" && !is_dir($base_dir . '/' . $index . '/web/mail/lib/menu.d/'))
                @mkdir($base_dir . '/' . $index . '/web/mail/lib/menu.d/');
            if ($index == "interface" && !is_dir($base_dir . '/' . $index . '/web/admin/lib/menu.d/'))
                @mkdir($base_dir . '/' . $index . '/web/admin/lib/menu.d/');

            foreach (self::$copy_files[$index] as $file) {
                if (file_exists($index . '/' . $file)) {
                    if (!copy($index . '/' . $file, $base_dir . '/' . $index . '/' . $file)) {
                        $errors[] = "Faild to copy file [{$index}/{$file}] to " . $base_dir . "/{$index}/{$file}";
                        $_error = TRUE;
                    }
                } else {
                    $errors[] = "File [{$index}/{$file}] not found in plugin folder";
                    $_error = TRUE;
                }
            }
        } else {
            echo "Files index [{$index}] doesn't exists" . PHP_EOL;
        }
        if ($_error) {
            echo "One or more errors were found during file copy" . PHP_EOL;
            echo str_repeat('=', 50) . PHP_EOL;
            echo implode(PHP_EOL, $errors) . PHP_EOL;
            echo str_repeat('=', 50) . PHP_EOL;
            echo "Continue (Y/N) [N]: ";
            if (strtolower(Installer::readInput("n")) == "n") {
                echo PHP_EOL;
                die("okay goodbye" . PHP_EOL);
            }
        }
    }

    /**
     * install files for ISPConfig server
     * @param string $base_dir
     */
    public static function installServer($base_dir) {
        if (is_dir($base_dir . '/') && is_dir($base_dir . '/server/')) {
            echo PHP_EOL . "Installing server files" . PHP_EOL;
            Installer::copyFiles('server', $base_dir);
        } else {
            echo "Not installing server files, server folder not found" . PHP_EOL;
        }
    }

    /**
     * install files for ISPConfig interface
     * @param string $base_dir
     */
    public static function installInterface($base_dir) {
        if (is_dir($base_dir . '/') && is_dir($base_dir . '/interface/')) {
            echo PHP_EOL . "Installing interface files" . PHP_EOL;
            Installer::copyFiles('interface', $base_dir);
        } else {
            echo PHP_EOL . "Not installing interface files, interface folder not found" . PHP_EOL;
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
     * print a message and exit the installer.
     * @param string $msg
     */
    public static function exitError($msg) {
        die(PHP_EOL . $msg . PHP_EOL);
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

    private function _get_user_select_osrelease() {
        $retval = false;
        $_echo_os = "";
        $_os_first = "";
        $_os_release = $this->osObject->getOSReleases();
        foreach ($_os_release as $key => $value) {
            $_echo_os .= $key . '|';
            if (empty($_os_first))
                $_os_first = $key;
        }
        $msg = "Select the name of your system: " . PHP_EOL . trim($_echo_os, '|') . " [{$_os_first}]: ";
        $this->os_release = $this->printReadInput($msg, $_os_first, true);
        unset($_echo_os, $_os_first);
        if ($this->os_release != null && isset($_os_release[$this->os_release])) {
            $retval = true;
        } else {
            echo PHP_EOL . "The selected release is not valid for this script.!" . PHP_EOL . PHP_EOL;
            $retval = $this->_get_user_select_osrelease();
        }
        return $retval;
    }

    private function _get_user_select_os() {
        $_echo_os = "";
        $_os_first = "";
        $retval = false;
        foreach ($this->os_supported as $key => $value) {
            $_echo_os .= $key . '|';
            if (empty($_os_first))
                $_os_first = $key;
        }
        $msg = "if your operating system is not listed here" . PHP_EOL;
        $msg .= "you can still install this addon by selecting" . PHP_EOL;
        $msg .= "noinstall, this will install all the files related" . PHP_EOL;
        $msg .= "to this addon but you will have to install SOGo manually" . PHP_EOL . PHP_EOL;
        $msg .= "Select the name of your system: " . PHP_EOL . trim($_echo_os, '|') . " [{$_os_first}]: ";
        $this->os = $this->printReadInput($msg, $_os_first, true);
        unset($_echo_os, $_os_first);

        if ($this->os != null && isset($this->os_supported[$this->os])) {
            if (file_exists($this->os_supported[$this->os]))
                $retval = true;
            else {
                Installer::exitError("Unable to locate the installer for the selected os: {$this->os}" . PHP_EOL
                        . $this->os_supported[$this->os]);
            }
        } else {
            echo PHP_EOL . "The selected operating system is not valid for this script.!" . PHP_EOL . PHP_EOL;
            $retval = $this->_get_user_select_os();
        }
        return $retval;
    }

}
