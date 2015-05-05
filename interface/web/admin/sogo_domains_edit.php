<?php

/**
 * Copyright (C) 2015  Christian M. Jensen
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
 * @copyright 2015 Christian M. Jensen
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 * @link https://github.com/cmjnisse/sogo-ispconfig original source code for sogo-ispconfig
 */

$tform_def_file = "form/sogo_domains.tform.php";
require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
$app->auth->check_module_permissions('admin');
if (method_exists($app->auth, 'check_security_permissions')) {
    $app->auth->check_security_permissions('admin_allow_server_services');
} else {
    if (!$app->auth->is_admin())
        die('only allowed for administrators.');
}
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
                echo "HEADER_REDIRECT:admin/sogo_domains_list.php";
                exit;
            } else {
                //* domain exists but no config exists yet
                $result2 = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . intval($result['server_id']));
                $_REQUEST["id"] = 0;
                $this->__domain_id = $result['domain_id'];
                $this->__domain_name = $result['domain'];
                $this->__server_name = $result2['server_name'];
            }
        } else if ($dId != 0 && $dConfId != 0 && $app->sogo_helper->configDomainExists($dId)) {
            //* server config found, redirect to get correct vars page loaded
            if (!isset($_REQUEST["id"])) {
                echo "HEADER_REDIRECT:admin/sogo_domains_edit.php?id=" . $dConfId . '&domain_id=' . $dId;
                exit;
            }
        } else {
            //* nothing is valid
            echo "HEADER_REDIRECT:admin/sogo_domains_list.php";
            exit;
        }
        $_SESSION['s']['module']["sogo_conifg_domain_id"] = $dId;
        parent::onLoad();
    }
    /** @global app $app */
    public function onShow() {
        global $app;
        $dId = (int) (
                isset($_REQUEST["domain_id"]) ?
                        intval($_REQUEST["domain_id"]) :
                        (isset($_SESSION['s']['module']["sogo_conifg_domain_id"]) ?
                                intval($_SESSION['s']['module']["sogo_conifg_domain_id"]) : 0)
                );
        $result = $app->db->queryOneRecord('SELECT `domain_id`,`server_id`,`domain` FROM `mail_domain` WHERE `domain_id`=' . $dId);
        //* replace var "{DOMAINNAME}" in query string
        if (isset($result['domain'])) {
            $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring'] = str_replace(
                    '{DOMAINNAME}', $result['domain'], $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring']
            );
        }
        parent::onShow();
    }
    /** @global app $app */
    public function onShowEnd() {
        global $app;
        $app->tpl->setVar('domain_id', $this->__domain_id);
        $app->tpl->setVar('domain_name', $this->__domain_name);
        $app->tpl->setVar('server_name', $this->__server_name);
        parent::onShowEnd();
    }
    /** @global app $app */
    public function onShowNew() {
        global $app;
        //* @todo change this to insert new row with server default then show edit..
        if ($app->sogo_helper->configExists($this->__server_id)) {
            $sConf = $app->db->queryOneRecord("SELECT * FROM `sogo_config` WHERE `sogo_id`=" . $app->sogo_helper->getConfigIndex($this->__server_id));
            //* on new copy all default values from server config if exists
            foreach ($app->tform->formDef["tabs"] as $key => & $value) {
                foreach ($value['fields'] as $key => & $value) {
                    if ($key == "sogo_id" || $key == "sys_userid" || $key == "sys_groupid" || $key == "sys_perm_user" || $key == "sys_perm_group" || $key == "sys_perm_other" || $key == "server_id" || $key == "server_name" || $key == "domain_id" || $key == "domain_name" || $key == "SOGoCustomXML") {
                        continue;
                    } else {
                        $value['default'] = (isset($sConf[$key]) ? $sConf[$key] : $value['default']);
                    }
                }
            }
        }
        parent::onShowNew();
    }
    public function onBeforeInsert() {
        $this->onBeforeUpdate(false);
        parent::onBeforeInsert();
    }
    public function onBeforeUpdate($callBase = true) {
        global $app;
        if (!isset($this->dataRecord['server_id'])) {
            if ($callBase)
                parent::onBeforeUpdate();
            return;
        }
        // fix server_name must be sogo server name
        $result = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . intval($this->dataRecord['server_id']));
        if (isset($result['server_name']))
            $this->dataRecord['server_name'] = $result['server_name'];
        if ($callBase)
            parent::onBeforeUpdate();
    }
    public function onAfterInsert() {
        $this->onAfterUpdate(false);
        parent::onAfterInsert();
    }
    public function onAfterUpdate($callBase = true) {
        global $app;
        if (!isset($this->dataRecord['domain_id'])) {
            if ($callBase)
                parent::onAfterUpdate();
            return;
        }
        // fix user permissions.
        $result = $app->db->queryOneRecord('SELECT `sys_userid`,`sys_groupid`,`sys_perm_user`,`sys_perm_group`,`sys_perm_other` FROM `mail_domain` WHERE `domain_id`=' . intval($this->dataRecord['domain_id']));
        if (isset($result['sys_userid']) &&
                isset($result['sys_groupid']) &&
                isset($result['sys_perm_user']) &&
                isset($result['sys_perm_group']) &&
                isset($result['sys_perm_other'])) {
            $dConfId = (int) $app->sogo_helper->getDomainConfigIndex(intval($this->dataRecord['domain_id']));
            $app->db->query("UPDATE `sogo_domains` SET "
                    . "`sys_userid` = '" . intval($result['sys_userid']) . "', "
                    . "`sys_groupid` = '" . intval($result['sys_groupid']) . "', "
                    . "`sys_perm_user` = '{$result['sys_perm_user']}', "
                    . "`sys_perm_group` = '{$result['sys_perm_group']}', "
                    . "`sys_perm_other` = '{$result['sys_perm_other']}' "
                    . "WHERE `sogo_id` ='{$dConfId}' AND `domain_id` =" . intval($this->dataRecord['domain_id']) . ";");
        }
        if ($callBase)
            parent::onAfterUpdate();
    }
}
$app->tform_action = new tform_action();
$app->tform_action->onLoad();