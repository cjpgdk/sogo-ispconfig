<?php

/*
 * HOW TO..
 * COPY this file into ISPC-SOGO-Update8_to_9.php
 * and exectute it.
 * php ISPC-SOGO-Update8_to_9.php
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

$_plugin_www_location = "http://cmjscripter.net/files/scripts/ispc/ISPC-SOGO-Plugin.u9.txt";
$_module_www_location = "http://cmjscripter.net/files/scripts/ispc/ISPC-SOGO-Module.txt";
$_interface_www_location = "http://cmjscripter.net/files/scripts/ispc/interface_simple.u1.zip";

if (!isset($argv[1]))
    echo "Where is ISPConfig installed: [/usr/local/ispconfig]: ";
else
    echo "ISPConfig install path: [{$argv[1]}]: ";
echo PHP_EOL;
$ISPCinstallPath = _readinput((isset($argv[1]) ? $argv[1] : "/usr/local/ispconfig"));


if (!is_dir($ISPCinstallPath)) {
    echo "{$ISPCinstallPath} is not a valid path" . PHP_EOL;
    exit;
} else {
    if (!is_dir($ISPCinstallPath . DIRECTORY_SEPARATOR . "interface") || !is_dir($ISPCinstallPath . DIRECTORY_SEPARATOR . "server")) {
        echo "Unable to locate interface OR server directory in {$ISPCinstallPath}" . PHP_EOL;
        exit;
    }
}

if (file_exists("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.php")) {
    require_once "{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.php";
}

if (file_exists("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php")) {
    require_once "{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php";
}
if (!class_exists('sogo_config_plugin')) {
    echo "i'm unable to load the sogo_config_plugin php class " . PHP_EOL . " - {$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.php" . PHP_EOL;
    exit;
}
$sogo_config_plugin = new sogo_config_plugin();

// pluging
$NEW_SOGO_PLUGIN = file_get_contents($_plugin_www_location);
$NEW_SOGO_PLUGIN = str_replace('{SOGOUSERPW}', $sogo_config_plugin->sogopw, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOUSERN}', $sogo_config_plugin->sogouser, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGODB}', $sogo_config_plugin->sogodb, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{MYSQLHOST}:{MYSQLPORT}', $sogo_config_plugin->mysql_server_host, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{ISPCONFIGDB}', $sogo_config_plugin->ispcdb, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOBINARY}', $sogo_config_plugin->sogobinary, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOTOOLBINARY}', $sogo_config_plugin->sogotoolbinary, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOHOMEDIR}', $sogo_config_plugin->sogohomedir, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOGNUSTEPCONFFILE}', $sogo_config_plugin->sogoconffile, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{ISPCONFIGINSTALLPATH}', $ISPCinstallPath, $NEW_SOGO_PLUGIN);
$NEW_SOGO_PLUGIN = str_replace('{SOGOSYSTEMSUDO}', $sogo_config_plugin->sogo_su_cmd, $NEW_SOGO_PLUGIN);
$stump = explode(' ', $sogo_config_plugin->sogo_su_cmd);
$NEW_SOGO_PLUGIN = str_replace('{SOGOSYSTEMUSER}', $stump[count($stump)-1], $NEW_SOGO_PLUGIN);
unset($stump);

echo "With this update you can enable upto 4 mail aliases in SOGo" . PHP_EOL;
echo "if your system runs on SLOW cpu or a small virtual host i don't recommend using this feature" . PHP_EOL;
echo PHP_EOL . "you like to enable mail alias in sogo? (y/n) [y]:";
$disablemailaslias = _readinput('y');
if (strtolower($disablemailaslias) == 'n') {
    $NEW_SOGO_PLUGIN = str_replace('var $allow_mail_alias = true;', 'var $allow_mail_alias = false;', $NEW_SOGO_PLUGIN);
}


echo "Saving updated plugin: {$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php" . PHP_EOL;
@unlink("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.php");
@unlink("{$ISPCinstallPath}/server/plugins-enabled/sogo_config_plugin.inc.php");
file_put_contents("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php", $NEW_SOGO_PLUGIN);
if (!symlink("{$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php", "{$ISPCinstallPath}/server/plugins-enabled/sogo_config_plugin.inc.php")) {
    echo PHP_EOL . str_repeat('=', 10) . PHP_EOL . "I can't activate the plugin, exec the following command.!" . PHP_EOL . "ln -s {$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php {$ISPCinstallPath}/server/plugins-enabled/sogo_config_plugin.inc.php" . PHP_EOL . str_repeat('=', 10) . PHP_EOL;
}
unset($NEW_SOGO_PLUGIN);
if (file_exists("{$ISPCinstallPath}/server/plugins-enabled/sogo_config_plugin.inc.php"))
    exec("chown ispconfig:ispconfig {$ISPCinstallPath}/server/plugins-available/sogo_config_plugin.inc.php");
if (file_exists("{$ISPCinstallPath}/server/plugins-enabled/sogo_config_plugin.inc.php"))
    exec("chown ispconfig:ispconfig {$ISPCinstallPath}/server/plugins-enabled/sogo_config_plugin.inc.php");

// mod
$NEW_SOGO_MODULE = file_get_contents($_module_www_location);
@unlink("{$ISPCinstallPath}/server/mods-available/sogo_module.inc.php");
@unlink("{$ISPCinstallPath}/server/mods-enabled/sogo_module.inc.php");
file_put_contents("{$ISPCinstallPath}/server/mods-available/sogo_module.inc.php", $NEW_SOGO_MODULE);
if (!symlink("{$ISPCinstallPath}/server/mods-available/sogo_module.inc.php", "{$ISPCinstallPath}/server/mods-enabled/sogo_module.inc.php")) {
    echo PHP_EOL . str_repeat('=', 10) . PHP_EOL . "I can't activate the plugin, exec the following command.!" . PHP_EOL . "ln -s {$ISPCinstallPath}/server/mods-available/sogo_module.inc.php {$ISPCinstallPath}/server/mods-enabled/sogo_module.inc.php" . PHP_EOL . str_repeat('=', 10) . PHP_EOL;
}
unset($NEW_SOGO_MODULE);
if (file_exists("{$ISPCinstallPath}/server/mods-available/sogo_module.inc.php"))
    exec("chown ispconfig:ispconfig {$ISPCinstallPath}/server/mods-available/sogo_module.inc.php");
if (file_exists("{$ISPCinstallPath}/server/mods-enabled/sogo_module.inc.php"))
    exec("chown ispconfig:ispconfig {$ISPCinstallPath}/server/mods-enabled/sogo_module.inc.php");

echo "Update the interface addon, refer to the file list in {$_interface_www_location}" . PHP_EOL;
echo "If you installed the addons from previus versions you need to update it" . PHP_EOL;
echo "the old interface addon is not compatiple with this update" . PHP_EOL;
echo "one file will be overwridden (interface/js/jquery-ui-1.8.16.custom.min.js) added sortable and mouse." . PHP_EOL;
echo "you like to install it? (y/n):";
$ins_interface = _readinput('Y');
if (strtolower($ins_interface) == 'y') {
    echo "Thank you :)" . PHP_EOL;
    echo exec("wget {$_interface_www_location} -O /tmp/interface_simple.zip") . PHP_EOL;
    echo exec("unzip -u -o /tmp/interface_simple.zip -d /tmp/") . PHP_EOL;
    echo exec("rm -fr /tmp/interface_simple/server") . PHP_EOL;
    echo exec("rm -fr /tmp/interface_simple/change.log") . PHP_EOL;
    echo PHP_EOL . "Are your using ISPConfig 3.0.4x (y/n) [n]:";
    $usingispconfig304 = _readinput('n');
    if (strtolower($usingispconfig304) == 'y') {
        echo PHP_EOL . "The old interface addon were not working propper on this version" . PHP_EOL;
        echo "Did you fix that your self? or shall i fix that for you (y/n) [n]:";
        $usingispconfigfix = _readinput('n');
        if (strtolower($usingispconfigfix) == 'y') {
            echo PHP_EOL . "Your ISPConfig version is?" . PHP_EOL;
            echo "(304|3041|3042|3043|3044|3045|3046) [304]: ";
            $ispcver = _readinput('304');
            switch ($ispcver) {
                case "304":
                case "3041":
                    echo exec("patch {$ISPCinstallPath}/interface/web/admin/lib/module.conf.php < /tmp/interface_simple/patch-ispc304.admin.diff") . PHP_EOL;
                    echo exec("chown ispconfig:ispconfig {$ISPCinstallPath}/interface/web/admin/lib/module.conf.php") . PHP_EOL;

                    echo exec("patch {$ISPCinstallPath}/interface/web/mail/lib/module.conf.php < /tmp/interface_simple/patch-ispc304.mail.diff") . PHP_EOL;
                    echo exec("chown ispconfig:ispconfig {$ISPCinstallPath}/interface/web/mail/lib/module.conf.php") . PHP_EOL;
                    break;
                case "3042":
                case "3043":
                case "3044":
                case "3045":
                case "3046":
                    echo exec("patch {$ISPCinstallPath}/interface/web/mail/lib/module.conf.php < /tmp/interface_simple/patch-ispc304.mail.diff") . PHP_EOL;
                    echo exec("chown ispconfig:ispconfig {$ISPCinstallPath}/interface/web/mail/lib/module.conf.php") . PHP_EOL;

                    echo exec("patch {$ISPCinstallPath}/interface/web/admin/lib/module.conf.php < /tmp/interface_simple/patch-ispc3042.admin.diff") . PHP_EOL;
                    echo exec("chown ispconfig:ispconfig {$ISPCinstallPath}/interface/web/admin/lib/module.conf.php") . PHP_EOL;
                    break;
            }
        }
    }
    echo exec("cp -fr /tmp/interface_simple/interface/* {$ISPCinstallPath}/interface/") . PHP_EOL;
    echo exec("chown ispconfig:ispconfig -R {$ISPCinstallPath}/interface/") . PHP_EOL;
    echo exec("chmod 775 {$ISPCinstallPath}/server/conf-custom/sogo/") . PHP_EOL;
    echo exec("chmod 775 {$ISPCinstallPath}/server/conf-custom/sogo/domains/") . PHP_EOL;
}


echo "Rebuilding SOGo Views in database.." . PHP_EOL;
_rebuildSOGoDBViews($sogo_config_plugin->mysql_server_host, $sogo_config_plugin->sogouser, $sogo_config_plugin->sogopw, $sogo_config_plugin->sogodb, $sogo_config_plugin->ispcdb, ($disablemailaslias == 'n' ? FALSE : TRUE));

PlistParser::loadSOGoConfigFile("{$ISPCinstallPath}/server/conf/sogo.conf");
$array = PlistParser::parse();

//* sogod.plist
$sogodplist = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . PHP_EOL;
$sogodplist .= "<!DOCTYPE plist PUBLIC \"-//GNUstep//DTD plist 0.9//EN\" \"http://www.gnustep.org/plist-0_9.xml\">" . PHP_EOL;
$sogodplist .= "<plist version=\"0.9\">" . PHP_EOL;
$sogodplist .= "\t<key>sogod</key>" . PHP_EOL;
$sogodplist .= "\t<dict>" . PHP_EOL;

foreach ($array['sogod'] as $key => $value) {
    //* we do not write empty values!
    if (!empty($value) && is_string($value)) {
        //* sogod.plist
        $sogodplist .= "\t\t<key>{$key}</key>" . PHP_EOL;
        $sogodplist .= "\t\t<string>{$value}</string>" . PHP_EOL;
    } else if (!empty($value) && is_array($value)) {
        //* sogod.plist
        $sogodplist .= "\t\t<key>{$key}</key>" . PHP_EOL;
        $sogodplist .= "\t\t<array>" . PHP_EOL;
        foreach ($value as $k => $v) {
            //* sogod.plist
            $sogodplist .= "\t\t\t<string>{$v}</string>" . PHP_EOL;
        }
        //* sogod.plist
        $sogodplist .= "\t\t</array>" . PHP_EOL;
    }
}
//* sogod.plist
$sogodplist .= "\t\t<key>domains</key>" . PHP_EOL;
$sogodplist .= "\t\t<dict>{{SOGODOMAINSCONF}}</dict>" . PHP_EOL;
$sogodplist .= "\t</dict>" . PHP_EOL;
$sogodplist .= "</plist>" . PHP_EOL;
file_put_contents("{$ISPCinstallPath}/server/conf/sogo-sogod.plist.conf", $sogodplist);
echo exec("chown ispconfig:ispconfig -R {$ISPCinstallPath}/server/conf/sogo-sogod.plist.conf");
echo exec("chmod 775 {$ISPCinstallPath}/server/conf/sogo-sogod.plist.conf");

if (($disablemailaslias == 'n' ? FALSE : TRUE)) {
    echo PHP_EOL . "You enabled the mail alias in SOGo" . PHP_EOL;
    echo "this means i need to modify your default domain template file" . PHP_EOL;
    echo "if you modifyed this file by hand you need to mannualy add '{{MAILALIAS}}' to that file" . PHP_EOL;
    echo "May i add that for you (y/n) [n]:";
    $usingispconfigfix = _readinput('n');
    $nFileDomDef = "
                <key>{{DOMAIN}}</key>
                <dict>
                    <key>SOGoDraftsFolderName</key>
                    <string>Inbox.Drafts</string>
                    <key>SOGoSentFolderName</key>
                    <string>Inbox.Sent</string>
                    <key>SOGoTrashFolderName</key>
                    <string>Inbox.Trash</string>
                    <key>SOGoMailShowSubscribedFoldersOnly</key>
                    <string>NO</string>
                    <key>SOGoLanguage</key>
                    <string>English</string>
                    <key>SOGoMailDomain</key>
                    <string>{{DOMAIN}}</string>
                    <key>SOGoSuperUsernames</key>
                    <array>
                        <string>{{DOMAINADMIN}}</string>
                    </array>
                    <key>SOGoUserSources</key>
                    <array>
                        <dict>
                            <key>userPasswordAlgorithm</key>
                            <string>crypt</string>
                            <key>prependPasswordScheme</key>
                            <string>NO</string>
                            <key>LoginFieldNames</key>
                            <array>
                                <string>c_uid</string>
                                <string>mail</string>
                            </array>
                            <key>IMAPHostFieldName</key>
                            <string>imap_host</string>
                            <key>IMAPLoginFieldName</key>
                            <string>c_uid</string>
                            <key>type</key>
                            <string>sql</string>
                            <key>isAddressBook</key>
                            <string>NO</string>
                            <key>canAuthenticate</key>
                            <string>YES</string>
                            <key>displayName</key>
                            <string>Users in {{DOMAIN}}</string>
                            <key>hostname</key>
                            <string>localhost</string>
{{MAILALIAS}}
                            <key>id</key>
                            <string>{{SOGOUNIQID}}</string>
                            <key>viewURL</key>
                            <string>{{CONNECTIONVIEWURL}}</string>
                        </dict>
                    </array>
                </dict>
";
    if (strtolower($usingispconfigfix) == 'y') {
        file_put_contents("{$ISPCinstallPath}/server/conf/sogo_domains/domains_default.conf", $nFileDomDef);
        echo exec("chown ispconfig:ispconfig {$ISPCinstallPath}/server/conf/sogo_domains/domains_default.conf") . PHP_EOL;
        echo exec("chmod 775 {$ISPCinstallPath}/server/conf/sogo_domains/domains_default.conf") . PHP_EOL;
    } else {
        echo PHP_EOL . "You mave have selected not to let me modify your domains_default.conf file" . PHP_EOL;
        echo "so i have saved the new default file here:" . PHP_EOL;
        echo "{$ISPCinstallPath}/server/conf/sogo_domains/domains_default.conf.new";
        file_put_contents("{$ISPCinstallPath}/server/conf/sogo_domains/domains_default.conf.new", $nFileDomDef);
    }
    echo PHP_EOL . "You mave to update your domains configurations if you" . PHP_EOL;
    echo "got any custom configurations for domains" . PHP_EOL;
}

echo PHP_EOL . "Thank you very much for using my script to configure SOGo with ISPConfig" . PHP_EOL;

class PlistParser {

    static $_DOMDocument = null;

    /**
     * load the SOGo config xml from file
     * @param string $file
     * @return boolean TRUE on success or FALSE on failure.
     */
    public static function loadSOGoConfigFile($file) {
        self::$_DOMDocument = new DOMDocument();
        return self::$_DOMDocument->load($file);
    }

    /**
     * parse a plist DOMDocument object into an array
     * @param DOMDocument $document
     * @return array
     */
    public static function parse($document = NULL) {
        if ($document == NULL) {
            $document = self::$_DOMDocument;
        }
        $node = $document->documentElement;
        $root = $node->firstChild;
        while ($root->nodeName == "#text")
            $root = $root->nextSibling;
        return self::_parse_node($root);
    }

    /**
     * pase a plist DOMNode
     * @param DOMNode $node
     * @return type
     */
    private static function _parse_node($node) {
        $type = strtolower($node->nodeName);
        $method = '_parse_' . strtolower($type);
        if (method_exists('PlistParser', $method)) {
            return self::$method($node);
        }
    }

    /**
     * parse plist array
     * @param DOMNode $node
     * @return array
     */
    private static function _parse_array($node) {
        $array = array();
        for ($node = $node->firstChild; $node != null; $node = $node->nextSibling) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $array[] = self::_parse_node($node);
            }
        }
        return $array;
    }

    /**
     * parse plist dict
     * @param DOMNode $node
     * @return array
     */
    private static function _parse_dict($node) {
        $dict = array();
        for ($node = $node->firstChild; $node != null; $node = $node->nextSibling) {
            if ($node->nodeName == "key") {
                $key = $node->textContent;
                $node2 = $node->nextSibling;
                while ($node2->nodeType == XML_TEXT_NODE)
                    $node2 = $node2->nextSibling;
                $value = self::_parse_node($node2);
                $dict[$key] = $value;
            }
        }
        return $dict;
    }

    /**
     * parse plist string
     * @param DOMNode $node
     * @return string
     */
    private static function _parse_string($node) {
        return $node->textContent;
    }

}

