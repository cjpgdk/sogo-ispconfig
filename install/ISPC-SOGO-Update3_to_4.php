<?php

/*
 * HOW TO..
 * COPY this file into ISPC-SOGO-Update3_to_4.php
 * and exectute it.
 * php ISPC-SOGO-Update3_to_4.php
 * 
 * DO NOT USE WGET....
 */
if (!defined('STDIN'))
    die("Im a CLI script run me from command line not from web.." . PHP_EOL);

/*
 * Copyright (C) 2013 Christian M. Jensen
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
$NEW_SOGO_PLUGIN = <<< EOF
<?php

/*
 * Copyright (C) 2013 Christian M. Jensen
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

class sogo_config_plugin {

    var \$plugin_name = 'sogo_config_plugin';
    var \$class_name = 'sogo_config_plugin';
    var \$sogo_su_cmd = "sudo -u sogo";
    var \$sogopw = '{$sogodbuserpw}';
    var \$sogouser = '{$sogodbuser}';
    var \$sogodb = '{$sogodbname}';
    var \$ispcdb = '{$ispcdb}';
    var \$sogobinary = '{$sogobinary}';
    var \$sogotoolbinary = '{$sogotoolbinary}';
    var \$sogohomedir = '{$sogohomedir}';
    var \$sogoconffile = '{$sogohomedir}/GNUstep/Defaults/.GNUstepDefaults';
    var \$sogoinitscript = '{$sogoinitscript}';
    var \$templ_file = '{$ISPCinstallPath}/server/conf/sogo.conf-templ';
    var \$templ_domains_dir = '{$ISPCinstallPath}/server/conf/sogo_domains';
    var \$mysql_server_host = '{$mysql_server_host}';

    function onInstall() {
        global \$conf;
        if (\$conf['services']['mail'] == true) {
            return true;
        } else {
            return false;
        }
    }

    function onLoad() {
        global \$app;
        \$app->plugins->registerEvent('mail_domain_delete', \$this->plugin_name, 'reconfigure');
        \$app->plugins->registerEvent('mail_domain_insert', \$this->plugin_name, 'reconfigure');
        \$app->plugins->registerEvent('mail_domain_update', \$this->plugin_name, 'reconfigure');
        \$app->plugins->registerEvent('mail_user_delete', \$this->plugin_name, 'remove_sogo_mail_user');
    }

    function remove_sogo_mail_user(\$event_name, \$data) {
        global \$app, \$conf;
        if (\$event_name == 'mail_user_delete') {
            exec(\$this->sogo_su_cmd . ' ' . \$this->sogotoolbinary . ' remove ' . escapeshellarg(\$data['old']['login']));
            sleep(1);
        }
    }

    function reconfigure(\$event_name, \$data) {
        global \$app, \$conf;
        \$flag = false;
        if (\$event_name == 'mail_domain_delete') {
            \$flag = \$this->remove_sogo_maildomain((isset(\$data['new']['domain']) ? \$data['new']['domain'] : \$data['old']['domain']));
        } else if (\$event_name == 'mail_domain_insert') {
            \$flag = true;
        } else if (\$event_name == 'mail_domain_update') {
            \$flag = true;
        } else {
            //* i can't work with that give me a command...
            // /PATH/to/ISPConfig_DIR/server/SOGO-reconfigure.log
            // file_put_contents('SOGO-reconfigure.log', print_r(\$event_name,true)."\\n\\n".print_r(\$data,true));
        }
        if (\$flag) {
            \$active_mail_domains = \$app->db->queryAllRecords('SELECT `domain` FROM `mail_domain` WHERE `active`=\'y\'');
            \$sogo_conf = file_get_contents(\$this->templ_file);
            \$tmp_conf = "";
            foreach (\$active_mail_domains as \$vd) {
                \$tmp_conf .= \$this->build_conf_sogo_maildomain(\$vd['domain']);
                //* create if not exist
                \$this->create_sogo_view(\$vd['domain']);
            }
            \$sogo_conf = str_replace('{{SOGODOMAINSCONF}}', \$tmp_conf, \$sogo_conf);
            if (!file_put_contents(\$this->sogoconffile, \$sogo_conf)) {
                \$app->log('ERROR. unable to reconfigure SOGo..', LOGLEVEL_ERROR);
                return;
            } else {
                exec(\$this->sogoinitscript . ' restart');
                //** make the system wait..
                sleep(2);
            }
        }
    }

    function remove_sogo_maildomain(\$dom) {
        global \$app, \$conf;
        //* TODO: validate domain the correct way not by filter_var
        if (empty(\$dom) || filter_var('http://' . \$dom, FILTER_VALIDATE_URL) === false) {
            \$app->log('ERROR. removeing sogo mail domain.. domain invalid [' . \$dom . ']', LOGLEVEL_ERROR);
            return false;
        }

        \$dom_no_point = str_replace('-', '_', str_replace('.', '_', \$dom));
        \$sqlres = \$this->_sqlConnect();
        \$sqlres->query('DROP VIEW `sogo_users_' . \$dom_no_point . '`');
        /* Broke my connection??? */
        /* @\$sqlres->close(); */
        return true;
    }

    function create_sogo_view(\$dom) {
        global \$app, \$conf;
        \$sqlres = \$this->_sqlConnect();

        \$dom_no_point = str_replace('-', '_', str_replace('.', '_', \$dom));
        \$sql1 = "SELECT `TABLE_NAME` FROM `information_schema`.`VIEWS` WHERE `TABLE_SCHEMA`='{\$this->sogodb}' AND `TABLE_NAME`='sogo_users_" . \$dom_no_point . "'";

        \$tmp = \$sqlres->query(\$sql1);
        while (\$obj = \$tmp->fetch_object()) {
            if (\$obj->TABLE_NAME == 'sogo_users_' . \$dom_no_point) {
                return true;
            }
        }

        \$sqlres->query('CREATE VIEW sogo_users_' . \$dom_no_point . ' AS SELECT
	`login` AS c_uid,
	`login` AS c_name,
	`password` AS c_password,
	`name` AS c_cn,
	`email` AS mail,
	(SELECT `server_name` FROM ' . \$this->ispcdb . '.`server`, ' . \$this->ispcdb . '.`mail_user` WHERE `mail_user`.`server_id`=`server`.`server_id` AND `server`.`mail_server`=1 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1) AS imap_host 
        FROM ' . \$this->ispcdb . '.`mail_user` AS ispcmu  WHERE `email` LIKE \'%@' . \$dom_no_point . '\' AND disableimap=\'n\'');
        if (!empty(\$sqlres->error))
            \$app->log('ERROR. unable to create SOGo view[sogo_users_' . \$dom_no_point . '].. ' . \$sqlres->error, LOGLEVEL_ERROR);
        /* Broke my connection??? */
        /* @\$sqlres->close(); */
        return true;
    }

    function build_conf_sogo_maildomain(\$dom) {
        global \$app, \$conf;
        \$dom_no_point = str_replace('-', '_', str_replace('.', '_', \$dom));
        /* For mail aliases..
          <key>MailFieldNames</key>
          <array>
          <string>Col1</string>
          <string>Col2</string>
          <string>Col3</string>
          </array>
         */
        \$sogo_conf = "";
        \$sogo_conf_vars = array(
            '{{DOMAIN}}' => \$dom,
            '{{DOMAINADMIN}}' => 'postmaster@' . \$dom,
            '{{SOGOUNIQID}}' => \$dom_no_point,
            '{{CONNECTIONVIEWURL}}'=>"mysql://{\$this->sogouser}:{\$this->sogopw}@{\$this->mysql_server_host}/{\$this->sogodb}/sogo_users_{\$dom_no_point}"
        );
        if (file_exists("{\$this->templ_domains_dir}/{\$dom_no_point}.conf")) {
            \$sogo_conf = file_get_contents("{\$this->templ_domains_dir}/{\$dom}.conf");
        } else {
            if (!file_exists("{\$this->templ_domains_dir}/{\$dom_no_point}.conf"))
                \$app->log('ERROR. loading domains config.. file: '."{\$this->templ_domains_dir}/{\$dom_no_point}.conf", LOGLEVEL_DEBUG);
            if (file_exists("{\$this->templ_domains_dir}/domains_default.conf")) {
                \$sogo_conf = file_get_contents("{\$this->templ_domains_dir}/domains_default.conf");
            }  else {
                \$app->log('ERROR. loading domain config.. file: ' . "{\$this->templ_domains_dir}/domains_default.conf", LOGLEVEL_ERROR);
                return;
            }
        }
        if (!empty(\$sogo_conf)) {
            foreach (\$sogo_conf_vars as \$key => \$value) {
                \$sogo_conf = preg_replace("/{\$key}/i", \$value, \$sogo_conf);
            }
        }
        return \$sogo_conf;
    }

    function _sqlConnect() {
        \$_sqlserver = explode(':', \$this->mysql_server_host);
        \$sqlres = new mysqli(\$_sqlserver[0], \$this->sogouser, \$this->sogopw, \$this->sogodb, \$_sqlserver[1]);
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\\n", mysqli_connect_error());
            exit;
        }
        return \$sqlres;
    }
}
?>
EOF;
echo "Saving updated plugin: {$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.php" . PHP_EOL;
file_put_contents("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.php", $NEW_SOGO_PLUGIN);
echo "Rebuilding SOGo Vies in database.." . PHP_EOL;
_rebuildSOGoDBViews($mysql_server_host, $sogodbuser, $sogodbuserpw, $sogodbname, $ispcdb);

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

?>