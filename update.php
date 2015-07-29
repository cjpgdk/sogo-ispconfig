<?php

/*
  cd /tmp
  wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
  tar -xvf sogo-ispconfig.tar.gz
  cd sogo-ispconfig-master
  php update.php
 */

$sogo_interface_version_latest = "9";

echo <<< EOF
 ____   ___   ____            ___ ____  ____   ____             __ _
/ ___| / _ \ / ___| ___      |_ _/ ___||  _ \ / ___|___  _ __  / _(_) __ _
\___ \| | | | |  _ / _ \ _____| |\___ \| |_) | |   / _ \| '_ \| |_| |/ _` |
 ___) | |_| | |_| | (_) |_____| | ___) |  __/| |__| (_) | | | |  _| | (_| |
|____/ \___/ \____|\___/     |___|____/|_|    \____\___/|_| |_|_| |_|\__, |
                                                                     |___/

Updater.
you can exit the updater at any time just hit: [CTRL + C]
EOF;

require '_ins/Installer.php';

echo PHP_EOL . PHP_EOL . "location of ISPConfig folder? [/usr/local/ispconfig]: ";
$ispchome = Installer::readInput("/usr/local/ispconfig");

$db = NULL;
if (file_exists($ispchome . '/server/lib/config.inc.php') &&
        file_exists($ispchome . '/server/lib/classes/db_mysql.inc.php') &&
        file_exists($ispchome . '/server/lib/mysql_clientdb.conf')) {
    require_once $ispchome . '/server/lib/config.inc.php';
    require_once $ispchome . '/server/lib/classes/db_mysql.inc.php';
    $db = new db();
    require_once $ispchome . '/server/lib/mysql_clientdb.conf';
} else {
    die("i can't include the following to files that is needed for updating.!" . PHP_EOL .
            $ispchome . '/server/lib/mysql_clientdb.conf' . PHP_EOL .
            $ispchome . '/server/lib/config.inc.php' . PHP_EOL .
            $ispchome . '/server/lib/classes/db_mysql.inc.php');
}

$sogo_interface_version = $db->queryOneRecord("SELECT `value` FROM sys_config WHERE `name`='sogo_interface' AND `group`='interface'");
if ($sogo_interface_version === FALSE || !isset($sogo_interface_version['value']))
    $sogo_interface_version = $db->queryOneRecord("SELECT `value` FROM sys_config WHERE `name`='sogo' AND `group`='addons'");
if ($sogo_interface_version === FALSE || !isset($sogo_interface_version['value']))
    $sogo_interface_version = '0'; //* no db version
else {
    if (preg_match("/[0-9]\.([0-9])/i", $sogo_interface_version['value'], $matches))
        $sogo_interface_version = $matches[1];
    else
        $sogo_interface_version = $sogo_interface_version['value'];
}
$sogo_interface_version_php = $sogo_interface_version; //* needed for php file update

echo <<< EOF

Select the update type:
all ..........: run the full update process.
MySQL ........: update only mysql tables. 
php ..........: run php based updates.
Interface ....: update only interface files
Server .......: update only server files

Update: 
EOF;

$_update = Installer::readInput('all');
echo PHP_EOL;
switch (strtolower($_update)) {
    case 'all': {
            require '_ins/copy_files.php';
            Installer::$copy_files = $files_copy;
            Installer::installInterface($ispchome);
            Installer::installServer($ispchome);
            echo PHP_EOL;
            goto mysql;
            break;
        }
    case 'mysql': {
            mysql:
            echo "Starting database update" . PHP_EOL;
            echo "Current version: {$sogo_interface_version}" . PHP_EOL;
            echo "Latest version: {$sogo_interface_version_latest}" . PHP_EOL;
            if ($sogo_interface_version < $sogo_interface_version_latest) {
                $dbupd_run = true;
                while ($dbupd_run) {
                    $next_db_version = intval($sogo_interface_version + 1);
                    $dbupd_filename = "_ins/db/{$next_db_version}.sql";
                    if (is_file($dbupd_filename)) {
                        if (!empty($clientdb_password)) {
                            $cmd = "mysql --default-character-set=" . $conf['db_charset'] . " --force -h '" . $clientdb_host . "' -u '" . $clientdb_user . "' -p'" . $clientdb_password . "' " . $conf['db_database'] . " < " . $dbupd_filename;
                            system($cmd);
                        }
                        echo 'Loading SQL file: ' . $dbupd_filename . PHP_EOL;
                        $sogo_interface_version = $next_db_version;
                    } else
                        $dbupd_run = false;
                }
            } else {
                echo "No database update neded" . PHP_EOL;
            }
            echo PHP_EOL;
            goto php;
            break;
        }
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
    case 'php': {
            php:
            echo "Starting file based update" . PHP_EOL;
            if ($sogo_interface_version_php < $sogo_interface_version_latest) {
                require_once "_ins/PHPUpdateBaseClass.php";
                $phpupd_run = true;
                while ($phpupd_run == true) {
                    $next_php_version = intval($sogo_interface_version_php + 1);
                    if ($next_php_version <= 6) {
                        $sogo_interface_version_php = $next_php_version;
                        continue; /* no php upgrade before v7 */
                    }
                    if ($next_php_version == 7) {
                        echo PHP_EOL . "[WARNING] ++++++++++++++++++++++++++++++++++++++++++" . PHP_EOL;
                        echo "[WARNING] Update script for update 7, may run indefinitely " . PHP_EOL;
                        echo "[WARNING] so please empty the table 'sogo_module' manually " . PHP_EOL;
                        echo "[WARNING] and check the server config. that will fix this" . PHP_EOL;
                        echo "[WARNING] ++++++++++++++++++++++++++++++++++++++++++" . PHP_EOL . PHP_EOL;
                        $sogo_interface_version_php = $next_php_version;
                        continue;
                    }
                    $patch_filename = "_ins/php/{$next_php_version}.php";
                    if (is_file($patch_filename)) {
                        echo 'Loading PHP update file: ' . $patch_filename . PHP_EOL;
                        require_once $patch_filename;
                        if (isset($updateClass))
                            $updateClass->run();
                        else
                            echo 'Failed to run update file: ' . $patch_filename . PHP_EOL;
                        unset($updateClass);
                        $sogo_interface_version_php = $next_php_version;
                    } else
                        $phpupd_run = false;
                }
            } else {
                echo "No file based update neded" . PHP_EOL;
            }
            break;
        }
    default:
        echo PHP_EOL . "Invalid selection" . PHP_EOL;
        break;
}
if (method_exists($db, 'close') && !method_exists($db, '__destruct')) {
    try {
        $db->close();
    } catch (Exception $ex) {
        
    }
}
echo PHP_EOL . "all done i hope!" . PHP_EOL . PHP_EOL;

