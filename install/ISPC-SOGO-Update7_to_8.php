<?php

/*
 * HOW TO..
 * COPY this file into ISPC-SOGO-Update7_to_8.php
 * and exectute it.
 * php ISPC-SOGO-Update7_to_8.php
 * 
 * DO NOT USE WGET....
 */

if (!defined('STDIN'))
    die("Im a CLI script run me from command line not from web.." . PHP_EOL);



/*
 * Copyright (C) 2014 Christian M. Jensen
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

$_plugin_www_location ="http://cmjscripter.net/files/scripts/ispc/sogo_config_plugin.inc.php";
$_interface_www_location ="http://cmjscripter.net/files/scripts/ispc/interface_simple.zip";

if (!isset($argv[1]))
    echo "Where is ISPConfig installed: [/usr/local/ispconfig]: ";
else
    echo "ISPConfig install path: [{$argv[1]}]: ";
echo PHP_EOL;
$ISPCinstallPath = _readinput((isset($argv[1]) ? $argv[1] : "/usr/local/ispconfig"));

if (!is_dir($ISPCinstallPath)) {
    echo "{$ISPCinstallPath} is not a valid dir name " . PHP_EOL;
    exit;
} else {
    if (!is_dir($ISPCinstallPath . DIRECTORY_SEPARATOR . "interface") || !is_dir($ISPCinstallPath . DIRECTORY_SEPARATOR . "server")) {
        echo "Unable to locate interface OR server directory in {$ISPCinstallPath}" . PHP_EOL;
        exit;
    }

}

$sogodbuserpw = $sogodbuser = $sogodbname = $mysql_server_host = "";

if (file_exists("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.php")) {
    $oldPlugin = file_get_contents("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.php");
    preg_match_all("/var.*sogopw.*'(.*)'/i", $oldPlugin, $sogodbuserpwmatches);
    $sogodbuserpw = (isset($sogodbuserpwmatches[1][0]) ? $sogodbuserpwmatches[1][0] : '');
    
    preg_match_all("/var.*sogouser.*'(.*)'/i", $oldPlugin, $sogodbusermatches);
    $sogodbuser = (isset($sogodbusermatches[1][0]) ? $sogodbusermatches[1][0] : '');

    preg_match_all("/var.*sogodb.*'(.*)'/i", $oldPlugin, $sogodbnamematches);
    $sogodbname = (isset($sogodbnamematches[1][0]) ? $sogodbnamematches[1][0] : '');

    preg_match_all("/var.*mysql_server_host.*'(.*)'/i", $oldPlugin, $mysql_server_hostmatches);
    $mysql_server_host = (isset($mysql_server_hostmatches[1][0]) ? $mysql_server_hostmatches[1][0] : '');

    /*
      var \$ispcdb = '${ISPCONFIGDB}';
      var \$sogobinary = '${SOGOBINARY}';
      var \$sogotoolbinary = '${SOGOTOOLBINARY}';
      var \$sogohomedir = '${SOGOHOMEDIR}';
      var \$sogoconffile = '${SOGOGNUSTEPCONFFILE}';
      var \$sogoinitscript = '${SOGOINITSCRIPT}';
      var \$templ_file = '${ISPCONFIGINSTALLPATH}/server/conf/sogo.conf-templ';
      var \$templ_domains_dir = '${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains';
      var \$mysql_server_host = '${MYSQLHOST}:${MYSQLPORT}';
     */

} else {
    echo "Unable to locate the SOGo plugin in {$ISPCinstallPath}/server/plugins-available/" . PHP_EOL;
    exit;
}

if (empty($mysql_server_host) || $mysql_server_host == "") {
    echo "i can't find SOGo Databse host give me the name thanks [127.0.0.1:3306]:";
    $mysql_server_host = _readinput("127.0.0.1:3306");
    if (empty($mysql_server_host) || $mysql_server_host == "") {
        echo "No Databse host!! exiting.." . PHP_EOL;
        exit;
    }

    if (!preg_match("/:[0-9]/i", $mysql_server_host)) {
        echo "ERROR Databse host format must be HOST:PORT!! exiting.." . PHP_EOL;
        exit;
    }
}

if (empty($sogodbname) || $sogodbname == "") {
    echo "i can't find SOGo Databse name give me the name thanks:";
    $sogodbname = _readinput();
    if (empty($sogodbname) || $sogodbname == "") {
        echo "No db name!! exiting.." . PHP_EOL;
        exit;
    }
}

if (empty($sogodbuser) || $sogodbuser == "") {
    echo "i can't find SOGo Databse user give me the name thanks:";
    $sogodbuser = _readinput();
    if (empty($sogodbuser) || $sogodbuser == "") {
        echo "No db user!! exiting.." . PHP_EOL;
        exit;
    }
}

