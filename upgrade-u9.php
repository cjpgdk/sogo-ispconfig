#!/usr/bin/php
<?php
    die('Please don\'t use this script until you read (and i\'m done writing) this wiki page [https://github.com/cmjnisse/sogo-ispconfig/wiki/Upgrading-from-%22update-9%22]');
if (PHP_SAPI !== 'cli' || !empty($_SERVER['REMOTE_ADDR']))
    die('Run me from command line');

$options = getopt("d:");
if (strtolower($options["d"]) === 'yes') {
    define('TEST_RUN', TRUE);
} else {
    define('TEST_RUN', FALSE);
}

$all_errors = array();

chdir(realpath(__DIR__));

function readInput($default = "") {
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    return (!empty($line) && trim($line) != "" ? trim($line) : $default);
}

function action_delete($path, $type = 'file') {
    echo "Delete({$type}): {$path}\n";
    if (!TEST_RUN) {
        exec("rm -fr {$path}");
    }
}

function action_delete_if_empty($path, $type = 'folder') {
    $empty = TRUE;
    if (is_dir($path)) {
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $empty = FALSE;
                }
            }
            closedir($handle);
        }
    }
    if ($empty) {
        echo "Delete({$type}): {$path}\n";
        if (!TEST_RUN) {
            exec("rm -fr {$path}");
        }
    } else {
        echo "Not empty so not removeing ({$type}): {$path}\n";
    }
}

function sogod_read($what, $callback = NULL) {
    $result = "";
    try {
        exec('sudo -u sogo defaults read sogod ' . escapeshellarg($what), $result);
        if ($callback !== NULL && function_exists($callback)) {
            $callback($result);
        }
    } catch (Exception $exc) {
        //echo $exc->getTraceAsString();
    }
    return $result;
}

function _test_sogo_read_domains($result) {
    $domains = implode("\n", $result);
    if (preg_match_all("#SOGoMailDomain = \"(.*)\"#i", $domains, $matches)) {
        foreach ($matches[1] as $key => $value) {
            echo "Domain in SOGo: {$value}\n";
        }
    } else {
        die("\n\nSorry reading config from sogo failed, i cannot continue without the possibility of destroying your server\n\n");
    }
    echo "\n";
}

function installTablesMySQL() {
    if (file_exists("_ins/tables.sql")) {
        //* add mysql tables
        echo "MySQL Host? [127.0.0.1]: ";
        $mysql_host = readInput("127.0.0.1");
        echo "\n";
        echo "MySQL admin user? [root]: ";
        $mysql_admin = readInput("root");
        echo "\n";
        echo "MySQL password? []: ";
        $mysql_password = str_replace('"', '\"', readInput(""));
        echo "\n";
        echo "ISPConfig database? [dbispconfig]: ";
        $mysql_database = readInput("dbispconfig");
        echo "\n";
        $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p\"{$mysql_password}\" {$mysql_database} < _ins/tables.sql";
        if (!TEST_RUN) {
            echo exec($command) . "\n";
        } else {
            echo "Run command: {$command}\n\n";
        }
    } else {
        echo "\n\n[FAIL]: Unable to locate mysql tables file (interface, plugin and module WILL NOT WORK without them)\nRedownload the tables and import them manualy before using\n\n";
    }
}

function sogo_config_keys_exists($key, $mysqli, $dbispconfig = 'dbispconfig') {
    if (!TEST_RUN) {
        $got_name = FALSE;
        if ($result = $mysqli->query("SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_NAME`='sogo_config' AND `TABLE_SCHEMA`='{$dbispconfig}' AND `COLUMN_NAME` LIKE '%{$mysqli->escape_string($key)}%'")) {
            while ($obj = $result->fetch_object()) {
                if ($obj->COLUMN_NAME == $key) {
                    $got_name = true;
                    break;
                }
            }
            $result->close();
        }
        return $got_name;
    }
    return true; //* we test so we assume all is fine
}

