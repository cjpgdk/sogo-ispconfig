<?php

/**
 * install ONLY mysql tables to ispconfig
 * requiered on all servers
 */

require '_ins/Installer.php';

$Installer = new Installer('mysqltables');

$Installer->run();