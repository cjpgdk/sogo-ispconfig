<?php

/*
  cd /tmp
  wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
  tar -xvf sogo-ispconfig.tar.gz
  cd sogo-ispconfig-master
  php install.php
 * 
  or use arguments like this
  php install.php [-i|--install]
 * 
  php install.php -i=all
  php install.php -i=mysql
  php install.php -i=interface
  php install.php -i=server
  php install.php -i=nginxvhost
  php install.php -i=apachevhost
 */

if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    /*
      PHP 5.3 on CentOS/RHEL 5.11 via Yum | Webtatic.com
      https://webtatic.com/packages/php53/
     * 
     */
    die('I require PHP >= 5.3 and you are running ' . PHP_VERSION);
}

require '_ins/Installer.php';

echo <<< EOF
 ____   ___   ____            ___ ____  ____   ____             __ _
/ ___| / _ \ / ___| ___      |_ _/ ___||  _ \ / ___|___  _ __  / _(_) __ _
\___ \| | | | |  _ / _ \ _____| |\___ \| |_) | |   / _ \| '_ \| |_| |/ _` |
 ___) | |_| | |_| | (_) |_____| | ___) |  __/| |__| (_) | | | |  _| | (_| |
|____/ \___/ \____|\___/     |___|____/|_|    \____\___/|_| |_|_| |_|\__, |
                                                                     |___/

Installer.
you can exit the installer at any time just hit: [CTRL + C]

Select the installation type:
all ..........: run the full install process.
MySQL ........: install only mysql tables. 
Interface ....: install only interface files
Server .......: install only server files
NginxVhost ...: install only Nginx vhost
ApacheVhost ..: install only Apache vhost
enplugin .....: enable server plugin
enmodule .....: enable server module

Install: 
EOF;
$_install_types = array('all', 'mysql', 'interface', 'server', 'nginxvhost', 'apachevhost', 'enplugin', 'enmodule');
$ARGS = array();
if ($argc > 0) {
    foreach ($argv as $arg) {
        if (preg_match('#--([^=]+)=(.*)#', $arg, $reg) || preg_match('#-([^=]+)=(.*)#', $arg, $reg)) {
            $ARGS[$reg[1]] = $reg[2];
        }
    }
}
if (isset($ARGS['install']) && in_array($ARGS['install'], $_install_types)) {
    echo $ARGS['install'];
    $_install = $ARGS['install'];
} else if (isset($ARGS['i']) && in_array($ARGS['i'], $_install_types)) {
    echo $ARGS['i'];
    $_install = $ARGS['i'];
} else
    $_install = Installer::readInput('all');

echo PHP_EOL;
switch (strtolower($_install)) {
    case 'all':
        new Installer();
        break;
    case 'mysql':
        Installer::installMySQLTables(realpath(__DIR__ . "/_ins/"));
        break;
    case 'interface':
        echo "location of ISPConfig folder? [/usr/local/ispconfig]: ";
        $ispcdir = Installer::readInput("/usr/local/ispconfig");
        require '_ins/copy_files.php';
        Installer::$copy_files = $files_copy;
        Installer::installInterface($ispcdir);
        break;
    case 'server':
        echo "location of ISPConfig folder? [/usr/local/ispconfig]: ";
        $ispcdir = Installer::readInput("/usr/local/ispconfig");
        require '_ins/copy_files.php';
        Installer::$copy_files = $files_copy;
        Installer::installServer($ispcdir);
        break;
    case 'nginxvhost':
        require '_ins/NginxVhost.php';
        NginxVhost::Run();
        break;
    case 'apachevhost':
        require '_ins/ApacheVhost.php';
        ApacheVhost::Run();
        break;
    case 'enplugin':
        echo "location of ISPConfig folder? [/usr/local/ispconfig]: ";
        $ispcdir = Installer::readInput("/usr/local/ispconfig");
        if (!is_link($ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php') && !file_exists($ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
            if (!@link($ispcdir . '/server/plugins-available/sogo_plugin.inc.php', $ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
                echo "\033[1;33m" . 'Unable to enable plugin: sogo_plugin' . "\033[0m" . PHP_EOL;
            } else {
                echo "\033[0;32m" . 'Enabled plugin: sogo_plugin' . "\033[0m" . PHP_EOL;
            }
        } else {
            echo "\033[1;33m" . 'Plugin already enabled: sogo_plugin' . "\033[0m" . PHP_EOL;
        }
        break;
    case 'enmodule':
        echo "location of ISPConfig folder? [/usr/local/ispconfig]: ";
        $ispcdir = Installer::readInput("/usr/local/ispconfig");
        
        if (!is_link($ispcdir . '/server/mods-enabled/sogo_module.inc.php') && !file_exists($ispcdir . '/server/mods-enabled/sogo_module.inc.php')) {
            if (!@link($ispcdir . '/server/mods-available/sogo_module.inc.php', $ispcdir . '/server/mods-enabled/sogo_module.inc.php')) {
                echo "\033[1;33m" . 'Unable to enable module: sogo_module' . "\033[0m" . PHP_EOL;
            } else {
                echo "\033[0;32m" . 'Enabled module: sogo_module' . "\033[0m" . PHP_EOL;
            }
        } else {
            echo "\033[1;33m" . 'Module already enabled: sogo_module' . "\033[0m" . PHP_EOL;
        }
        break;
    default:
        echo PHP_EOL . "Invalid selection" . PHP_EOL;
        break;
}

