<?php

/*
  cd /tmp
  wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
  tar -xvf sogo-ispconfig.tar.gz
  cd sogo-ispconfig-master
  php update.php
 */
$sogo_interface_version_latest = "6";

require '_ins/Installer.php';
$ispchome = Installer::getISPConfigHomeDir();
Installer::$ispc_home_dir = $ispchome;
$db = NULL;
if (file_exists($ispchome . '/server/lib/config.inc.php') && file_exists($ispchome . '/server/lib/classes/db_mysql.inc.php') && file_exists($ispchome . '/server/lib/mysql_clientdb.conf')) {
    require_once $ispchome . '/server/lib/config.inc.php';
    require_once $ispchome . '/server/lib/classes/db_mysql.inc.php';
    $db = new db();
    require_once $ispchome . '/server/lib/mysql_clientdb.conf';
} else {
    die("i can't include the following to files that is needed for updating.!" . PHP_EOL . $ispchome . '/server/lib/mysql_clientdb.conf' . PHP_EOL . $ispchome . '/server/lib/config.inc.php' . PHP_EOL . $ispchome . '/server/lib/classes/db_mysql.inc.php');
}

require '_ins/copy_files.php';
Installer::$files_copy = $files_copy;

$sogo_interface_version = $db->queryOneRecord("SELECT `value` FROM sys_config WHERE `name`='sogo_interface' AND `group`='interface'");
if ($sogo_interface_version === FALSE || !isset($sogo_interface_version['value']))
    $sogo_interface_version = $db->queryOneRecord("SELECT `value` FROM sys_config WHERE `name`='sogo' AND `group`='addons'");
if ($sogo_interface_version === FALSE || !isset($sogo_interface_version['value']))
    $sogo_interface_version = '0'; //* no db version
if (preg_match("/[0-9]\.([0-9])/i", $sogo_interface_version['value'], $matches))
    $sogo_interface_version = $matches[1];
else
    $sogo_interface_version = $sogo_interface_version['value'];

echo PHP_EOL . PHP_EOL; //* give some space thanks
//* start copy files

echo "Update ISPConfig interface files (Y/N) [Y]: ";
if (strtolower(Installer::readInput('y')) == 'y') {
    Installer::copyFiles('interface');
}
echo PHP_EOL;
echo "Update ISPConfig server files (Y/N) [Y]: ";
if (strtolower(Installer::readInput('y')) == 'y') {
    Installer::copyFiles('server');
}
echo PHP_EOL;

//* db update
echo "Starting database update" . PHP_EOL;
echo "Current version: {$sogo_interface_version}" . PHP_EOL;
echo "Latest version: {$sogo_interface_version_latest}" . PHP_EOL;
if ($sogo_interface_version < $sogo_interface_version_latest) {
    $dbupd_run = true;
    while ($dbupd_run == true) {
        $next_db_version = intval($sogo_interface_version + 1);
        $patch_filename = "_ins/db/{$next_db_version}.sql";
        if (is_file($patch_filename)) {
            if (!empty($clientdb_password)) {
                $cmd = "mysql --default-character-set=" . $conf['db_charset'] . " --force -h '" . $clientdb_host . "' -u '" . $clientdb_user . "' -p'" . $clientdb_password . "' " . $conf['db_database'] . " < " . $patch_filename;
                system($cmd);
            }
            echo 'Loading SQL file: ' . $patch_filename . PHP_EOL;
            $sogo_interface_version = $next_db_version;
        } else {
            $dbupd_run = false;
        }
    }
} else {
    echo "No database update neded" . PHP_EOL;
}
echo PHP_EOL . "all done i hope!" . PHP_EOL;
