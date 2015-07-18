<?php

/*
 * Copyright (C) 2014  Christian M. Jensen
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
 *  @author Christian M. Jensen <christian@cmjscripter.net>
 *  @copyright 2014 Christian M. Jensen
 *  @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

$tform_def_file = "form/sogo_domains.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$app->auth->check_module_permissions('mail');

$app->uses('tpl,tform,functions,sogo_helper');
$app->load('tform_actions');

class tform_action extends tform_actions {

    /** @global app $app */
    public function onLoad() {
        global $app;
        $dId = (int) (
                isset($_REQUEST["domain_id"]) ?
                        intval($_REQUEST["domain_id"]) :
                        (
                        isset($_SESSION['s']['module']["sogo_conifg_domain_id"]) ?
                                intval($_SESSION['s']['module']["sogo_conifg_domain_id"]) : 0)
                );
        $dConfId = (int) $app->sogo_helper->getDomainConfigIndex($dId);

        //* if no config for domain exists set id = 0 to create a new
        if ($dId != 0 && !$app->sogo_helper->configDomainExists($dId)) {
            $result = $app->db->queryOneRecord('SELECT `domain_id`,`server_id`,`domain` FROM `mail_domain` WHERE `domain_id`=' . intval($dId));
            if (!isset($result['domain_id']) && !isset($result['server_id'])) {
                //* domain do not exists.!
                echo "HEADER_REDIRECT:mail/sogo_mail_domain_list.php";
                exit;
            } else {
                //* domain exists but no config exists yet
                $result2 = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . intval($result['server_id']));
                $_REQUEST["id"] = 0;
                $this->__domain_id = $result['domain_id'];
                $this->__domain_name = $result['domain'];
                $this->__server_id = $result['server_id'];
                $this->__server_name = $result2['server_name'];
            }
        } else if ($dId != 0 && $dConfId != 0 && $app->sogo_helper->configDomainExists($dId)) {
            //* server config found, redirect to get correct vars page loaded
            if (!isset($_REQUEST["id"])) {
                echo "HEADER_REDIRECT:mail/sogo_mail_domains_edit.php?id=" . $dConfId . '&domain_id=' . $dId;
                exit;
            }
        } else {
            //* nothing is valid
            echo "HEADER_REDIRECT:mail/sogo_mail_domain_list.php";
            exit;
        }
        $_SESSION['s']['module']["sogo_conifg_domain_id"] = $dId;
        if (!$app->sogo_helper->configExists($this->__server_id) && !$app->sogo_helper->configExistsByDomain($dId)) {
            global $tform_def_file;
            $app->tform->loadFormDef($tform_def_file);
            if (!$app->auth->is_admin()) {
                $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND2'];
            } else {
                $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND'];
            }
            $app->error($msg);
        } else
            parent::onLoad();
    }

    /** @global app $app */
    public function onShow() {
        global $app;

        $dId = (int) (
                isset($_REQUEST["domain_id"]) ?
                        intval($_REQUEST["domain_id"]) :
                        (
                        isset($_SESSION['s']['module']["sogo_conifg_domain_id"]) ?
                                intval($_SESSION['s']['module']["sogo_conifg_domain_id"]) : 0)
                );
        $result = $app->db->queryOneRecord('SELECT `domain_id`,`server_id`,`domain` FROM `mail_domain` WHERE `domain_id`=' . $dId);
        //* replace var "{DOMAINNAME}" in query string
        if (isset($result['domain'])) {
            $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring'] = str_replace('{DOMAINNAME}', $result['domain'], $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring']);
        }

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
        $app->tpl->setVar('domain_id', $this->__domain_id);
        $app->tpl->setVar('domain_name', $this->__domain_name);
        $app->tpl->setVar('server_id', $this->__server_id);
        $app->tpl->setVar('server_name', $this->__server_name);

        if (!$app->auth->is_admin()) {
            $_uid = $app->auth->get_user_id();
            if ($app->auth->has_clients($_uid))
                $edit_permissions = $app->sogo_helper->getResellerConfigPermissions($_uid);
            else
                $edit_permissions = $app->sogo_helper->getClientConfigPermissions($_uid);

            if (isset($edit_permissions) & count($edit_permissions) > 0)
                $app->tpl->setVar($edit_permissions);

            //* imap stuff
            $_imap_section = 0;
            if (isset($edit_permissions['permission_imap_server']) && $edit_permissions['permission_imap_server'] == 'y')
                $_imap_section = 1;
            if (isset($edit_permissions['permission_imap_conforms_imapext']) && $edit_permissions['permission_imap_conforms_imapext'] == 'y')
                $_imap_section = 1;
            if (isset($edit_permissions['permission_imap_acl_style']) && $edit_permissions['permission_imap_acl_style'] == 'y')
                $_imap_section = 1;
            if (isset($edit_permissions['permission_imap_folder_drafts']) && $edit_permissions['permission_imap_folder_drafts'] == 'y')
                $_imap_section = 1;
            if (isset($edit_permissions['permission_imap_folder_trash']) && $edit_permissions['permission_imap_folder_trash'] == 'y')
                $_imap_section = 1;
            if (isset($edit_permissions['permission_imap_folder_sent']) && $edit_permissions['permission_imap_folder_sent'] == 'y')
                $_imap_section = 1;
            if (isset($edit_permissions['permission_subscription_folder_format']) && $edit_permissions['permission_subscription_folder_format'] == 'y')
                $_imap_section = 1;
            if (isset($edit_permissions['permission_mail_auxiliary_accounts']) && $edit_permissions['permission_mail_auxiliary_accounts'] == 'y')
                $_imap_section = 1;
            $app->tpl->setVar('show_imap_section', $_imap_section);


            //* sieve stuff
            $_sieve_section = 0;
            if (isset($edit_permissions['permission_sieve_filter_forward']) && $edit_permissions['permission_sieve_filter_forward'] == 'y')
                $_sieve_section = 1;
            if (isset($edit_permissions['permission_sieve_filter_vacation']) && $edit_permissions['permission_sieve_filter_vacation'] == 'y')
                $_sieve_section = 1;
            if (isset($edit_permissions['permission_sieve_server']) && $edit_permissions['permission_sieve_server'] == 'y')
                $_sieve_section = 1;
            if (isset($edit_permissions['permission_sieve_filter_enable_disable']) && $edit_permissions['permission_sieve_filter_enable_disable'] == 'y')
                $_sieve_section = 1;
            if (isset($edit_permissions['permission_sieve_folder_encoding']) && $edit_permissions['permission_sieve_folder_encoding'] == 'y')
                $_sieve_section = 1;
            $app->tpl->setVar('show_sieve_section', $_sieve_section);

            //* smtp stuff
            $_smtp_section = 0;
            if (isset($edit_permissions['permission_smtp_server']) && $edit_permissions['permission_smtp_server'] == 'y')
                $_smtp_section = 1;
            if (isset($edit_permissions['permission_mailing_mechanism']) && $edit_permissions['permission_mailing_mechanism'] == 'y')
                $_smtp_section = 1;
            if (isset($edit_permissions['permission_mail_spool_path']) && $edit_permissions['permission_mail_spool_path'] == 'y')
                $_smtp_section = 1;
            if (isset($edit_permissions['permission_mail_custom_from_enabled']) && $edit_permissions['permission_mail_custom_from_enabled'] == 'y')
                $_smtp_section = 1;
            if (isset($edit_permissions['permission_smtp_authentication_type']) && $edit_permissions['permission_smtp_authentication_type'] == 'y')
                $_smtp_section = 1;
            $app->tpl->setVar('show_smtp_section', $_smtp_section);
        }


        parent::onShowEnd();
    }

    /** @global app $app */
    public function onShowNew() {
        global $app;
        //* @todo change this to insert new row with server default then show edit..
        if ($app->sogo_helper->configExists($this->__server_id)) {

            $server_config = $app->db->queryOneRecord("SELECT * FROM `sogo_config` WHERE `sogo_id`=" . $app->sogo_helper->getConfigIndex($this->__server_id));

            //* on new copy all default values from server config if exists
            foreach ($app->tform->formDef["tabs"] as $key => & $value) {
                foreach ($value['fields'] as $key => & $value) {
                    if ($key == "sogo_id" || $key == "sys_userid" || $key == "sys_groupid" || $key == "sys_perm_user" || $key == "sys_perm_group" || $key == "sys_perm_other" || $key == "server_id" || $key == "server_name" || $key == "domain_id" || $key == "domain_name" || $key == "SOGoCustomXML") {
                        continue;
                    } else {
                        $value['default'] = (isset($server_config[$key]) ? $server_config[$key] : $value['default']);
                    }
                }
            }
        }
        parent::onShowNew();
    }

    public function onAfterInsert() {
        global $app;
        //* if reseller or client fix missing server default values..!
        if (!$app->auth->is_admin()) {
            $domain_config_fileds = $app->sogo_helper->getDomainConfigFields();
            $server_config = $app->db->queryOneRecord("SELECT * FROM `sogo_config` WHERE `sogo_id`=" . $app->sogo_helper->getConfigIndex($this->__server_id));
            $server_id = @$this->dataRecord['server_id'];
            $domain_id = @$this->dataRecord['domain_id'];
            $missing_values = array();
            foreach ($domain_config_fileds as $key => $value) {
                if (!isset($this->dataRecord[$key])) {
                    if ($key == "SOGoCustomXML")
                        continue;
                    $value['default'] = isset($server_config[$key]) ? $server_config[$key] : $value['default'];
                    $missing_values[$key] = $value;
                }
            }
            unset($domain_config_fileds, $server_config);
            $sql = " UPDATE `sogo_domains` ";
            $sql_where = " WHERE `domain_id`=" . intval($domain_id) . " AND `server_id`=" . intval($server_id); //* domain_name server_name
            $sql_set = " SET ";

            if (!empty($missing_values) && count($missing_values) > 1) {
                foreach ($missing_values as $key => $value) {
                    $sql_set .= " `{$key}`='{$value['default']}',";
                }
            }
            $sql .= trim($sql_set, ',') . " {$sql_where}";
            $app->db->query($sql);
            unset($sql, $sql_set, $sql_where, $missing_values, $key, $value, $domain_id, $server_id);
        }
        $this->__fixDomainOwner();
        parent::onAfterInsert();
    }

    public function onAfterUpdate() {
        $this->__fixDomainOwner();
        parent::onAfterUpdate();
    }

    /** @global app $app */
    private function __fixDomainOwner() {
        global $app;
        $dId = (int) (
                isset($_REQUEST["domain_id"]) ?
                        intval($_REQUEST["domain_id"]) :
                        (
                        isset($_SESSION['s']['module']["sogo_conifg_domain_id"]) ?
                                intval($_SESSION['s']['module']["sogo_conifg_domain_id"]) : 0)
                );
        $result = $app->db->queryOneRecord('SELECT `sys_userid`,`sys_groupid`,`sys_perm_user`,`sys_perm_group`,`sys_perm_other` FROM `mail_domain` WHERE `domain_id`=' . intval($dId));
        if (isset($result['sys_userid']) && isset($result['sys_groupid']) && isset($result['sys_perm_user']) && isset($result['sys_perm_group']) && isset($result['sys_perm_other'])) {
            $dConfId = (int) $app->sogo_helper->getDomainConfigIndex($dId);
            $app->db->query("UPDATE `sogo_domains` SET "
                    . "`sys_userid` = '" . intval($result['sys_userid']) . "', "
                    . "`sys_groupid` = '" . intval($result['sys_groupid']) . "', "
                    . "`sys_perm_user` = '{$result['sys_perm_user']}', "
                    . "`sys_perm_group` = '{$result['sys_perm_group']}', "
                    . "`sys_perm_other` = '{$result['sys_perm_other']}' "
                    . "WHERE `sogo_id` ='{$dConfId}' AND `domain_id` ='{$dId}';");
        }
    }

}

$app->tform_action = new tform_action();
$app->tform_action->onLoad();