if (empty($sogodbuserpw) || $sogodbuserpw == "") {
    echo "i can't find SOGo Databse user password give it to me thanks:";
    $sogodbuserpw = _readinput();
    if (empty($sogodbuserpw) || $sogodbuserpw == "") {
        echo "No db passwd!! exiting.." . PHP_EOL;
        exit;
    }
}

$ispcdb = "";
if (file_exists("{$ISPCinstallPath}/server/lib/config.inc.php")) {
    require $ISPCinstallPath . '/server/lib/config.inc.php';
    $ispcdb = $conf['db_database'];
} else {
    preg_match_all("/var.*ispcdb.*'(.*)'/i", $oldPlugin, $ispcdbmatches);
    $ispcdb = (isset($ispcdbmatches[1][0]) ? $ispcdbmatches[1][0] : '');
}

if (empty($ispcdb) || $ispcdb == "") {
    echo "i can't find ISPConfig Databse name give me the name thanks:";
    $ispcdb = _readinput();
    if (empty($ispcdb) || $ispcdb == "") {
        echo "No ISPConfig db name!! exiting.." . PHP_EOL;
        exit;
    }
}

echo "Location SOGo Binary.." . PHP_EOL;
$sogobinary = _getSOGoBinary();
echo "Location SOGo Tool Binary.." . PHP_EOL;
$sogotoolbinary = _getSOGoToolBinary();
echo "Location SOGo Home Dir.." . PHP_EOL;
$sogohomedir = _getSOGoHoseDir();
echo "Location SOGo INIT Script.." . PHP_EOL;
$sogoinitscript = _getSOGoINITScript();
echo "Building plugin.." . PHP_EOL;
$NEW_SOGO_PLUGIN = file_get_contents($_plugin_www_location);
$NEW_SOGO_PLUGIN = str_replace('{SOGOUSERPW}', $sogodbuserpw, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOUSERN}', $sogodbuser, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGODB}', $sogodbname, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{MYSQLHOST}:{MYSQLPORT}', $mysql_server_host, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{ISPCONFIGDB}', $ispcdb, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOBINARY}', $sogobinary, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOTOOLBINARY}', $sogotoolbinary, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOHOMEDIR}', $sogohomedir, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOGNUSTEPCONFFILE}', "{$sogohomedir}/GNUstep/Defaults/.GNUstepDefaults", $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOINITSCRIPT}', $sogoinitscript, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{ISPCONFIGINSTALLPATH}', $ISPCinstallPath, $NEW_SOGO_PLUGIN);
echo "Saving updated plugin: {$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php" . PHP_EOL;
@unlink("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.php");
@unlink("{$ISPCinstallPath}/server/plugins-enabled/sogo_config_plugin.inc.php");
file_put_contents("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php", $NEW_SOGO_PLUGIN);
if (!symlink ("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php" , "{$ISPCinstallPath}/server/plugins-enabled/sogo_config_plugin.inc.php")) {
    echo PHP_EOL .  str_repeat('=', 10) . PHP_EOL . "I can't activate the plugin, exec the following command.!" . PHP_EOL . "ln -s {$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php {$ISPCinstallPath}/server/plugins-enabled/sogo_config_plugin.inc.php" .  PHP_EOL .  str_repeat('=', 10) . PHP_EOL;
}
exec('mv '.$ISPCinstallPath.'/server/conf/sogo.conf-templ '.$ISPCinstallPath.'/server/conf/sogo.conf');
exec("mkdir -p {$ISPCinstallPath}/server/conf-custom/sogo/domains");
exec("chown ispconfig:ispconfig -R {$ISPCinstallPath}/");

echo "Rebuilding SOGo Views in database.." . PHP_EOL;
_rebuildSOGoDBViews($mysql_server_host, $sogodbuser, $sogodbuserpw, $sogodbname, $ispcdb);

