<?php

require 'noinstall.php';
$osInstallerName = "DebianInstaller";

/**
 * Copyright (C) 2015 Christian M. Jensen
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
 */
class DebianInstaller extends noinstallInstaller {

    public $os_name = "debian";
    public $os_releases = array(
        'lenny' => 'lenny',
        'squeeze' => 'squeeze',
        'wheezy' => 'wheezy',
        'jessie' => 'jessie',
    );
    public $install_sogo = true;
    // sogo conf vars
    public $sogo_init_script = null;
    public $sogo_tool_binary = null;
    public $sogo_home_dir = null;
    public $sogo_system_user = null;
    public $sogo_system_group = null;
    // source list
    public $source_list = "/etc/apt/sources.list";
    public $source_list_dir = "/etc/apt/sources.list.d/";

    public function endOfInstall() {
        echo PHP_EOL . "All done assuming no errors and all went well" . PHP_EOL;
        echo "you will need to add SOGo config values to interface config file:" . PHP_EOL;
        echo $this->ispconfig_home_dir . "/interface/lib/config.inc.local.php" . PHP_EOL;
        echo "A sample file can be found here.!" . PHP_EOL;
        echo $this->ispconfig_home_dir . "/interface/lib/config.inc.local.sogo-sample.php" . PHP_EOL . PHP_EOL;
        echo "and you also need to add SOGo config values to server config file:" . PHP_EOL;
        echo $this->ispconfig_home_dir . "/server/lib/config.inc.local.php" . PHP_EOL;
        echo "A sample file can be found here.!" . PHP_EOL;
        echo $this->ispconfig_home_dir . "/server/lib/config.inc.local.sogo-sample.php" . PHP_EOL . PHP_EOL;
    }