function sogo_domains_keys_exists($key, $mysqli, $dbispconfig = 'dbispconfig') {
    if (!TEST_RUN) {
        $got_name = FALSE;
        if ($result = $mysqli->query("SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_NAME`='sogo_domains' AND `TABLE_SCHEMA`='{$dbispconfig}' AND `COLUMN_NAME` LIKE '%{$mysqli->escape_string($key)}%'")) {
            while ($obj = $result->fetch_object()) {
                if ($obj->COLUMN_NAME == $key) {
                    $got_name = true;
                    break;
                }
            }
            $result->close();
        }
        return $got_name;
    }
    return true; //* we test so we assume all is fine
}

require '_ins/old_files.php';

echo "\n\nISPConfig home directory [/usr/local/ispconfig]: ";
$ispchome = readInput('/usr/local/ispconfig');
echo "\n";

echo "Username SOGo is running under [sogo]: ";
$sogo_user = readInput('sogo');
echo "\n";

$sogo_home_dir = exec('getent passwd ' . escapeshellarg($sogo_user) . ' | cut -d: -f6');
if (!is_dir($sogo_home_dir)) {
    echo "Unable to locate sogo home directory!\n";
    echo "SOGo Home dir [{$sogo_home_dir}]: ";
    $sogo_home_dir = readInput($sogo_home_dir);
    echo "\n";
} else {
    echo "Found sogo home directory in {$sogo_home_dir}\n";
}

$sogo_tool_bin = exec('which sogo-tool');
if (!file_exists($sogo_tool_bin)) {
    echo "Where is sogo-tool binary? []: ";
    $sogo_tool_bin = readInput('');
    echo "\n";
} else {
    echo "Found sogo-tool in {$sogo_tool_bin}\n\n";
}
sogod_read('domains', '_test_sogo_read_domains');
installTablesMySQL();


