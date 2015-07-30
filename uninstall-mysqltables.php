<?php

/*
 * uninstall ONLY mysql tables
 * 
  cd /tmp
  wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
  tar -xvf sogo-ispconfig.tar.gz
  cd sogo-ispconfig-master
  php uninstall-mysqltables.php
 */

echo str_repeat('=', 50) . PHP_EOL;
echo "= This file will be removed with next update" . PHP_EOL . "= use uninstall.php instead";
echo str_repeat('=', 50) . PHP_EOL . PHP_EOL;
 
function readInput($default = "") {
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    return (!empty($line) && trim($line) != "" ? trim($line) : $default);
}
 
 
$mysql_tables_dropispc = "_ins/drop_tables.sql";
echo "MySQL Host? [127.0.0.1]: ";
$mysql_host = readInput("127.0.0.1");
echo PHP_EOL;
echo "MySQL admin user? [root]: ";
$mysql_admin = readInput("root");
echo PHP_EOL;
echo "MySQL password? []: ";
$mysql_password = str_replace('"', '\"', readInput(""));
echo PHP_EOL;
echo "ISPConfig database? [dbispconfig]: ";
$mysql_database = readInput("dbispconfig");
echo PHP_EOL;
$command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\" {$mysql_database} < " .  $mysql_tables_dropispc;
echo exec($command) . PHP_EOL;