    public function initOSVars($os_release) {
        parent::initOSVars($os_release);

        //* check if SOGo is installed
        if (Installer::isSOGoOnServer()) {
            echo PHP_EOL . "I Found SOGo on your server is this true? (Y/N) [Y]: ";
            if (strtolower(Installer::readInput("y")) == "y")
                $this->install_sogo = false;
        }else {
            echo PHP_EOL . "I'm not able to locate SOGo, shall i install it? (Y/N) [Y]: ";
            if (strtolower(Installer::readInput("y")) == "y")
                $this->install_sogo = true;
            else
                $this->install_sogo = false;
        }

        //* install SOGo
        if ($this->install_sogo) {
            if (!file_exists($this->source_list)) {
                echo PHP_EOL . $this->source_list . ' is not found on you system';
            } else {
                //* is inverse mirror installed
                $sourcelist = file($this->source_list);
                $inverse_mirror = FALSE;
                foreach ($sourcelist as $value) {
                    if (preg_match("/inverse\.ca/i", $value)) {
                        $this->echoMessage(PHP_EOL . "Not adding inverse mirror to your source list.!" . PHP_EOL);
                        $inverse_mirror = TRUE;
                        break;
                    }
                }
                unset($sourcelist);

                //* install inverse mirror
                if (!$inverse_mirror) {
                    $mirror = $this->debMirrors($os_release);
                    echo PHP_EOL . "Saving inverse mirrors for SOGO in";
                    echo PHP_EOL . " - " . $this->source_list_dir . 'inverse.mirror.list';
                    file_put_contents($this->source_list_dir . 'inverse.mirror.list', $mirror);
                    unset($mirror);
                    echo PHP_EOL . "Adding apt key '0x810273C4' from keyserver keys.gnupg.net";
                    echo exec('sudo apt-key adv --keyserver keys.gnupg.net --recv-key 0x810273C4');
                    echo PHP_EOL . "Updating apt package list";
                    echo exec('sudo apt-get update');
                }
                unset($inverse_mirror);

                //* install SOGo
                echo PHP_EOL . "About to install SOGo";
                $pkg = "sogo sope4.9-gdl1-mysql memcached rpl";
                /*
                  Debian: lenny
                  Ubuntu: maverick, natty, oneiric
                  do not have activesync
                 */
                if ($os_release != "lenny" && $os_release != "maverick" && 
                        $os_release != "natty" && $os_release != "oneiric") {
                    echo PHP_EOL . "Shall i install sogo-activesync? (Y/N) [Y]: ";
                    $pkg .= (strtolower(Installer::readInput("y")) == "y" ? " sogo-activesync" : "");
                }
                echo PHP_EOL . "Starting the installation of SOGo, this can take a long time please be patient";
                echo exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $pkg);
                unset($pkg);

                //* force some settings
                if (file_exists('/etc/tmpreaper.conf')) {
                    echo PHP_EOL . "Removeing warnings from tmpreaper";
                    $tmp = file_get_contents('/etc/tmpreaper.conf');
                    $tmp = str_replace('SHOWWARNING=true', 'SHOWWARNING=false', $tmp);
                    file_put_contents('/etc/tmpreaper.conf', $tmp);
                    unset($tmp);
                }
                if (file_exists('/etc/memcached.conf')) {
                    echo PHP_EOL . "memcached not happy w. IPv6, setting to 127.0.0.1";
                    $tmp = file_get_contents('/etc/memcached.conf');
                    $tmp = str_replace('localhost', '127.0.0.1', $tmp);
                    file_put_contents('/etc/memcached.conf', $tmp);
                    unset($tmp);
                } else {
                    echo PHP_EOL . "memcached config file, not found please make sure it's installed";
                }
                echo PHP_EOL . "ALL DONE.. SOGo 'should' now installed on your server..!" . PHP_EOL . PHP_EOL;

                $this->_set_SOGo_configuration();
            }
        } else {
            //* not installing SOGo get enviroment settings
            echo PHP_EOL . "is SOGo running on this server? (Y/N) [Y]: ";
            if (strtolower(Installer::readInput("y")) == "y") {
                $this->install_sogo = true;
                $this->_set_SOGo_configuration();
            }
        }
        return true;
    }

    public function installAddon($os_release) {
        parent::installAddon($os_release);
        //* create SOGo configuration
        if ($this->server_installed && $this->install_sogo) {

            echo PHP_EOL . "Add SOGo database? (Y/N) [Y]:";
            if (strtolower(Installer::readInput("y")) == "y") {

                echo PHP_EOL . "MySQL Host? [127.0.0.1]: ";
                $mysql_host = Installer::readInput("127.0.0.1");
                echo PHP_EOL . "MySQL admin user? [root]: ";
                $mysql_admin = Installer::readInput("root");
                echo PHP_EOL . "MySQL password? []: ";
                $mysql_password = str_replace('"', '\"', Installer::readInput(""));

                echo PHP_EOL . "SOGo database? [dbsogo]: ";
                $sogo_database = Installer::readInput("dbsogo");
                echo PHP_EOL . "SOGo database user? [sogo]: ";
                $sogo_user = Installer::readInput("sogo");
                $passwd = sha1(md5(time() . microtime(true)));
                echo PHP_EOL . "SOGo database user password? [{$passwd}]: ";
                $sogo_passwd = Installer::readInput($passwd);

                $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\"  -e \"CREATE USER '{$sogo_user}'@'localhost' IDENTIFIED BY '{$sogo_passwd}';\"";
                echo exec($command) . PHP_EOL;

                $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\"  -e \"GRANT USAGE ON *.* TO '{$sogo_user}'@'localhost' IDENTIFIED BY '{$sogo_passwd}' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;\"";
                echo exec($command) . PHP_EOL;

                $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\"  -e \"CREATE DATABASE IF NOT EXISTS {$sogo_database} ;\"";
                echo exec($command) . PHP_EOL;

                $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\"  -e \"GRANT ALL PRIVILEGES ON {$sogo_database}. * TO '{$sogo_user}'@'localhost';\"";
                echo exec($command) . PHP_EOL;

                $this->server_config_local = str_replace('{SOGODB}', $sogo_database, $this->server_config_local);
                $this->server_config_local = str_replace('{SOGODBUSER}', $sogo_user, $this->server_config_local);
                $this->server_config_local = str_replace('{SOGODBPW}', $sogo_passwd, $this->server_config_local);
            }

            echo PHP_EOL . "Enable SOGo Module and Plugin?" . PHP_EOL;
            echo " - Only do this if SOGo is install on this server? (Y/N) [Y]: ";
            if (strtolower(Installer::readInput("y")) == 'y') {
                $this->enableSOGoModuleAndPlugin();
                //* create sogo local config in server
                @file_put_contents($this->ispconfig_home_dir . '/server/lib/config.inc.local.sogo-sample.php', $this->server_config_local);
            }
        } else {
            if (!$this->server_installed && $this->install_sogo) {
                echo PHP_EOL . "No server files has been installed and you"
                . PHP_EOL . "selected to run SOGo from this server"
                . PHP_EOL . "that is simply not possible"
                . PHP_EOL . "run the installer again to setup configuration for sogo." . PHP_EOL;
            }
        }
        return true;
    }

    protected function debMirrors($os_release) {
        switch ($os_release) {
            case 'lenny':
                $mirror = "deb http://inverse.ca/debian/ lenny lenny" . PHP_EOL;
                $mirror .= "#deb-src http://inverse.ca/debian/ lenny lenny";
                return $mirror;
            case 'squeeze':
            case 'wheezy':
            case 'jessie':
            default:
                $mirror = "deb http://inverse.ca/debian/ {$os_release} {$os_release}" . PHP_EOL;
                $mirror .= "#deb-src http://inverse.ca/debian/ {$os_release} {$os_release}" . PHP_EOL . PHP_EOL;
                $mirror .= "#deb http://inverse.ca/debian-nightly/ {$os_release} {$os_release}" . PHP_EOL;
                $mirror .= "#deb-src http://inverse.ca/debian-nightly/ {$os_release} {$os_release}";
                return $mirror;
        }
    }

    private function _set_SOGo_configuration() {

        //* get init script for sogo
        $this->sogo_init_script = Installer::getSOGoInitScript();
        //* locate sogo-tool binary
        $this->sogo_tool_binary = Installer::getSOGoToolBinary();
        //* get sogo home dir
        $this->sogo_home_dir = Installer::getSOGoHomeDir();

        // @todo Add ask for sogo system user and group name
        $this->sogo_system_user = 'sogo';
        $this->sogo_system_group = 'sogo';

        $this->server_config_local = str_replace('{SOGOTOOLBIN}', $this->sogo_tool_binary, $this->server_config_local);
        $this->server_config_local = str_replace('{SOGOHOMEDIR}', $this->sogo_home_dir, $this->server_config_local);
        $this->server_config_local = str_replace('{SOGOINITSCRIPT}', $this->sogo_init_script, $this->server_config_local);
        $this->server_config_local = str_replace('{SOGOSYSTEMUSER}', $this->sogo_system_user, $this->server_config_local);
        $this->server_config_local = str_replace('{SOGOSYSTEMGROUP}', $this->sogo_system_group, $this->server_config_local);
    }

}