require_once $ispchome . '/server/lib/config.inc.php';
if (file_exists("{$sogo_home_dir}/GNUstep/Defaults/.GNUstepDefaults")) {

    require 'server/lib/classes/sogo_config.inc.php';
    $sogo_config = new sogo_config();
    if ($sogo_config->loadSOGoConfigFile("{$sogo_home_dir}/GNUstep/Defaults/.GNUstepDefaults")) {
        echo "\nStarting import of SOGo main config\n";
        $sogo_config = $sogo_config->parse();
        $domains = $sogo_config['sogod']['domains'];
        unset($sogo_config['sogod']['domains']);
        $sogo_config = $sogo_config['sogod'];

        if (!isset($conf['server_id'])) {
            echo "Trying to locate the server id? []: ";
            $server_id = readInput('');
            echo "\n";
        } else {
            $server_id = $conf['server_id'];
        }
        $db_host = $conf['db_host'];
        $db_database = $conf['db_database'];
        $db_user = $conf['db_user'];
        $db_password = $conf['db_password'];

        $mysqli = new mysqli($db_host, $db_user, $db_password, $db_database);
        $server_name = "";
        if ($result = $mysqli->query("SELECT `server_name` FROM `server` WHERE `server_id`=" . intval($server_id))) {
            while ($obj = $result->fetch_object()) {
                $server_name = $obj->server_name;
            }
            $result->close();
        }
        if (empty($server_name)) {
            $server_name = exec('hostname -f');
            echo "Trying to locate the server name? [{$server_name}]: ";
            $server_name = readInput($server_name);
            echo "\n";
        }
        $_sogo_main_config_sql = "INSERT INTO `sogo_config` (`sogo_id`, `sys_userid`, `sys_groupid`, `sys_perm_user`, "
                . "`sys_perm_group`, `sys_perm_other`, `server_id`, `server_name`,%CONFVARS%) VALUES (%VALUES%)";

        $_sogo_main_config_sql_confvars = "";
        $_sogo_main_config_sql_values = "NULL, '0', '0', 'riud', 'riud', '', '{$server_id}', '{$mysqli->escape_string($server_name)}',";

        $_sogo_main_config_unkown_keys = array();

        foreach ($sogo_config as $key => $value) {

            /* Misspelled items */
            if ($key == "SOGoForceIMAPLoginWithEmail")
                $key = "SOGoForceExternalLoginWithEmail";
            if ($key == "SOGoACLsSendEMailNotifcations")
                $key = "SOGoACLsSendEMailNotifications";
            if ($key == "SOGoAppointmentSendEMailNotifcations")
                $key = "SOGoAppointmentSendEMailNotifications";
            if ($key == "SOGoFoldersSendEMailNotifcations")
                $key = "SOGoFoldersSendEMailNotifications";

            if (sogo_config_keys_exists($key, $mysqli, $db_database)) {
                $_sogo_main_config_sql_confvars .= " `{$key}`,";
                if (is_array($value)) {
                    $_sogo_main_config_sql_values .= " '" . $mysqli->escape_string(trim(implode(',', $value), ',')) . "',";
                } else {
                    $_sogo_main_config_sql_values .= " '" . $mysqli->escape_string($value) . "',";
                }
            } else {
                $_sogo_main_config_unkown_keys[] = "{$key} => {$value}";
            }
        }

        $_sogo_main_config_sql = str_replace('%CONFVARS%', trim($_sogo_main_config_sql_confvars, ','), $_sogo_main_config_sql);
        $_sogo_main_config_sql = str_replace('%VALUES%', trim($_sogo_main_config_sql_values, ','), $_sogo_main_config_sql);
        unset($_sogo_main_config_sql_confvars, $_sogo_main_config_sql_values);

        if (!TEST_RUN) {
            if (!$mysqli->query($_sogo_main_config_sql)) {
                $all_errors[] = $mysqli->error . "\n" . str_repeat('-', 25) . "\n" . $_sogo_main_config_sql . "\n" . str_repeat('-', 25) . "\n";
                echo $mysqli->error . "\n" . str_repeat('-', 25) . "\n" . $_sogo_main_config_sql . "\n" . str_repeat('-', 25) . "\n";
            }
        } else {
            echo "\nRun SQL command:\n$_sogo_main_config_sql\n\n";
        }
        if (!empty($_sogo_main_config_unkown_keys)) {
            $err = "The following configuration keys were not inserted into sogo configuration\nthey are most likely unknown or misspelled\n\t" . implode("\n\t", $_sogo_main_config_unkown_keys) . "\n\n";
            $all_errors[] = $err;
            echo "{$err}";
            unset($_sogo_main_config_unkown_keys);
        }
        unset($_sogo_main_config_unkown_keys, $_sogo_main_config_sql, $_sogo_main_config_sql_values, $_sogo_main_config_sql_confvars);
        echo "\n\nStarting import of SOGo domains config\n";

        $_sogo_domain_config_sql = "INSERT INTO `sogo_domains` (`sogo_id`, `sys_userid`, `sys_groupid`, `sys_perm_user`, "
                . "`sys_perm_group`, `sys_perm_other`, `domain_id`, `domain_name`, `server_id`, `server_name`,%CONFVARS%) VALUES (%VALUES%)";

        $_sogo_domain_config_sql_confvars = "";
        $_sogo_domain_config_sql_values = "NULL,'%sys_userid%','%sys_groupid%','%sys_perm_user%','%sys_perm_group%','%sys_perm_other%','%DOMAIN_ID%','%DOMAIN_NAME%','{$server_id}','{$mysqli->escape_string($server_name)}'";
        $_sogo_domain_config_sql_values2 = "";
        $_sogo_domain_config_unkown_keys = array();

        foreach ($domains as $key => $value) {
            $_sogo_domain_config_sql_confvars = $_sogo_domain_config_sql_values2 = "";
            foreach ($value as $dkey => $dvalue) {
                if ($dkey == "SOGoUserSources" || $dkey == "SOGoMailDomain")
                    continue;
                if (sogo_domains_keys_exists($dkey, $mysqli, $db_database)) {
                    $_sogo_domain_config_sql_confvars .= " `{$dkey}`,";
                    if (is_array($dvalue)) {
                        $_sogo_domain_config_sql_values2 .= " '" . $mysqli->escape_string(trim(implode(',', $dvalue), ',')) . "',";
                    } else {
                        $_sogo_domain_config_sql_values2 .= " '" . $mysqli->escape_string($dvalue) . "',";
                    }
                } else {
                    $_sogo_domain_config_unkown_keys[$key][] = "{$dkey} => {$dvalue}";
                }
            }
            $sys_userid = $sys_groupid = $sys_perm_user = $sys_perm_group = $sys_perm_other = "";
            $domain_id = 0;
            if ($result = $mysqli->query('SELECT `domain_id`,`sys_userid`,`sys_groupid`,`sys_perm_user`,`sys_perm_group`,`sys_perm_other` FROM `mail_domain` WHERE `domain`=\'' . $mysqli->escape_string($key) . '\'')) {
                while ($obj = $result->fetch_object()) {
                    $domain_id = $obj->domain_id;
                    $sys_userid = $obj->sys_userid;
                    $sys_groupid = $obj->sys_groupid;
                    $sys_perm_user = $obj->sys_perm_user;
                    $sys_perm_group = $obj->sys_perm_group;
                    $sys_perm_other = $obj->sys_perm_other;
                }
            } else {
                $err = "\nSkiping configuration of domain {$key}\nSQL Error: {$mysqli->error}\n";
                $all_errors[] = $err;
                echo $err;
                continue;
            }
            if ($domain_id > 0) {
                $_sogo_domain_sql = str_replace('%CONFVARS%', trim($_sogo_domain_config_sql_confvars, ','), $_sogo_domain_config_sql);
                $_sogo_domain_sql = str_replace('%VALUES%', trim($_sogo_domain_config_sql_values . ", " . $_sogo_domain_config_sql_values2, ','), $_sogo_domain_sql);
                $_sogo_domain_sql = str_replace('%DOMAIN_NAME%', $mysqli->escape_string($key), $_sogo_domain_sql);
                $_sogo_domain_sql = str_replace('%DOMAIN_ID%', $mysqli->escape_string($domain_id), $_sogo_domain_sql);
                $_sogo_domain_sql = str_replace('%sys_userid%', $mysqli->escape_string($sys_userid), $_sogo_domain_sql);
                $_sogo_domain_sql = str_replace('%sys_groupid%', $mysqli->escape_string($sys_groupid), $_sogo_domain_sql);
                $_sogo_domain_sql = str_replace('%sys_perm_user%', $mysqli->escape_string($sys_perm_user), $_sogo_domain_sql);
                $_sogo_domain_sql = str_replace('%sys_perm_group%', $mysqli->escape_string($sys_perm_group), $_sogo_domain_sql);
                $_sogo_domain_sql = str_replace('%sys_perm_other%', $mysqli->escape_string($sys_perm_other), $_sogo_domain_sql);

                if (!TEST_RUN) {
                    if (!$mysqli->query($_sogo_domain_sql)) {
                        $all_errors[] = $mysqli->error . "\n" . str_repeat('-', 25) . "\n" . $_sogo_domain_sql . "\n" . str_repeat('-', 25) . "\n";
                        echo $mysqli->error . "\n" . str_repeat('-', 25) . "\n" . $_sogo_domain_sql . "\n" . str_repeat('-', 25) . "\n";
                    }
                } else {
                    echo "\nRun SQL command:\n$_sogo_domain_sql\n\n";
                }
            } else {
                $err = "\nSkiping configuration of domain {$key}\nCan not find domain id\n";
                $all_errors[] = $err;
                echo $err;
            }
        }

        if (!empty($_sogo_domain_config_unkown_keys)) {
            foreach ($_sogo_domain_config_unkown_keys as $key => $value) {
                $err = "The following configuration keys were not inserted into sogo domain configuration\nthey are most likely unknown or misspelled\n\t**{$key}**\n\t" . implode("\n\t", $value) . "\n\n";
                $all_errors[] = $err;
                echo "{$err}";
            }
            unset($_sogo_domain_config_unkown_keys);
        }

        $mysqli->close();

        echo "\n" . str_repeat('-', 25) . "\n";
        echo "Starting SOGo backup this can take a long time so be patient";
        echo "\n" . str_repeat('-', 25) . "\n\n";
        echo "Start SOGo Backup (Y/N) [Y]: ";
        if (strtolower(readInput('y')) == "y") {
            define('SOGO_BACKUP', true);
            echo "Storing backups in - {$sogo_home_dir}/Backup/\n\n";
            if (file_exists("{$sogo_home_dir}/Backup/") || is_dir("{$sogo_home_dir}/Backup/") || @mkdir("{$sogo_home_dir}/Backup")) {
                exec("chown {$sogo_user}:{$sogo_user} -R {$sogo_home_dir}/Backup");
                exec("sudo -u {$sogo_user} {$sogo_tool_bin} backup \"{$sogo_home_dir}/Backup/\" ALL");
            }
            echo "SOGo Backup Completed\n\n";
        }
    } else {
        $all_errors[] = "\n\nim not able to load your sogo configuration\nthis means you will have to recreate all configurations once this update is done.\n\n";
    }
} else {
    $all_errors[] = "\n\nim not able to load your sogo configuration\nthis means you will have to recreate all configurations once this update is done.\n\n";
}
if (!defined('SOGO_BACKUP'))
    define('SOGO_BACKUP', false);

