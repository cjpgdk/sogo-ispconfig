<?php

/*
 * delete all files related to this adon
 * 
  cd /tmp
  wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
  tar -xvf sogo-ispconfig.tar.gz
  cd sogo-ispconfig-master
  php uninstall-files.php
 */

require '_ins/copy_files.php';
require '_ins/old_files.php';

function readInput($default = "") {
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    return (!empty($line) && trim($line) != "" ? trim($line) : $default);
}

echo str_repeat('=', 50) . PHP_EOL;
echo "= This file will be removed with next update" . PHP_EOL . "= use uninstall.php instead";
echo str_repeat('=', 50) . PHP_EOL . PHP_EOL;

echo "location of ISPConfig folder? [/usr/local/ispconfig]: ";
$ispcdir = readInput("/usr/local/ispconfig");

echo PHP_EOL . PHP_EOL;
foreach ($old_files as $key => $value) {
    if ($value['type'] == 'file')
        @unlink($ispcdir . '/' . $value['file']);
    else if ($value['type'] == 'folder')
        echo "The folowing folder is not delete: " . $ispcdir . '/' . $value['folder'] . PHP_EOL;
}

foreach ($files_copy as $key => $value) {
    foreach ($value as $value2) {
        @unlink($ispcdir . '/' . $key . "/" . $value2);
    }
}