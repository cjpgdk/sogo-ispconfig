<?php

$tform_def_file = "form/mail_sogo_domain.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';


//* Check permissions for module
$app->auth->check_module_permissions('mail');

// Loading classes
$app->uses('tpl,tform,tform_actions,functions');
$app->load('tform_actions');

class page_action extends tform_actions {

    public function onShowEdit() {
        global $app, $conf;

        //* START FROM parent::onShowEdit()
        if ($app->tform->errorMessage == '') {
            if ($app->tform->formDef['auth'] == 'yes' && $_SESSION["s"]["user"]["typ"] != 'admin') {
                $sql = "SELECT * FROM " . $app->tform->formDef['db_table'] . " WHERE " . $app->tform->formDef['db_table_idx'] . " = " . $this->id . " AND " . $app->tform->getAuthSQL('r');
            } else {
                $sql = "SELECT * FROM " . $app->tform->formDef['db_table'] . " WHERE " . $app->tform->formDef['db_table_idx'] . " = " . $this->id;
            }
            if (!$record = $app->db->queryOneRecord($sql))
                $app->error($app->lng('error_no_view_permission'));
        } else {
            // $record = $app->tform->encode($_POST,$this->active_tab);
            $record = $app->tform->encode($this->dataRecord, $this->active_tab, false);
        }

        $this->dataRecord = $record;
        //* /END FROM parent::onShowEdit()

        $server = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . @$app->functions->intval($this->dataRecord['server_id']));

