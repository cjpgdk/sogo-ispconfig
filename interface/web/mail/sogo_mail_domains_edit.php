<?php

/*
 * Copyright (C) 2015 Christian M. Jensen
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
 * @author Christian M. Jensen <christian@cmjscripter.net>
 * @copyright 2014-2015 Christian M. Jensen
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

$tform_def_file = "form/sogo_domains.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$app->auth->check_module_permissions('mail');

$app->uses('tpl,tform,functions,sogo_helper');
$app->load('tform_actions');

class tform_action extends tform_actions {

    static private $edit_permissions = array();
    static private $load_domain_id = null;
    static private $load_domain_name = null;
    static private $load_server_id = null;

    /** @global app $app */
    public function onLoad() {
        global $app;

        $tmp_sogo_domain = $app->db->queryOneRecord('SELECT `sogo_id`,`domain_id`,`domain_name`,`server_id`,`server_name` FROM `sogo_domains` WHERE `sogo_id`=' . intval($_REQUEST["id"]));
        self::$load_domain_id = $tmp_sogo_domain['domain_id'];
        self::$load_domain_name = $tmp_sogo_domain['domain_name'];
        self::$load_server_id = $tmp_sogo_domain['server_id'];

        //* get permissions if not admin
        if (!$app->auth->is_admin()) {
            $_uid = $app->auth->get_user_id();
            $_cid = $app->sogo_helper->get_client_id();
            if ($app->auth->has_clients($_uid))
                self::$edit_permissions = $app->sogo_helper->getResellerConfigPermissions($_cid);
            else
                self::$edit_permissions = $app->sogo_helper->getClientConfigPermissions($_cid);
        }
        parent::onLoad();
    }

    /** @global app $app */
    public function onShow() {
        global $app;

        $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring'] = str_replace('{DOMAINNAME}', self::$load_domain_name, $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring']);
        //* i like this to be translated WHY IS THIS NOT DONE IN CORE FILES..!..! :-(
        foreach ($app->tform->formDef["tabs"]['domain']['fields'] as $key => & $value) {
            if (isset($value['value']) && is_array($value['value'])) {
                foreach ($value['value'] as $innerkey => & $innervalue) {
                    if (isset($app->tform->wordbook[$innerkey])) {
                        //* using the key of the field to translate
                        $innervalue = $app->tform->wordbook[$innerkey];
                    } else if (isset($app->tform->wordbook[$innervalue])) {
                        //* using the value of the field to translate
                        $innervalue = $app->tform->wordbook[$innervalue];
                    }
                }
            }
        }

        parent::onShow();
    }

    /** @global app $app */
    public function onShowEnd() {
        global $app;
        if (!$app->auth->is_admin()) {
            if (isset(self::$edit_permissions) & count(self::$edit_permissions) > 0)
                $app->tpl->setVar(self::$edit_permissions);
            //* imap stuff
            $app->tpl->setVar('show_imap_section', $this->_show_imap_section());
            //* sieve stuff
            $app->tpl->setVar('show_sieve_section', $this->_show_sieve_section());
            //* smtp stuff
            $app->tpl->setVar('show_smtp_section', $this->_show_smtp_section());
        }
        parent::onShowEnd();
    }

    public function onInsert() /* onBeforeInsert() */ {
        global $app;
        $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND2'];
        $app->error($msg);
        exit;
    }

    public function onUpdate()/* onBeforeUpdate() */ {
        global $app;
        //* if not admin.
        if (!$app->auth->is_admin()) {
            if ($app->sogo_helper->configExists(self::$load_server_id)) {
                //* get server defaults
                $domain_config_fileds = $app->sogo_helper->getDomainConfigFields();
                if ($server_config = $app->db->queryOneRecord("SELECT * FROM `sogo_config` WHERE `sogo_id`=" . $app->sogo_helper->getConfigIndex(self::$load_server_id)))
                    foreach ($domain_config_fileds as $key => $value)
                        if (!isset($this->dataRecord[$key]) && isset($server_config[$key])) {
                            if ($key == "SOGoCustomXML")
                                continue;
                            $this->dataRecord[$key] = $server_config[$key];
                        }
            }else {
                $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND2'];
                //* if we get here as user it's an error as this check is done in onLoad()
                $app->log("SOGo server configuration is missing on server: #" . self::$load_server_id
                        . ', User: #' . $app->auth->get_user_id()
                        . " Tried to load domain: #" . $this->dataRecord['domain'], LOGLEVEL_ERROR);
                $app->error($msg);
                exit;
            }
        }
        parent::onUpdate();
    }

    public function onAfterUpdate() {
        $this->__fixDomainOwner();
        parent::onAfterUpdate();
    }

    /** @global app $app */
    private function __fixDomainOwner() {
        global $app;
        $result = $app->db->queryOneRecord('SELECT `sys_userid`,`sys_groupid`,`sys_perm_user`,`sys_perm_group`,`sys_perm_other` FROM `mail_domain` WHERE `domain_id`=' . intval(self::$load_domain_id));
        if (isset($result['sys_userid']) && isset($result['sys_groupid']) && isset($result['sys_perm_user']) && isset($result['sys_perm_group']) && isset($result['sys_perm_other'])) {
            $app->db->query("UPDATE `sogo_domains` SET "
                    . "`sys_userid` = '" . intval($result['sys_userid']) . "', "
                    . "`sys_groupid` = '" . intval($result['sys_groupid']) . "', "
                    . "`sys_perm_user` = '{$result['sys_perm_user']}', "
                    . "`sys_perm_group` = '{$result['sys_perm_group']}', "
                    . "`sys_perm_other` = '{$result['sys_perm_other']}' "
                    . "WHERE `sogo_id` ='" . /* self::$domain_config_index */ $this->id . "' AND `domain_id` ='" . self::$load_domain_id . "';");
        }
    }

    private function _show_smtp_section() {
        if (isset(self::$edit_permissions['permission_smtp_server']) && self::$edit_permissions['permission_smtp_server'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_mailing_mechanism']) && self::$edit_permissions['permission_mailing_mechanism'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_mail_spool_path']) && self::$edit_permissions['permission_mail_spool_path'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_mail_custom_from_enabled']) && self::$edit_permissions['permission_mail_custom_from_enabled'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_smtp_authentication_type']) && self::$edit_permissions['permission_smtp_authentication_type'] == 'y')
            return 1;
        return 0;
    }

    private function _show_sieve_section() {
        if (isset(self::$edit_permissions['permission_sieve_filter_forward']) && self::$edit_permissions['permission_sieve_filter_forward'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_sieve_filter_vacation']) && self::$edit_permissions['permission_sieve_filter_vacation'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_sieve_server']) && self::$edit_permissions['permission_sieve_server'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_sieve_filter_enable_disable']) && self::$edit_permissions['permission_sieve_filter_enable_disable'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_sieve_folder_encoding']) && self::$edit_permissions['permission_sieve_folder_encoding'] == 'y')
            return 1;
        return 0;
    }

    private function _show_imap_section() {
        if (isset(self::$edit_permissions['permission_imap_server']) && self::$edit_permissions['permission_imap_server'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_conforms_imapext']) && self::$edit_permissions['permission_imap_conforms_imapext'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_acl_style']) && self::$edit_permissions['permission_imap_acl_style'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_folder_drafts']) && self::$edit_permissions['permission_imap_folder_drafts'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_folder_trash']) && self::$edit_permissions['permission_imap_folder_trash'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_folder_sent']) && self::$edit_permissions['permission_imap_folder_sent'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_subscription_folder_format']) && self::$edit_permissions['permission_subscription_folder_format'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_mail_auxiliary_accounts']) && self::$edit_permissions['permission_mail_auxiliary_accounts'] == 'y')
            return 1;
        return 0;
    }

}

$app->tform_action = new tform_action();
$app->tform_action->onLoad();