if (!empty($all_errors)) {
    echo "\n" . str_repeat('-', 25) . "\n";
    echo "There were some errors in the previous command/steps\nPlease take a good look at the following errors before you continue";
    echo "\n" . str_repeat('-', 25) . "\n\n";
    foreach ($all_errors as $key => $value) {
        echo "\n[{$key}]:\t{$value}\n";
    }

    echo "Continue (Y/N) [N]: ";
    if (strtolower(readInput('n')) == "n") {
        die("\n");
    }
}


/* ############# get old config settings from plugin ############# */
// 
require $ispchome . '/server/plugins-available/sogo_config_plugin.inc.php';
$sogo_config_plugin = new sogo_config_plugin();
//    var $plugin_name = 'sogo_config_plugin';
//    var $class_name = 'sogo_config_plugin';
//    var $sogo_su_cmd = "sudo -u sogo";
$sogo_su_cmd = $sogo_config_plugin->sogo_su_cmd;
//    var $sogo_su = "sogo";
//    var $sogopw = '0gywwPjXBSf3kRUL9BUpqwv54';
$sogo_sogopw = $sogo_config_plugin->sogopw;
//    var $sogouser = 'sogodbuser';
$sogo_sogouser = $sogo_config_plugin->sogouser;
//    var $sogodb = 'sogodb';
$sogo_sogodb = $sogo_config_plugin->sogodb;
//    var $ispcdb = 'dbispconfig'; //* kept here to allow for difrent database with less data.!
//    var $sogobinary = '/usr/sbin/sogod'; //* hmm not used, maybe remove it
//    var $sogotoolbinary = '/usr/sbin/sogo-tool';
//    var $sogohomedir = '/var/lib/sogo'; //* hmm not used, maybe remove it
//    var $sogoconffile = '/var/lib/sogo/GNUstep/Defaults/.GNUstepDefaults';
//    var $templ_file = '/usr/local/ispconfig/server/conf/sogo.conf';
//    var $templ_domains_dir = '/usr/local/ispconfig/server/conf/sogo_domains';
//    var $templ_override_file = '/usr/local/ispconfig/server/conf-custom/sogo/sogo.conf';
//    var $templ_override_domains_dir = '/usr/local/ispconfig/server/conf-custom/sogo/domains';
//    var $mysql_server_host = '127.0.0.1:3306';
list($mysql_host, $mysql_port) = explode(':', $sogo_config_plugin->mysql_server_host);
unset($sogo_config_plugin);
/* ############# get old config settings from plugin ############# */


