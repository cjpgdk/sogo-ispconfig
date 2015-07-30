<?php

/*
  cd /tmp
  wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
  tar -xvf sogo-ispconfig.tar.gz
  cd sogo-ispconfig-master
  php uninstall.php
 * 
  or use arguments like this
  php uninstall.php [-ui|--uninstall]
 * 
  php uninstall.php -ui=all
  php uninstall.php -ui=mysql
  php uninstall.php -ui=interface
  php uninstall.php -ui=server
  php uninstall.php -ui=files
  php uninstall.php -ui=dismodule
  php uninstall.php -ui=displugin
 */

if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('I require PHP >= 5.3 and you are running ' . PHP_VERSION);
}

require_once __DIR__ . '/_ins/Installer.php';
require_once __DIR__ . '/_ins/copy_files.php';
require_once __DIR__ . '/_ins/old_files.php';

echo <<< EOF
 ____   ___   ____            ___ ____  ____   ____             __ _
/ ___| / _ \ / ___| ___      |_ _/ ___||  _ \ / ___|___  _ __  / _(_) __ _
\___ \| | | | |  _ / _ \ _____| |\___ \| |_) | |   / _ \| '_ \| |_| |/ _` |
 ___) | |_| | |_| | (_) |_____| | ___) |  __/| |__| (_) | | | |  _| | (_| |
|____/ \___/ \____|\___/     |___|____/|_|    \____\___/|_| |_|_| |_|\__, |
                                                                     |___/

Uninstaller.
you can exit the uninstaller at any time just hit: [CTRL + C]

This uninstaller won't remove sogo use your distribution's 
package manager to do that.

Select the installation type:
all ..........: run the full uninstall process.
MySQL ........: uninstall only mysql tables. 
Interface ....: uninstall only interface files
Server .......: uninstall only server files
files ........: uninstall server + interface files
dismodule ....: disable server module
displugin ....: disable server plugin

Uninstall: 
EOF;

$_uninstall_types = array('all', 'mysql', 'interface', 'server', 'files', 'dismodule', 'displugin');
$ARGS = array();
if ($argc > 0) {
    foreach ($argv as $arg) {
        if (preg_match('#--([^=]+)=(.*)#', $arg, $reg) || preg_match('#-([^=]+)=(.*)#', $arg, $reg)) {
            $ARGS[$reg[1]] = $reg[2];
        }
    }
}
if (isset($ARGS['uninstall']) && in_array($ARGS['uninstall'], $_uninstall_types)) {
    echo $ARGS['uninstall'];
    $_uninstall = $ARGS['uninstall'];
} else if (isset($ARGS['ui']) && in_array($ARGS['ui'], $_uninstall_types)) {
    echo $ARGS['ui'];
    $_uninstall = $ARGS['ui'];
} else
    $_uninstall = Installer::readInput('all');

echo PHP_EOL;

echo "location of ISPConfig folder? [/usr/local/ispconfig]: ";
$ispcdir = Installer::readInput("/usr/local/ispconfig");

echo PHP_EOL;

switch (strtolower($_uninstall)) {
    case 'all':
        define('UNINSTALL_ALL', true);
        goto mysql;
        break;
    case 'mysql':
        mysql:

        echo "MySQL Host? [127.0.0.1]: ";
        $mysql_host = Installer::readInput("127.0.0.1");
        echo PHP_EOL;
        echo "MySQL admin user? [root]: ";
        $mysql_admin = Installer::readInput("root");
        echo PHP_EOL;
        echo "MySQL password? []: ";
        $mysql_password = str_replace('"', '\"', Installer::readInput(""));
        echo PHP_EOL;
        echo "ISPConfig database? [dbispconfig]: ";
        $mysql_database = Installer::readInput("dbispconfig");
        echo PHP_EOL;
        $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\" {$mysql_database} < " . __DIR__ . "/_ins/drop_tables.sql";
        echo exec($command) . PHP_EOL;

        if (defined('UNINSTALL_ALL'))
            goto files;
        break;
    case 'interface':
        interfacefiles:
        //* old files just remove...
        foreach ($old_files as $key => $value) {
            if ($value['type'] == 'file') {
                if (file_exists($ispcdir . '/' . $value['file'])) {
                    if (!@unlink($ispcdir . '/' . $value['file'])) {
                        echo "\033[1;33m" . 'Not Deleted:' . $ispcdir . '/' . $value['file'] . "\033[0m" . PHP_EOL;
                    } else {
                        echo "\033[0;32m" . 'Delete:' . $ispcdir . '/' . $value['file'] . "\033[0m" . PHP_EOL;
                    }
                }
            } else if ($value['type'] == 'folder') {
                if (file_exists($ispcdir . '/' . $value['folder']) || is_dir($ispcdir . '/' . $value['folder']))
                    echo "\033[1;33m" . 'Not Deleted:' . $ispcdir . '/' . $value['folder'] . "\033[0m" . PHP_EOL;
            }
        }
        //* remove interface files
        foreach ($files_copy['interface'] as $key => $value) {
            if (file_exists($ispcdir . "/interface/" . $value)) {
                if (!@unlink($ispcdir . "/interface/" . $value)) {
                    echo "\033[1;33m" . 'Not Deleted:' . $ispcdir . "/interface/" . $value . "\033[0m" . PHP_EOL;
                } else {
                    echo "\033[0;32m" . 'Delete:' . $ispcdir . "/interface/" . $value . "\033[0m" . PHP_EOL;
                }
            }
        }
        if (defined('UNINSTALL_FILES'))
            goto dismodule;
        break;
    case 'server':
        server:
        if (!defined('UNINSTALL_FILES')) {
            //* disable module and plugin
            if (is_link($ispcdir . '/server/mods-enabled/sogo_module.inc.php') || file_exists($ispcdir . '/server/mods-enabled/sogo_module.inc.php')) {
                @unlink($ispcdir . '/server/mods-enabled/sogo_module.inc.php');
            }
            if (is_link($ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php') || file_exists($ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
                @unlink($ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php');
            }
        }
        //* old files just remove...
        foreach ($old_files as $key => $value) {
            if ($value['type'] == 'file') {
                if (file_exists($ispcdir . '/' . $value['file'])) {
                    if (!@unlink($ispcdir . '/' . $value['file'])) {
                        echo "\033[1;33m" . 'Not Deleted:' . $ispcdir . '/' . $value['file'] . "\033[0m" . PHP_EOL;
                    } else {
                        echo "\033[0;32m" . 'Delete:' . $ispcdir . '/' . $value['file'] . "\033[0m" . PHP_EOL;
                    }
                }
            } else if ($value['type'] == 'folder') {
                if (file_exists($ispcdir . '/' . $value['folder']) || is_dir($ispcdir . '/' . $value['folder']))
                    echo "\033[1;33m" . 'Not Deleted:' . $ispcdir . '/' . $value['folder'] . "\033[0m" . PHP_EOL;
            }
        }
        //* remove server files
        foreach ($files_copy['server'] as $key => $value) {
            if (file_exists($ispcdir . "/server/" . $value)) {
                if (!@unlink($ispcdir . "/server/" . $value)) {
                    echo "\033[1;33m" . 'Not Deleted:' . $ispcdir . "/server/" . $value . "\033[0m" . PHP_EOL;
                } else {
                    echo "\033[0;32m" . 'Delete:' . $ispcdir . "/server/" . $value . "\033[0m" . PHP_EOL;
                }
            }
        }
        break;
    case 'dismodule':
        dismodule:
        if (is_link($ispcdir . '/server/mods-enabled/sogo_module.inc.php') || file_exists($ispcdir . '/server/mods-enabled/sogo_module.inc.php')) {
            if (!@unlink($ispcdir . '/server/mods-enabled/sogo_module.inc.php')) {
                echo "\033[1;33m" . 'Unable to disable module: sogo_module' . "\033[0m" . PHP_EOL;
            } else {
                echo "\033[0;32m" . 'Disabled module: sogo_module' . "\033[0m" . PHP_EOL;
            }
        } else {
            echo "\033[1;33m" . 'Module not enabled: sogo_module' . "\033[0m" . PHP_EOL;
        }
        if (defined('UNINSTALL_FILES'))
            goto displugin;
        break;
    case 'displugin':
        displugin:
        if (is_link($ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php') || file_exists($ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
            if (!@unlink($ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
                echo "\033[1;33m" . 'Unable to disable plugin: sogo_plugin' . "\033[0m" . PHP_EOL;
            } else {
                echo "\033[0;32m" . 'Disabled plugin: sogo_plugin' . "\033[0m" . PHP_EOL;
            }
        } else {
            echo "\033[1;33m" . 'Plugin not enabled: sogo_plugin' . "\033[0m" . PHP_EOL;
        }
        if (defined('UNINSTALL_FILES'))
            goto server;
        break;
    case 'files':
        files:
        define('UNINSTALL_FILES', true);
        goto interfacefiles;
        break;
    default:
        echo PHP_EOL . "Invalid selection" . PHP_EOL;
        break;
}