        if (file_exists(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/{$this->dataRecord['domain']}.conf")) {
            //* default domain config if exists
            $domain_default = file_get_contents(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/{$this->dataRecord['domain']}.conf");
        } else if (file_exists(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/{$server['server_name']}.conf")) {
            //* NO default domain config, then default server config if exists
            $domain_default = file_get_contents(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/{$server['server_name']}.conf");
        } else {
            //* NO no nothing! hmm use default
            $domain_default = file_get_contents(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/domains_default.conf");
        }

        if (file_exists(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$this->dataRecord['domain']}.conf")) {
            //* custom domain config if exists
            $domain_custom = file_get_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$this->dataRecord['domain']}.conf");
        } else if (file_exists(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$server['server_name']}.conf")) {
            //* NO custom domain config, then custom server config if exists
            $domain_custom = file_get_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$server['server_name']}.conf");
        }

        if (isset($domain_custom)) {
            $xml = simplexml_load_string('<sogo_conf>' . $this->myTrim($domain_custom) . '</sogo_conf>');
            $strings = (array) $xml->dict->string;
            $c = -1;
            foreach ($xml->dict->key as $key => $value) {
                $c++;
                if (!isset($this->dataRecord["{$value}"])) {
                    if ($value != 'SOGoSuperUsernames' && $value != 'SOGoUserSources') {
                        $this->dataRecord["{$value}"] = (string) $strings[$c];
                    } else if ($value == 'SOGoSuperUsernames') {
                        foreach ($xml->dict->array[0]->string as $su) {
                            $this->dataRecord["{$value}"] .= (string) "{$su}|";
                        }
                        $this->dataRecord["{$value}"] = rtrim($this->dataRecord["{$value}"], '|');
                    }
                }
            }
        }
        if (isset($domain_default)) {
            $xml = simplexml_load_string('<sogo_conf>' . $this->myTrim($domain_default) . '</sogo_conf>');
            $strings = (array) $xml->dict->string;
            $c = -1;
            foreach ($xml->dict->key as $key => $value) {
                $c++;
                if (!isset($this->dataRecord["{$value}"])) {
                    if ($value != 'SOGoSuperUsernames' && $value != 'SOGoUserSources') {
                        $this->dataRecord["{$value}"] = (string) $strings[$c];
                    } else if ($value == 'SOGoSuperUsernames') {
                        foreach ($xml->dict->array[0]->string as $su) {
                            $this->dataRecord["{$value}"] .= (string) "{$su}|";
                        }
                        $this->dataRecord["{$value}"] = rtrim($this->dataRecord["{$value}"], '|');
                    }
                }
            }
        }
        $record = $app->tform->getHTML($this->dataRecord, $this->active_tab, 'EDIT');
        $record['id'] = $this->id;
        $app->tpl->setVar($record);
    }

    function myTrim($str) {
        $search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
        $replace = array('>', '<', '');
        $str = preg_replace($search, $replace, $str);
        return $str;
    }

    public function onUpdate() {
        global $app, $conf;
        if (!empty($_REQUEST['SOGoDraftsFolderName']) &&
                !empty($_REQUEST['SOGoSentFolderName']) &&
                !empty($_REQUEST['SOGoTrashFolderName']) &&
                !empty($_REQUEST['SOGoMailShowSubscribedFoldersOnly']) &&
                !empty($_REQUEST['SOGoLanguage']) &&
                !empty($_REQUEST['SOGoSuperUsernames'])) {
            if ($_REQUEST['SOGoMailShowSubscribedFoldersOnly'] != 'NO' && $_REQUEST['SOGoMailShowSubscribedFoldersOnly'] != 'YES') {
                $SOGoMailShowSubscribedFoldersOnly = 'NO';
            } else {
                $SOGoMailShowSubscribedFoldersOnly = $_REQUEST['SOGoMailShowSubscribedFoldersOnly'];
            }
            $su_names = "";
            if (is_array($_REQUEST['SOGoSuperUsernames'])) {
                foreach ($_REQUEST['SOGoSuperUsernames'] as $key => $value) {
                    $su_names .= "<string>{$value}</string>";
                }
            } else {
                $su_names = "<string>{{DOMAINADMIN}}</string>";
            }
            $sogo_conf = <<< EOF
                <key>{{DOMAIN}}</key>
                <dict>
                    <key>SOGoDraftsFolderName</key>
                    <string>{$_REQUEST['SOGoDraftsFolderName']}</string>
                    <key>SOGoSentFolderName</key>
                    <string>{$_REQUEST['SOGoSentFolderName']}</string>
                    <key>SOGoTrashFolderName</key>
                    <string>{$_REQUEST['SOGoTrashFolderName']}</string>
                    <key>SOGoMailShowSubscribedFoldersOnly</key>
                    <string>{$SOGoMailShowSubscribedFoldersOnly}</string>
                    <key>SOGoLanguage</key>
                    <string>{$_REQUEST['SOGoLanguage']}</string>
                    <key>SOGoMailDomain</key>
                    <string>{{DOMAIN}}</string>
                    <key>SOGoSuperUsernames</key>
                    <array>
                        {$su_names}
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
                            <key>id</key>
                            <string>{{SOGOUNIQID}}</string>
                            <key>viewURL</key>
                            <string>{{CONNECTIONVIEWURL}}</string>
                        </dict>
                    </array>
                </dict>
EOF;

            $domain = $app->db->queryOneRecord('SELECT `domain` FROM `mail_domain` WHERE `domain_id`=' . @$app->functions->intval($_REQUEST["id"]));
            
            //* wee only save to conf-custom, on the safe side make sure the dirs are there.!
            if (!is_dir(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/")) {
                if (!is_dir(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/")) {
                    if (!is_dir(ISPC_ROOT_PATH . "/../server/conf-custom/")) {
                        mkdir(ISPC_ROOT_PATH . "/../server/conf-custom/");
                    }
                    mkdir(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/");
                }
                mkdir(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/");
            }
            //* save it.!
            file_put_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$domain['domain']}.conf", $sogo_conf);
            //* lets create a fake update to make the chages afectiv (DO NOTE WE SET THE DOMAIN TO SAME VALUE!)
            $app->db->datalogUpdate('mail_domain', "domain='{$domain['domain']}'", 'domain_id', @$app->functions->intval($_REQUEST["id"]), true);
        }
        header("Location: " . $app->tform->formDef['list_default']);
    }

}

$page = new page_action();
$page->onLoad();
?>