echo "with this new update there come a simple interface" . PHP_EOL;
echo "No files will be overwritten all files are new" . PHP_EOL;
echo "refer to the file list in {$_interface_www_location}" . PHP_EOL;
echo "you like to install it? (y/n):";
$ins_interface = _readinput('Y');
if (strtolower($ins_interface) == 'y') {
    echo "Thank you :)" . PHP_EOL;
    $interface_ins_script = <<< EOF
#!/bin/sh

cd /tmp/
wget {$_interface_www_location}
unzip interface_simple.zip
rm -fr interface_simple/server
cp -rr interface_simple/* {$ISPCinstallPath}/
rm -fr interface_simple*
chown ispconfig:ispconfig -R {$ISPCinstallPath}/
EOF;
}
file_put_contents("/tmp/sogo-interface.sh", $interface_ins_script);
exec('chmod +x /tmp/sogo-interface.sh');
exec('/bin/sh /tmp/sogo-interface.sh');

echo PHP_EOL .  str_repeat('=', 10) . PHP_EOL . "I'm done here." . PHP_EOL . "Thanks for using my script :)" .  PHP_EOL .  str_repeat('=', 10) . PHP_EOL;

function _getSOGoBinary() {
    $sogobinary = exec('which sogod');
    if (empty($sogobinary) || $sogobinary == "") {
        echo "i can't find SOGo binary where is it:";
        $sogobinary = _readinput();
        if (empty($sogobinary) || $sogobinary == "") {
            echo "No SOGo binary!! exiting.." . PHP_EOL;
            exit;
        }
    }
    return $sogobinary;
}
function _getSOGoToolBinary() {
    $sogotoolbinary = exec('which sogo-tool');
    if (empty($sogotoolbinary) || $sogotoolbinary == "") {
        echo "i can't find SOGo Tool binary where is it:";
        $sogotoolbinary = _readinput();
        if (empty($sogotoolbinary) || $sogotoolbinary == "") {
            echo "No SOGo Tool binary!! exiting.." . PHP_EOL;
            exit;
        }
    }
    return $sogotoolbinary;
}

function _readinput($default = "") {
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    return (!empty($line) && trim($line) != "" ? trim($line) : $default);
}

function _getSOGoINITScript() {
    $sogoinitscript = '/etc/init.d/sogo';
    if (!file_exists($sogoinitscript)) {
        if (file_exists($sogoinitscript . "d")) {
            $sogoinitscript = '/etc/init.d/sogod';
        } else {
            echo "i can't find SOGo init script where is it:";
            $sogoinitscript = _readinput();
            if (empty($sogoinitscript) || $sogoinitscript == "") {
                echo "No SOGo init script!! exiting.." . PHP_EOL;
                exit;
            }
        }
    }
    return $sogoinitscript;
}

function _getSOGoHoseDir() {
    $sogohomedir = exec('getent passwd sogo | cut -d: -f6');
    if (empty($sogohomedir) || $sogohomedir == "") {
        echo "i can't find SOGo home dir where is it:";
        $sogohomedir = _readinput();
        if (empty($sogohomedir) || $sogohomedir == "") {
            echo "No SOGo Home dir!! exiting.." . PHP_EOL;
            exit;
        }
    }
    return $sogohomedir;
}



function _rebuildSOGoDBViews($sqlhost, $sqluser, $sqlpw, $sogodb, $ispcdb) {
    $sqlhost = explode(':', $sqlhost);
    $mysqli = new mysqli($sqlhost[0], $sqluser, $sqlpw, $sogodb, $sqlhost[1]);
    $mail_domains = $mysqli->query("SELECT `domain` FROM `{$ispcdb}`.`mail_domain` WHERE `active`='y'");
    while ($obj = $mail_domains->fetch_object()) {
        if (_mdomHasView($obj->domain,$sogodb,$mysqli)) {
            $dom = str_replace('-', '_', str_replace('.', '_', $obj->domain));
            $mysqli->query("DROP VIEW `{$sogodb}`.`sogo_users_{$dom}`");
        }
        $mysqli->query('CREATE VIEW `' .$sogodb. '`.`sogo_users_' . $dom . '` AS SELECT
	`login` AS c_uid,
	`login` AS c_name,
	`password` AS c_password,
	`name` AS c_cn,
	`email` AS mail,
	(SELECT `server_name` FROM ' . $ispcdb . '.`server`, ' . $ispcdb . '.`mail_user` WHERE `mail_user`.`server_id`=`server`.`server_id` AND `server`.`mail_server`=1 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1) AS imap_host 
        FROM ' . $ispcdb . '.`mail_user` AS ispcmu  WHERE `email` LIKE \'%@' . $dom . '\' AND disableimap=\'n\'');
    }
}
function _mdomHasView($dom,$sogodb,$mysqli) {
    $dom = str_replace('-', '_', str_replace('.', '_', $dom));
    $sql1 = "SELECT `TABLE_NAME` FROM `information_schema`.`VIEWS` WHERE `TABLE_SCHEMA`='{$sogodb}' AND `TABLE_NAME`='sogo_users_" . $dom . "'";
    $tmp = $mysqli->query($sql1);
    while ($obj = $tmp->fetch_object()) {
        if ($obj->TABLE_NAME == 'sogo_users_' . $dom) {
            return true;
        }
    }
    return false;
}