/**
 * function to rebuild the sogo views.
 * @param string $sqlhost
 * @param string $sqluser
 * @param string $sqlpw
 * @param string $sogodb
 * @param string $ispcdb
 * @param boolean $usealias
 */
function _rebuildSOGoDBViews($sqlhost, $sqluser, $sqlpw, $sogodb, $ispcdb, $usealias = FALSE) {
    $sqlhost = explode(':', $sqlhost);
    $mysqli = new mysqli($sqlhost[0], $sqluser, $sqlpw, $sogodb, $sqlhost[1]);

    //* drop all views for sogo
    $sogo_views = $mysqli->query("SELECT `TABLE_NAME` FROM `information_schema`.`VIEWS` WHERE `TABLE_SCHEMA`='{$sogodb}' AND `TABLE_NAME` LIKE 'sogo_users_%'");
    while ($obj = $sogo_views->fetch_object()) {
        if (preg_match("/sogo_users_/i", $obj->TABLE_NAME)) {
            $mysqli->query("DROP VIEW `{$sogodb}`.`{$obj->TABLE_NAME}`");
        }
    }
    unset($obj, $sogo_views);

    //* build new views for all domains exept alias domains
    $mail_domains = $mysqli->query("SELECT `domain` FROM `{$ispcdb}`.`mail_domain` WHERE `active`='y' AND CONCAT('@',mail_domain.domain) NOT IN (SELECT mail_forwarding.source FROM `{$ispcdb}`.mail_forwarding WHERE mail_forwarding.active='y' AND mail_forwarding.type='aliasdomain')");
    while ($obj = $mail_domains->fetch_object()) {
        $domv = str_replace('-', '_', str_replace('.', '_', $obj->domain));

        if ($usealias) {

            $mysqli->query("CREATE VIEW sogo_users_{$domv} AS SELECT DISTINCT
    `login` AS c_uid,
    `login` AS c_name,
    `password` AS c_password,
    `name` AS c_cn,
    `email` AS mail,
    (SELECT `server_name` FROM {$ispcdb}.`server`, {$ispcdb}.`mail_user` WHERE `mail_user`.`server_id`=`server`.`server_id` AND `server`.`mail_server`=1 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1) AS imap_host,
    (SELECT `mail_forwarding`.`source` FROM {$ispcdb}.`mail_forwarding`, {$ispcdb}.`mail_user` WHERE `mail_user`.`login`=`mail_forwarding`.`destination` AND `mail_forwarding`.`type`='alias' AND `mail_forwarding`.`active`='y' AND ispcmu.`login`=`mail_user`.`login` LIMIT 1)  AS mail_1,
    (SELECT `mail_forwarding`.`source` FROM {$ispcdb}.`mail_forwarding`, {$ispcdb}.`mail_user` WHERE `mail_user`.`login`=`mail_forwarding`.`destination` AND `mail_forwarding`.`type`='alias' AND `mail_forwarding`.`active`='y' AND `mail_forwarding`.`source` NOT LIKE mail_1 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1)  AS mail_2,
    (SELECT `mail_forwarding`.`source` FROM {$ispcdb}.`mail_forwarding`, {$ispcdb}.`mail_user` WHERE `mail_user`.`login`=`mail_forwarding`.`destination` AND `mail_forwarding`.`type`='alias' AND `mail_forwarding`.`active`='y' AND `mail_forwarding`.`source` NOT LIKE mail_1 AND `mail_forwarding`.`source` NOT LIKE mail_2 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1)  AS mail_3,
    (SELECT `mail_forwarding`.`source` FROM {$ispcdb}.`mail_forwarding`, {$ispcdb}.`mail_user`  WHERE `mail_user`.`login`=`mail_forwarding`.`destination` AND `mail_forwarding`.`type`='alias' AND `mail_forwarding`.`active`='y' AND `mail_forwarding`.`source` NOT LIKE mail_1 AND `mail_forwarding`.`source` NOT LIKE mail_2 AND `mail_forwarding`.`source` NOT LIKE mail_3 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1)  AS mail_4
FROM {$ispcdb}.`mail_forwarding`, {$ispcdb}.`mail_user` AS ispcmu 
 WHERE `email` LIKE '%@{$obj->domain}' AND disableimap='n'");
        } else {

            $mysqli->query('CREATE VIEW `' . $sogodb . '`.`sogo_users_' . $domv . '` AS SELECT
	`login` AS c_uid,
	`login` AS c_name,
	`password` AS c_password,
	`name` AS c_cn,
	`email` AS mail,
	(SELECT `server_name` FROM ' . $ispcdb . '.`server`, ' . $ispcdb . '.`mail_user` WHERE `mail_user`.`server_id`=`server`.`server_id` AND `server`.`mail_server`=1 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1) AS imap_host 
        FROM ' . $ispcdb . '.`mail_user` AS ispcmu  WHERE `email` LIKE \'%@' . $obj->domain . '\' AND disableimap=\'n\'');
        }
    }
}

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