echo "\n\n" . str_repeat('-', 25) . "\n";
echo "This next step will delete all known files from update 9\n";
echo "If you have modified any of them or you are JUST A BIT unsure\n";
echo "about deleting them type 'n' for no to leave the files\n";
echo "leaving the old files will not affect this update\n";
echo "but it will leave you with a lot of useless files\n";
echo "Delete Old files (Y/N) [Y]: ";
if (strtolower(readInput('y')) == "y") {
    echo "\n\n";
    foreach ($old_files as $key => $value) {
        if ($value['type'] == 'file') {
            if (file_exists("{$ispchome}/{$value[$value['type']]}")) {
                $fuc = "action_{$value['action']}";
                $fuc("{$ispchome}/{$value[$value['type']] }", $value['type']);
            }
        } else if ($value['type'] == 'folder') {
            if (is_dir("{$ispchome}/{$value[$value['type']]}")) {
                $fuc = "action_{$value['action']}";
                $fuc("{$ispchome}/{$value[$value['type']]}", $value['type']);
            }
        } else {
            echo "Unkown TYPE: {$value['type']} => {$value['action']} => {$value[$value['type']]}\n";
        }
    }
}
echo "\n\n";
if (!TEST_RUN) {
    require __DIR__ . '/update.php';
} else {
    echo "Run script: " . __DIR__ . "/update.php\n\n";
}


