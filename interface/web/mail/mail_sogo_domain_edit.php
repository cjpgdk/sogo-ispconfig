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
        $app->uses('sogo_config');

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

        $domain_conf = $this->getConfig($this->dataRecord['server_id'], $this->dataRecord['domain']);

        if (isset($domain_conf['custom'])) {
            $sogo_config01 = new sogo_config();
            $sogo_config01->loadSOGoConfigString('<?xml version="1.0"?><sogo_conf><dict>' . $domain_conf['custom'] . '</dict></sogo_conf>');
            $domain_config = $sogo_config01->getConfigArray();
            unset($domain_conf, $sogo_config01);
        } else if (isset($domain_conf['default'])) {
            $sogo_config02 = new sogo_config();
            $sogo_config02->loadSOGoConfigString('<?xml version="1.0"?><sogo_conf><dict>' . $domain_conf['default'] . '</dict></sogo_conf>');
            $domain_config = $sogo_config02->getConfigArray();
            unset($domain_conf, $sogo_config02);
        }

        //* fields allowed by current user
        $_allowed_fields = $app->tform->formDef['tabs'][$app->tform->formDef['tab_default']]['fields'];
        $allowed_fields = array();
        $c = 0;
        foreach ($_allowed_fields as $_allowed_fields_key => $_allowed_fields_value) {
            $allowed_fields[$c] = array(
                'name' => $_allowed_fields_key,
                'name_txt' => (!empty($app->tform->wordbook[$_allowed_fields_key . '_txt']) ? $app->tform->wordbook[$_allowed_fields_key . '_txt'] : (!empty($app->tform->wordbook[$_allowed_fields_key]) ? $app->tform->wordbook[$_allowed_fields_key] : $_allowed_fields_key)),
                'type' => $_allowed_fields_value['formtype'],
                'default' => $_allowed_fields_value['default'],
            );
            if (is_array($_allowed_fields_value['value'])) {
                $values = "";
                foreach ($_allowed_fields_value['value'] as $_allowed_fields_value_key => $_allowed_fields_value_value) {
                    if (empty($values)) {
                        $values = "{$_allowed_fields_value_key}:{$_allowed_fields_value_value}";
                    } else {
                        $values .= ",{$_allowed_fields_value_key}:{$_allowed_fields_value_value}";
                    }
                }
            } else {
                $values = $_allowed_fields_value['value'];
            }
            $allowed_fields[$c]['values'] = $values;
            $c++;
        }
        unset($c);
        $app->tform->formDef['tabs'][$app->tform->formDef['tab_default']]['fields'] = array();

        $_hidden_fields = array();

        if (isset($domain_config) && is_array($domain_config)) {
            foreach ($domain_config as $lckey => $lcvalue) {
                foreach ($lcvalue as $lcvalue_key => $lcvalue_value) {
                    //* remove allready added fields
                    foreach ($allowed_fields as $allowed_fields_key => $allowed_fields_value) {
                        if ($allowed_fields_value['name'] == $lcvalue_key) {
                            unset($allowed_fields[$allowed_fields_key]);
                            break;
                        }
                    }
                    if (!isset($_allowed_fields[$lcvalue_key])) {
                        /*
                         * we only use the name of hidden fields to minimize the users
                         * ability to edit the posted values.! 
                         */
                        $_hidden_fields[]['name'] = $lcvalue_key;
                        continue;
                    }
                    if ($lcvalue_key == 'SOGoSuperUsernames') {
                        $app->tform->formDef['tabs'][$app->tform->formDef['tab_default']]['fields'][$lcvalue_key] = $app->sogo_config->getISPConfigFormField($lcvalue_key, array('RECORDID' => $this->id, 'VALUE' => $lcvalue_value));
                    } else if ($lcvalue_key != 'SOGoUserSources' && $lcvalue_key != 'SOGoMailDomain')
                        $app->tform->formDef['tabs'][$app->tform->formDef['tab_default']]['fields'][$lcvalue_key] = $app->sogo_config->getISPConfigFormField($lcvalue_key, $lcvalue_value);
                }
            }
        }
        $app->tpl->setLoop('allowed_fields', $allowed_fields);
        $app->tpl->setLoop('hidden_fields', $_hidden_fields);
        unset($_hidden_fields, $_allowed_fields, $allowed_fields);

        $app->tpl->setVar('id', $this->id);
        $app->tpl->setVar('server_id', $this->dataRecord['server_id']);
        $app->tpl->setVar('domain', $this->dataRecord['domain']);

        $record = $this->getHTML(array(), $app->tform->formDef['tab_default'], 'NEW');
        $app->tpl->setLoop('records', $record);
    }

    /**
     * 
     * @global app $app
     * @global type $conf
     */
    public function onUpdate() {
        global $app, $conf;
        $app->uses('sogo_config');
        if (count($_POST) > 1) {

            $domain = $app->db->queryOneRecord('SELECT `domain` FROM `mail_domain` WHERE `domain_id`=' . @intval($_POST["id"]));

            $domain_conf = $this->getConfig(@intval($_POST['server_id']), $domain['domain']);
            $app->sogo_config->loadSOGoConfigString('<?xml version="1.0"?><sogo_conf><dict>' . (isset($domain_conf['custom']) && !empty($domain_conf['custom']) ? $domain_conf['custom'] : $domain_conf['default']) . '</dict></sogo_conf>');
            $old_config = $app->sogo_config->getConfigArray();
            $new_config = array(
                '{{DOMAIN}}' => array(
                    'SOGoMailDomain' => '{{DOMAIN}}', //* must be isset.
                    'SOGoUserSources' => $old_config['{{DOMAIN}}']["SOGoUserSources"],
                )
            );

            foreach ($_POST as $key => $value) {
                //* no not them
                if ($key == 'id' || $key == 'domain' || $key == 'server_name' || $key == 'server_id' || $key == 'phpsessid' || $key == 'next_tab' || $key == 'SOGoUserSources')
                    continue;
                //* hidden values, not allowed to be edited by current user
                if ($key == $value) {
                    $new_config['{{DOMAIN}}']["{$key}"] = $old_config['{{DOMAIN}}']["{$key}"];
                    continue;
                }
                //* edited by user allowed
                if ($key == 'SOGoMailListViewColumnsOrder') {
                    //*  posted as string but is realy an array
                    $new_config['{{DOMAIN}}']["{$key}"] = explode(',', $value);
                } else {
                    $new_config['{{DOMAIN}}']["{$key}"] = $value;
                }
            }

            $server = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . @intval($_POST['server_id']));

            //* if no imap, smtp or sieve server isset set them to allow multi mail servers useing the same instance of sogo
            if (!isset($new_config['{{DOMAIN}}']["SOGoIMAPServer"])) {
                $new_config['{{DOMAIN}}']["SOGoIMAPServer"] = 'imap://' . $server['server_name'] . ':143';
            }
            if (!isset($new_config['{{DOMAIN}}']["SOGoSMTPServer"])) {
                $new_config['{{DOMAIN}}']["SOGoSMTPServer"] = $server['server_name'];
            }
            if (!isset($new_config['{{DOMAIN}}']["SOGoSieveServer"])) {
                $new_config['{{DOMAIN}}']["SOGoSieveServer"] = 'sieve://' . $server['server_name'] . ':4190';
            }

            //* if no useruser name(s) isset set to default
            if (!isset($new_config['{{DOMAIN}}']["SOGoSuperUsernames"]) || empty($new_config['{{DOMAIN}}']["SOGoSuperUsernames"])) {
                $new_config['{{DOMAIN}}']["SOGoSuperUsernames"] = array("{{DOMAINADMIN}}");
            }
            $domain_config_file = $app->sogo_config->createDomainConfig($new_config);
            
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
            if (!file_put_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$domain['domain']}.conf", $domain_config_file)) {
                $app->log('Unable to write new sogo domain config for ' . $domain['domain'], LOGLEVEL_ERROR);
            } else {
                chmod(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$domain['domain']}.conf", 0777);
                $this->_fake_update_datalog(array(
                    'new' => array(
                        'server_id' => @intval($_POST['server_id']),
                        'config' => $new_config,
                    ),
                    'old' => array(
                        'server_id' => @intval($_POST['server_id']),
                        'config' => $old_config,
                    )
                ));
            }
        }
        header("Location: " . $app->tform->formDef['list_default']);
    }

    /**
     * the method creats a FAKE datalog update wee need this to force the system to 
     * think it needs to run the cron data on mail_domain table on ISPConfig > 3.0.4 we can use $app->db->datalogSave($tablename, 'UPDATE', $index_field, $index_value, $old_rec, $new_rec, $force_update); with $force_update set to true
     * after much testing this will not to my knowledge do anything to your system other than run the cron job..
     * @global app $app
     */
    private function _fake_update_datalog($change) {
        global $app;
        $diffstr = $app->db->quote(serialize($change));
        $app->db->query("INSERT INTO sys_datalog (dbtable,dbidx,server_id,action,tstamp,user,data) VALUES ('fake_tb_sogo','server_id:{$change['new']['server_id']}','{$change['new']['server_id']}','u','" . time() . "','{$app->db->quote($_SESSION['s']['user']['username'])}','{$diffstr}')");
    }

    function getHTML($record, $tab, $action = 'NEW') {
        global $app;
        $record = $app->tform->getHTML($record, $tab, $action);

//        foreach ($app->tform->formDef['tabs'][$tab]['fields'] as $key => $field) {
//            switch ($field['formtype']) {
//                default:
//                    break;
//            }
//        }

        $ret = array();
        foreach ($record as $key => $value) {
            $txt = $key;
            if (!empty($app->tform->wordbook[$txt . '_txt']))
                $txt = $app->tform->wordbook[$txt . '_txt'];
            else if (!empty($app->tform->wordbook[$txt]))
                $txt = $app->tform->wordbook[$txt];
            $ret[] = array('name' => $key, 'text' => $txt, 'value' => $value, 'type' => $app->tform->formDef['tabs'][$tab]['fields'][$key]['formtype']);
        }
        return $ret;
    }

    function getConfig($server_id, $domain) {
        global $app, $conf;
        $server = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . @intval($server_id));
        $return = array();
        if (file_exists(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/{$domain}.conf")) {
            //* default domain config if exists
            $return['default'] = file_get_contents(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/{$domain}.conf");
        } else if (file_exists(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/{$server['server_name']}.conf")) {
            //* NO default domain config, then default server config if exists
            $return['default'] = file_get_contents(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/{$server['server_name']}.conf");
        } else {
            //* NO no nothing! hmm use default
            $return['default'] = file_get_contents(ISPC_ROOT_PATH . "/../server/conf/sogo_domains/domains_default.conf");
        }

        if (file_exists(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$domain}.conf")) {
            //* custom domain config if exists
            $return['custom'] = file_get_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$domain}.conf");
        } else if (file_exists(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$server['server_name']}.conf")) {
            //* NO custom domain config, then custom server config if exists
            $return['custom'] = file_get_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/domains/{$server['server_name']}.conf");
        }
        return $return;
    }

}

$app->tform_actions = new page_action();
$app->tform_actions->onLoad();
?>
