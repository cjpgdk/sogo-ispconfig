<?php

$tform_def_file = "form/sogo_config.tform.php";
require_once('../../lib/config.inc.php');
require_once('../../lib/app.inc.php');
$app->auth->check_module_permissions('admin');
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

    /**
     * this method is do not use the parent::onShowNew(), but self::_ShowForm();
     * @global app $app
     * @global array $conf
     * @see parent::onShowNew()
     */
    function onShowNew() {
        global $app, $conf;
        //* we are not using SQL so Override
        $this->_ShowForm();
    }

    /**
     * this method is do not use the parent::onShowEdit(), but self::_ShowForm();
     * @global app $app
     * @global array $conf
     * @see parent::onShowEdit()
     */
    function onShowEdit() {
        global $app, $conf;
        //* we are not using SQL so Override
        $this->_ShowForm();
    }

    /**
     * The form is generated from SOGo.conf or if it exists the selected server config.
     * @global app $app
     * @global array $conf
     */
    function _ShowForm() {
        global $app, $conf;
        $app->uses('sogo_config');

        $server_id = @$_REQUEST['id'];
        if (intval($server_id) <= 0) {
            //* no server id go to list
            $app->error('no server id found');
            header("Location: " . $app->tform->formDef['list_default']);
            exit;
        }
        $app->tpl->setVar('id', $server_id);
        if (!$serverec = $app->db->queryOneRecord("SELECT `server_name` FROM `server` WHERE `server_id`=" . intval($server_id))) {
            //* no server name ?? go to list
            $app->error('no server name found ??');
            header("Location: " . $app->tform->formDef['list_default']);
            exit;
        }
        $app->tpl->setVar('server_name', $serverec['server_name']);


        $record = array();


        $SERVERSOGOCONFIG = $this->getConfig();
        //* if we have a config file loaded
        if (!is_null($SERVERSOGOCONFIG) && is_string($SERVERSOGOCONFIG)) {
            $app->uses('sogo_config');
            if ($app->sogo_config->loadSOGoConfigString($SERVERSOGOCONFIG)) {
                // die('<pre>'.print_r($app->sogo_config->parse(),true));
                // $app->sogo_config->printObject(TRUE);
                $loadedConfig = $app->sogo_config->getConfigArray('sogod');
                if (is_array($loadedConfig)) {
                    foreach ($loadedConfig as $lckey => $lcvalue) {
                        if ($lckey != 'domains')
                            $app->tform->formDef['tabs'][$app->tform->formDef['tab_default']]['fields'][$lckey] = $app->sogo_config->getISPConfigFormField($lckey, $lcvalue);
                    }
                }
            }
        }
        $record = $this->getHTML($record, $app->tform->formDef['tab_default'], 'NEW');
        $app->tpl->setLoop('records', $record);
    }

    /**
     * this method is do not use the parent::onSubmit();
     * @global app $app
     * @global array $conf
     */
    public function onSubmit() {
        //* we stil don't use sql so all new or save actions is overriden
        global $app, $conf;
        $app->uses('sogo_config');

        //* we allways need a post
        if (count($_POST) > 1) {
            $old_config = $this->getConfig();
            //* if we have a config file loaded
            if (!is_null($old_config) && is_string($old_config))
                $app->sogo_config->loadSOGoConfigString($old_config);
            $old_config = $app->sogo_config->getConfigArray();
            $new_config = $old_config;
            
            foreach ($_POST as $key => $value) {
                //* no not them
                if ($key == 'id' || $key == 'server_name' || $key == 'phpsessid' || $key == 'next_tab')
                    continue;
                //* YUP!, God awtan liden Elna, gods fredd
                if ($key == 'SOGoMailListViewColumnsOrder') {
                    //*  posted as string but is realy an array
                    $new_config['sogod']["{$key}"] = explode(',', $value);
                } else {
                    $new_config['sogod']["{$key}"] = $value;
                }
                //* if your like it give me "Pæng-a" ny.!
            }
            //* get the servername for this config.
            if (!$serverec = $app->db->queryOneRecord("SELECT `server_name` FROM `server` WHERE `server_id`=" . intval($_POST['id']))) {
                //* no server name ?? go to list
                $app->error('no server name found ??');
                header("Location: " . $app->tform->formDef['list_default']);
                exit;
            }
            if ($app->sogo_config->createConfig($new_config)) {
                $check = 0;
                if (intval($conf['server_id']) == intval($_POST['id'])) {
                    //* only if we are on the server we edit.
                    $check += $app->sogo_config->writeConfig('sogo.conf');
                    //* for helved ubuntu va skal det til for, det dér.! i need some weed now!!
                    $check += $app->sogo_config->writeConfig('sogo-sogod.plist.conf', true);
                }
                //* we allways write the config with servername
                $check += $app->sogo_config->writeConfig($serverec['server_name'] . '.conf');

                if ($check > 0) {
                    //* trigger a sogo reconf..!
                    $this->_fake_update_datalog(intval($_POST['id']), array(
                        'new' => array(
                            'server_id' => $_POST['id'],
                            'config' => $new_config,
                        ),
                        'old' => array(
                            'server_id' => $_POST['id'],
                            'config' => $old_config,
                        )
                    ));
                }
            }
        }
        //* HAHA din forbanede "Jylkat"
        header("Location: " . $app->tform->formDef['list_default']);
    }

    /**
     * the method creats a FAKE datalog update wee need this to force the system to 
     * think it needs to run the cron data on fake_tb_sogo table on ISPConfig >= 3.0.5 we can use $app->db->datalogSave($tablename, 'UPDATE', $index_field, $index_value, $old_rec, $new_rec, $force_update); with $force_update set to true
     * after much testing this will not to my knowledge do anything to your system other than run the cron job..
     * @global app $app
     */
    private function _fake_update_datalog($server_id, $change) {
        global $app;

        $diffstr = $app->db->quote(serialize($change));
        $app->db->query("INSERT INTO sys_datalog (dbtable,dbidx,server_id,action,tstamp,user,data) VALUES ('fake_tb_sogo','server_id:{$server_id}','{$server_id}','u','" . time() . "','{$app->db->quote($_SESSION['s']['user']['username'])}','{$diffstr}')");
    }

    /**
     * wee need som custom fileds so we make it here
     * @global app $app
     * @param type $record
     * @param type $tab
     * @param type $action
     * @return type
     */
    function getHTML($record, $tab, $action = 'NEW') {
        global $app;
        $record = $app->tform->getHTML($record, $tab, $action);

        foreach ($app->tform->formDef['tabs'][$tab]['fields'] as $key => $field) {
            switch ($field['formtype']) {
                case 'CUSTOMFIELDSORTER':
                    $out = "";
                    $field['value'] = explode(',', $field['value']);
                    foreach ($field['value'] as $k => $v) {
                        $_v = $v;
                        if (!empty($app->tform->wordbook[$_v . '_txt']))
                            $_v = $app->tform->wordbook[$_v . '_txt'];
                        $out .= "<li class=\"ui-state-default\" fieldvalue=\"{$v}\">"
                                . "<span class=\"ui-icon ui-icon-arrowthick-2-n-s\"  style=\"float: left;\"></span>"
                                . "{$_v}"
                                . "</li>";
                    }
                    $record[$key] = $out;
                    break;
                default:
                    break;
            }
        }
        $ret = array();
        foreach ($record as $key => $value) {
            $txt = $key;
            if (!empty($app->tform->wordbook[$txt . '_txt']))
                $txt = $app->tform->wordbook[$key . '_txt'];
            $ret[] = array('name' => $key, 'text' => $txt, 'value' => $value, 'type' => $app->tform->formDef['tabs'][$tab]['fields'][$key]['formtype']);
        }

        return $ret;
    }

    function _error($msg) {
        if (file_exists(dirname(__FILE__) . '/../themes/' . $_SESSION['s']['theme'] . '/templates/error.tpl.htm')) {
            $content = file_get_contents(dirname(__FILE__) . '/../themes/' . $_SESSION['s']['theme'] . '/templates/error.tpl.htm');
        } else {
            $content = file_get_contents(dirname(__FILE__) . '/../themes/default/templates/error.tpl.htm');
        }
        return str_replace('###ERRORMSG###', $msg, $content);
    }

    function getConfig() {

        if (file_exists(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/sogo.conf")) {
            //* default SOGo "CUSTOM" config if exists, wee allways use the main server a template for new servers
            $_default = file_get_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/sogo.conf");
        } else if (file_exists(ISPC_ROOT_PATH . "/../server/conf/sogo.conf")) {
            //* NO CUSTOM then default SOGo config if exists
            $_default = file_get_contents(ISPC_ROOT_PATH . "/../server/conf/sogo.conf");
        } else {
            //* NO nothing! WHAT!!! use the default from the form file
            $_default = NULL;
        }


        if (file_exists(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/{$serverec['server_name']}.conf")) {
            //* custom SOGo config for selected server.!
            $_custom = file_get_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/{$serverec['server_name']}.conf");
        }
        $SERVERSOGOCONFIG = "";
        if (isset($_custom)) {
            $SERVERSOGOCONFIG = $_custom;
        } elseif (isset($_default)) {
            $SERVERSOGOCONFIG = $_default;
        } else {
            $SERVERSOGOCONFIG = NULL;
            echo $this->_error($app->lng('Unable to find/load any configuration files for SOGo we are now using a default template once you click save we will attempt to correct this, if you think this is an error please check your configuration and the permissions on your files'));
            //$this->_error($app->lng('sogo_config_file_exists_error'));
        }
        return $SERVERSOGOCONFIG;
    }

}

$app->tform_actions = new page_action();
$app->tform_actions->onLoad();