if (!TEST_RUN) {
// create configs
    $interface_conf = <<< EOF
<?php
//* the folder were uploaded SOGo plugins will be saved (ISPC_WEB_TEMP_PATH . '/SOGoPlugins')
\$conf['sogo_plugins_upload_dir'] = ISPC_ROOT_PATH . '/web/temp/SOGoPlugins';

EOF;
    @file_put_contents($ispchome . '/interface/lib/config.inc.local.sogo-sample.php', $interface_conf);
} else {
    echo "Create config file: {$ispchome}/interface/lib/config.inc.local.sogo-sample.php\n\n";
}


if (!TEST_RUN) {
    $server_conf = <<< EOF
<?php

/*
 SOGo sudo command to use when executing a SOGo binary
 eg. 
 su -p -c '{command}' {$sogo_user}
 sudo -u {$sogo_user} {command}
 **** if you must quote the command ONLY USE ' (Single quote) NOT "(Double quote)
*/
\$conf['sogo_su_command'] = '{$sogo_su_cmd} {command}';
//* full path to sogo-tool binary 
\$conf['sogo_tool_binary'] = '{$sogo_tool_bin}';
//* name of the database used for SOGo
\$conf['sogo_database_name'] = '{$sogo_sogodb}';
//* name of the database user used for SOGo db
\$conf['sogo_database_user'] = '{$sogo_sogouser}';
//* name of the database user password used for SOGo db
\$conf['sogo_database_passwd'] = '{$sogo_sogopw}';
//* database host where SOGo db is hosted
\$conf['sogo_database_host'] = '{$mysql_host}';
//* database port number
\$conf['sogo_database_port'] = '{$mysql_port}';
//* vars added to the domain template
\$conf['sogo_domain_extra_vars'] = array(
    //* password algorithm default is crypt
    //* Possible algorithms are: plain, md5, crypt-md5, sha, ssha (including 256/512 variants),
    'userPasswordAlgorithm' => 'crypt',
    /*
      The default behaviour is to store newly set
      passwords with out the scheme (default: NO). 
      This can be overridden by setting to YES
      and will result in passwords stored as {scheme}encryptedPass
     */
    'prependPasswordScheme' => 'NO',
    //* human identification name of the address book
    'displayName' => 'Users in {domain}',
);
//* sogo default configuration file(s)
\$conf['sogo_gnu_step_defaults'] = '{$sogo_home_dir}/GNUstep/Defaults/.GNUstepDefaults';
\$conf['sogo_gnu_step_defaults_sogod.plist'] = '{$sogo_home_dir}/GNUstep/Defaults/sogod.plist';

//* template to use for table names in sogo db
\$conf['sogo_domain_table_tpl'] = "{domain}_users";

EOF;
    @file_put_contents("{$ispchome}/server/lib/config.inc.local.sogo-sample.php", $server_conf);
} else {
    echo "Create config file: {$ispchome}/server/lib/config.inc.local.sogo-sample.php\n\n";
}

$command = "mysql -h {$mysql_host} -u {$sogo_sogouser} -p\"{$sogo_sogopw}\"  -e \"DROP DATABASE {$sogo_sogodb}\"";
if (!TEST_RUN) {
    echo exec($command) . "\n";
} else {
    echo "Run command: {$command}\n\n";
}
$command = "mysql -h {$mysql_host} -u {$sogo_sogouser} -p\"{$sogo_sogopw}\"  -e \"CREATE DATABASE {$sogo_sogodb}\"";
if (!TEST_RUN) {
    echo exec($command) . "\n";
} else {
    echo "Run command: {$command}\n\n";
}

// create domains tables!!


$domains = sogod_read('domains');
$domains = implode("\n", $domains);
if (preg_match_all("#SOGoMailDomain = \"(.*)\"#i", $domains, $matches)) {
    foreach ($matches[1] as $key => $value) {
        if (!TEST_RUN) {
            __create_sogo_table($value);
        } else {
            echo "Create SOGo table for: {$value}\n\n";
        }
    }
}

