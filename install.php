<?php

/*
  cd /tmp
  wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
  tar -xvf sogo-ispconfig.tar.gz
  cd sogo-ispconfig-master
  php install.php
 */

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

Install: 
EOF;
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
    default:
        echo PHP_EOL . "Invalid selection" . PHP_EOL;
        break;
}

