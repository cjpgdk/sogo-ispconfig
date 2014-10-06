<?php

$files_copy = array(
    'interface' => array(
        'lib/classes/sogo_helper.inc.php',
        //* #ADMIN#
        //* admin
        'web/admin/sogo_conifg_edit.php',
        'web/admin/sogo_conifg_list.php',
        'web/admin/sogo_domains_del.php',
        'web/admin/sogo_domains_edit.php',
        'web/admin/sogo_domains_list.php',
        //* form
        'web/admin/form/sogo_config.tform.php',
        'web/admin/form/sogo_domains.tform.php',
        //* lng
        'web/admin/lib/lang/en_sogo_config.lng',
        'web/admin/lib/lang/en_sogo_conifg_list.lng',
        'web/admin/lib/lang/en_sogo_domains.lng',
        'web/admin/lib/lang/en_sogo_domains_list.lng',
        //* menu
        'web/admin/lib/menu.d/sogo.menu.php',
        //* list
        'web/admin/list/sogo_domains.list.php',
        'web/admin/list/sogo_server.list.php',
        //* templates
        'web/admin/templates/sogo_config_custom_edit.html',
        'web/admin/templates/sogo_config_domain_edit.html',
        'web/admin/templates/sogo_config_edit.html',
        'web/admin/templates/sogo_config_user_edit.html',
        'web/admin/templates/sogo_conifg_list.html',
        'web/admin/templates/sogo_domains_custom_edit.html',
        'web/admin/templates/sogo_domains_domain_edit.html',
        'web/admin/templates/sogo_domains_list.html',
        'web/admin/templates/sogo_domains_user_edit.html',
    //* /#ADMIN#
    //* #MAIL#
    //* currently empty
    //* /#MAIL#
    ),
    'server' => array(
        'conf/sogo_domain.master',
        'lib/config.inc.local.sogo-sample.php',
        'lib/classes/sogo_config.inc.php',
        'lib/classes/sogo_helper.inc.php',
        'mods-available/sogo_module.inc.php',
        'plugins-available/sogo_plugin.inc.php',
    ),
);
$mysql_tables_ispc = "_ins/tables.sql";
$failed = FALSE;
$srv_enable = TRUE;

//* see if we have a init scriupt for sogo
if (file_exists("/etc/init.d/sogo")) $sogo_init_script = "/etc/init.d/sogo";
else if (file_exists("/etc/init.d/sogo")) $sogo_init_script = "/etc/init.d/sogod";
else $sogo_init_script = "";

//* locate sogo-tool binary
$sogo_tool_binary = exec("which sogo-tool");
//* get sogo home dir
$sogo_home_dir = exec("getent passwd sogo | cut -d: -f6");
//* SOGo init script ?
if (empty($sogo_init_script)) {
    echo "[FAIL]: Unable to locate SOGo init script" . PHP_EOL;
    $failed = TRUE;
}
//* sogo-tool binary?
if (empty($sogo_tool_binary)) {
    echo "[FAIL]: Unable to locate sogo-tool" . PHP_EOL;
    $failed = TRUE;
}
//* SOGo home dir ?
if (empty($sogo_home_dir)) {
    echo "[FAIL]: Unable to locate sogo home dir" . PHP_EOL;
    $failed = TRUE;
}
//* any errors!?
if ($failed) {
    echo PHP_EOL;
    echo "One or more attempts to locate some sins of sogo failed" . PHP_EOL;
    echo "Continue (Y/N) [N]: ";
    if (strtolower(_readinput("n")) == "n") {
        echo PHP_EOL;
        echo "okay goodbye" . PHP_EOL;
        exit();
    } else {
        echo PHP_EOL;
        echo "i will now copy files need to configure SOGo" . PHP_EOL;
        echo "from ISPConfig interface." . PHP_EOL;
        echo "but i can't safely enable the module and plugin needed without sins of SOGo binary and home dir" . PHP_EOL;
        $srv_enable = FALSE;
    }
    echo PHP_EOL;
    $failed = FALSE; //* reset
}
//* locate ISP Config home dir
echo "ISPConfig folder? [/usr/local/ispconfig]: ";
$ispcdir = _readinput("/usr/local/ispconfig");
echo PHP_EOL;
//* copy files for interface && server
$missing_error = "";
foreach ($files_copy as $dir => $files) {
    if (is_dir($dir)) {
        foreach ($files as $file) {
            if (file_exists($dir . '/' . $file)) {
                copy($dir . '/' . $file, $ispcdir . '/' . $dir . '/' . $file);
            } else {
                $missing_error .= "File [{$dir}/{$file}] not found in plugin folder" . PHP_EOL;
                $failed = TRUE;
            }
        }
    } else {
        $missing_error .= "Folder [{$dir}] not found in plugin folder" . PHP_EOL;
    }
}
//* errors?
if (!empty($missing_error)) {
    echo str_repeat('=', 8) . PHP_EOL . $missing_error . str_repeat('=', 8) . PHP_EOL;
}