function __create_sogo_table($domain_name) {
    global $db_host, $db_user, $db_password, $db_database, $mysql_host, $sogo_sogouser, $sogo_sogopw, $sogo_sogodb;
    $mysqli_ispconfig = new mysqli($db_host, $db_user, $db_password, $db_database);
    $can_continue = true;
    if ($emails_result = $mysqli_ispconfig->query("SELECT count(*) as cnt FROM `mail_user` WHERE `email` LIKE '%@{$domain_name}' AND `disableimap` = 'n'")) {
        $can_continue = false;
        while ($obj = $emails_result->fetch_object()) {
            if ($obj->cnt > 0) {
                $can_continue = true;
                break;
            }
        }
        $emails_result->close();
    } else {
        $mysqli_ispconfig->close();
        echo "Unable to get mail user for domain: {$domain_name}\n\n";
        return;
    }
    $mysqli_ispconfig->close();
    if (!$can_continue) {
        echo "No mail user for domain: {$domain_name}, not creating table\n";
        return;
    }
    $domain_name_table = str_replace(array('-', '.'), '_', $domain_name) . '_users';

    $sql = "
CREATE TABLE IF NOT EXISTS `{$domain_name_table}` (
  `c_uid` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_cn` text CHARACTER SET utf8 NOT NULL,
  `c_name` varchar(500) CHARACTER SET utf8 NOT NULL,
  `mail` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_imaplogin` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_sievehostname` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_imaphostname` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_domain` varchar(255) CHARACTER SET utf8 NOT NULL,
  `c_password` varchar(255) CHARACTER SET utf8 NOT NULL,
  KEY `c_uid` (`c_uid`(333))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

    $mysqli_sogo = new mysqli($mysql_host, $sogo_sogouser, $sogo_sogopw, $sogo_sogodb);
    $result = $mysqli_sogo->query($sql) ? TRUE : FALSE;
    echo "add SOGo table for domain: {$domain_name}" . (!$result ? "\n\tERROR\n\t{$mysqli_sogo->error}\t\n{$sql}" : "") . "\n\n";
    return $result;
}

// enable sogo module and plugin

if (!TEST_RUN) {
    if (!symlink($ispchome . '/server/plugins-available/sogo_plugin.inc.php', $ispchome . '/server/plugins-enabled/sogo_plugin.inc.php')) {
        echo str_repeat('=', 25) . "\n";
        echo "Unable to enable sogo_plugin\n";
        echo "Try!\n";
        echo "ln -s " . $ispchome . "/server/plugins-available/sogo_plugin.inc.php " . $ispchome . "/server/plugins-enabled/sogo_plugin.inc.php\n";
        echo str_repeat('=', 25) . "\n";
    }
} else {
    echo "Create symlink: {$ispchome}/server/plugins-available/sogo_plugin.inc.php => {$ispchome}/server/plugins-enabled/sogo_plugin.inc.php\n\n";
}
if (!TEST_RUN) {
    if (!symlink($ispchome . '/server/mods-available/sogo_module.inc.php', $ispchome . '/server/mods-enabled/sogo_module.inc.php')) {
        echo str_repeat('=', 25) . "\n";
        echo "Unable to enable sogo_module\n";
        echo "Try!\n";
        echo "ln -s " . $ispchome . "/server/mods-available/sogo_module.inc.php " . $ispchome . "/server/mods-enabled/sogo_module.inc.php\n";
        echo str_repeat('=', 25) . "\n";
    }
} else {
    echo "Create symlink: {$ispchome}/server/mods-available/sogo_module.inc.php => {$ispchome}/server/mods-enabled/sogo_module.inc.php\n\n";
}


echo "\n\n" . str_repeat('-', 25) . "\n";
echo "The following files need to be renamed into 'config.inc.local.php'\n";
echo "Interface Config:     {$ispchome}/interface/lib/config.inc.local.sogo-sample.php\n";
echo "Server Config:        {$ispchome}/server/lib/config.inc.local.sogo-sample.php\n";
echo str_repeat('-', 25) . "\n\n";
