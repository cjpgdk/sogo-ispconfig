<?php

/*
 * install ONLY mysql tables to ispconfig
 * requiered on all servers

  cd /tmp
  wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
  tar -xvf sogo-ispconfig.tar.gz
  cd sogo-ispconfig-master
  php install-mysqltables.php
 */

require '_ins/Installer.php';

$Installer = new Installer('mysqltables');

$Installer->run();
