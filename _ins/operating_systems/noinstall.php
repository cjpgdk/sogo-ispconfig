<?php

$osInstallerName = "noinstallInstaller";

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
 */
class noinstallInstaller {

    public $os_name = "Addon only";
    public $os_releases = array('all' => 'all');
    public $ispconfig_home_dir = "";
    public $interface_installed = false;
    public $server_installed = false;

    public function installVhost() {
        echo PHP_EOL . PHP_EOL . "Setup basic vhost (Y/N) [Y]: ";
        if (strtolower(Installer::readInput("y")) == "y") {
            echo PHP_EOL . "Webserver apache, nginx [apache]: ";
            if (strtolower(Installer::readInput("apache")) == "apache") {
                require __DIR__ . '/../ApacheVhost.php';
                ApacheVhost::Run();
            } else {
                require __DIR__ . '/../NginxVhost.php';
                NginxVhost::Run();
            }
        }
        echo PHP_EOL;
    }

    /**
     * initialize os specific variables
     */
    public function installAddon($os_release) {
        //* install interface files
        echo PHP_EOL . "Install interface files? (Y/N) [Y]: ";
        if (strtolower(Installer::readInput("y")) == "y") {
            Installer::installInterface($this->ispconfig_home_dir);
            $this->interface_installed = true;
        }

        //* install server files
        echo PHP_EOL . "Install server files? (Y/N) [Y]: ";
        if (strtolower(Installer::readInput("y")) == "y") {
            Installer::installServer($this->ispconfig_home_dir);
            $this->server_installed = true;
        }

        if ($this->server_installed || $this->interface_installed) {
            //* required...
            Installer::installMySQLTables(realpath(__DIR__ . "/.."));
        } else {
            //* install MySQL tables
            echo PHP_EOL . "Install MySQL tables? (Y/N) [Y]: ";
            if (strtolower(Installer::readInput("y")) == "y") {
                Installer::installMySQLTables(realpath(__DIR__ . "/.."));
            }
        }
        return true;
    }

    public function endOfInstall() {
        echo PHP_EOL . "Install complete.!" . PHP_EOL;
    }

    /**
     * initialize required variables
     */
    public function initVars($os_release) {
        if ($this->_getISPConfigHomeDir()) {
            return $this->initOSVars($os_release);
        } else
            Installer::exitError("Installer exit?, after select of ISPConfig home dir");
        return false;
    }

    /**
     * initialize os specific variables
     */
    public function initOSVars($os_release) {
        return true;
    }

    /**
     * locate ISP Config home dir
     * @return string
     */
    public function getISPConfigHomeDir() {
        echo PHP_EOL . "location of ISPConfig folder? [/usr/local/ispconfig]: ";
        $ispcdir = Installer::readInput("/usr/local/ispconfig");
        return $ispcdir;
    }

    /**
     * get the name og the operating system
     * @return string
     */
    public function getOSName() {
        return $this->os_name;
    }

    /**
     * get the supported releases of the operating system
     * @return array
     */
    public function getOSReleases() {
        return $this->os_releases;
    }

    /**
     * Enable module and plugin
     */
    public function enableSOGoModuleAndPlugin() {
        if (!is_link($this->ispconfig_home_dir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
            if (!symlink($this->ispconfig_home_dir . '/server/plugins-available/sogo_plugin.inc.php', $this->ispconfig_home_dir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
                echo PHP_EOL . str_repeat('=', 50) . PHP_EOL;
                echo "Unable to enable sogo_plugin" . PHP_EOL .
                "Try!" . PHP_EOL .
                "ln -s " . $this->ispconfig_home_dir . "/server/plugins-available/sogo_plugin.inc.php " . $this->ispconfig_home_dir . "server/plugins-enabled/sogo_plugin.inc.php" . PHP_EOL;
                echo str_repeat('=', 50) . PHP_EOL;
            }
        }
        if (!is_link($this->ispconfig_home_dir . '/server/mods-enabled/sogo_module.inc.php')) {
            if (!symlink($this->ispconfig_home_dir . '/server/mods-available/sogo_module.inc.php', $this->ispconfig_home_dir . '/server/mods-enabled/sogo_module.inc.php')) {
                echo PHP_EOL . str_repeat('=', 50) . PHP_EOL;
                echo "Unable to enable sogo_module" . PHP_EOL .
                "Try!" . PHP_EOL .
                "ln -s " . $this->ispconfig_home_dir . "/server/mods-available/sogo_module.inc.php " . $this->ispconfig_home_dir . "/server/mods-enabled/sogo_module.inc.php" . PHP_EOL;
                echo str_repeat('=', 50) . PHP_EOL;
            }
        }
        return true;
    }

    private function _getISPConfigHomeDir() {
        $retval = false;
        $this->ispconfig_home_dir = $this->getISPConfigHomeDir();
        if (is_dir($this->ispconfig_home_dir . '/') && (is_dir($this->ispconfig_home_dir . '/interface/') || is_dir($this->ispconfig_home_dir . '/server/'))) {
            $retval = true;
        } else {
            echo PHP_EOL . "The folder: [{$this->ispconfig_home_dir}], do not contain a valid ispconfig folder structure" . PHP_EOL . PHP_EOL;
            $retval = $this->_getISPConfigHomeDir();
        }
        return $retval;
    }

    // {SOGOTOOLBIN}
    // {SOGOHOMEDIR}
    // {SOGOINITSCRIPT}
    // {SOGOSYSTEMUSER}
    // {SOGOSYSTEMGROUP}
    public $server_config_local = <<< EOF
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
\$conf['sogo_system_user'] = '{SOGOSYSTEMUSER}';
//* SOGo system group name
\$conf['sogo_system_group'] = '{SOGOSYSTEMGROUP}';
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

//* SOGo init.d script, not in use yet
\$conf['sogo_init_script'] = '{SOGOINITSCRIPT}';
EOF;

}

