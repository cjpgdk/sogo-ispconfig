<?php

/*
  cd /tmp
  wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
  tar -xvf sogo-ispconfig.tar.gz
  cd sogo-ispconfig-master
  php install.php
 */

/* create sogo user
CREATE USER 'dbsogo'@'localhost' IDENTIFIED BY 'dbsogo';
GRANT USAGE ON * . * TO 'dbsogo'@'localhost' IDENTIFIED BY 'dbsogo' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
CREATE DATABASE IF NOT EXISTS `dbsogo` ;
GRANT ALL PRIVILEGES ON `dbsogo` . * TO 'dbsogo'@'localhost';
*/

require '_ins/copy_files.php';
require '_ins/Installer.php';
Installer::$files_copy = $files_copy;

$failed = FALSE;
$srv_enable = TRUE;

$Installer = new Installer();
$Installer->run();

if (count(Installer::$errors) > 0) {
    echo "A list of all errors during the install" . PHP_EOL;
    foreach (Installer::$errors as $key => $value) {
        echo "{$key}" . PHP_EOL;
        if (is_array($value)) {
            foreach ($value as $i => $s) {
                echo "\t* {$s}" . PHP_EOL;
            }
        } else {
            echo "\t* {$value}" . PHP_EOL;
        }
    }
}
echo PHP_EOL;

echo "All done assuming no errors and all went well" . PHP_EOL;
echo "you will need to add SOGo config values to server config file:" . PHP_EOL;
echo Installer::$ispc_home_dir."/server/lib/config.inc.local.php" . PHP_EOL;
echo "A sample file can be found here.!" . PHP_EOL;
echo Installer::$ispc_home_dir."/server/lib/config.inc.local.sogo-sample.php" . PHP_EOL . PHP_EOL;

echo "AND DON'T forget to create a database SOGo can use for storage" . PHP_EOL;