if (file_exists($mysql_tables_ispc)) {
//* add mysql tables
    echo "MySQL Host? [127.0.0.1]: ";
    $mysql_host = _readinput("127.0.0.1");
    echo PHP_EOL;
    echo "MySQL admin user? [root]: ";
    $mysql_admin = _readinput("root");
    echo PHP_EOL;
    echo "MySQL password? []: ";
    $mysql_password = _readinput("");
    echo PHP_EOL;
    echo "ISPConfig database? [dbispconfig]: ";
    $mysql_database = _readinput("dbispconfig");
    echo PHP_EOL;
    $command = "mysql -h {$mysql_host} -u {$mysql_admin} -p{$mysql_password} {$mysql_database} < {$mysql_tables_ispc}";
    echo exec($command). PHP_EOL;
}  else {
    echo "[FAIL]: Unable to locate mysql tables file (interface, plugin and module WILL NOT WORK without them)" . PHP_EOL;
    echo "Redownload the tables and import them manualy before using" . PHP_EOL;
    $failed = TRUE;
}

//$mysql_link = new mysqli($mysql_host, $mysql_admin, $mysql_password, $mysql_database);
//$mysql_link->
//* can safely enable module and plugin?
if ($srv_enable && !$failed) {
    echo "Enable SOGo Module and Plugin?" . PHP_EOL;
    echo "\t - Only do this if SOGo is install on this server" . PHP_EOL;
    echo "? (Y/N) [Y]: ";
    if (strtolower(_readinput("y")) == "y") {
        if (!is_link($ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
            if (!symlink($ispcdir . '/server/plugins-available/sogo_plugin.inc.php', $ispcdir . '/server/plugins-enabled/sogo_plugin.inc.php')) {
                echo str_repeat('=', 8) . PHP_EOL;
                echo "Unable to enable sogo_plugin" . PHP_EOL;
                echo "Try!" . PHP_EOL;
                echo "ln -s {$ispcdir}/server/plugins-available/sogo_plugin.inc.php {$ispcdir}/server/plugins-enabled/sogo_plugin.inc.php" . PHP_EOL;
                echo str_repeat('=', 8) . PHP_EOL;
            }
        }
        if (!is_link($ispcdir . '/server/mods-enabled/sogo_module.inc.php')) {
            if (!symlink($ispcdir . '/server/mods-available/sogo_module.inc.php', $ispcdir . '/server/mods-enabled/sogo_module.inc.php')) {
                echo str_repeat('=', 8) . PHP_EOL;
                echo "Unable to enable sogo_module" . PHP_EOL;
                echo "Try!" . PHP_EOL;
                echo "ln -s {$ispcdir}/server/mods-available/sogo_module.inc.php {$ispcdir}/server/mods-enabled/sogo_module.inc.php" . PHP_EOL;
                echo str_repeat('=', 8) . PHP_EOL;
            }
        }
    }
    echo PHP_EOL;
}
if ($failed) {
    echo "Erros doing the install i can't safely enable the module and plugin needed" . PHP_EOL;
    $failed = FALSE; //* reset
}

echo "All done assuming no errors and all went well" . PHP_EOL;
echo "you will need to add SOGo config values to server config file:" . PHP_EOL;
echo "{$ispcdir}/server/lib/config.inc.local.php" . PHP_EOL;
echo "A sample file can be found here.!" . PHP_EOL;
echo "{$ispcdir}/server/lib/config.inc.local.sogo-sample.php" . PHP_EOL . PHP_EOL;

echo "AND DON'T forget to create a database SOGo can use for storage" . PHP_EOL;
/**
 * Read input from stdin
 * @param string $default
 * @return string
 */
function _readinput($default = "") {
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    return (!empty($line) && trim($line) != "" ? trim($line) : $default);
}
