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

    //* modifyed in order to replace {DOMAINNAME} in  SOGoSuperUsernames querystring
    function onShowEdit() {
        global $app, $conf;

        // bestehenden Datensatz anzeigen
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

        if (isset($this->dataRecord['domain_name'])) {
            $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring'] = str_replace(
                    '{DOMAINNAME}', $this->dataRecord['domain_name'], $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring']
            );
        }
        $app->tpl->setVar('is_edit', 1);

        // Userdaten umwandeln
        $record = $app->tform->getHTML($record, $this->active_tab, 'EDIT');
        $record['id'] = $this->id;
        
        //* edit config, not allowed to edit server or domain
        $record['domain_id_disabled'] = $this->dataRecord['domain_id'];
        $record['server_id_disabled'] = $this->dataRecord['server_id'];

        $app->tpl->setVar($record);
    }

    //* record fix.
    public function onBeforeInsert() {
        $this->onBeforeUpdate(false);
        parent::onBeforeInsert();
    }

    /** @global app $app */
    public function onBeforeUpdate($callBase = true) {
        global $app;
        if (!isset($this->dataRecord['server_id'])) {
            if ($callBase)
                parent::onBeforeUpdate();
            return;
        }
        if ($app->tform->errorMessage == '') {
            // fix server_name must be sogo server name
            if ($result = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . intval($this->dataRecord['server_id']))) {
                if (isset($result['server_name']))
                    $this->dataRecord['server_name'] = $result['server_name'];
            } else
                $app->tform->errorMessage .= $app->tform->lng("errmsg_server_id_not_found") . "<br />\r\n";

            // fix domain_name must be sogo server name
            if ($result = $app->db->queryOneRecord('SELECT `domain` FROM `mail_domain` WHERE `domain_id`=' . intval($this->dataRecord['domain_id']))) {
                if (isset($result['domain']))
                    $this->dataRecord['domain_name'] = $result['domain'];
            } else
                $app->tform->errorMessage .= $app->tform->lng("errmsg_domain_id_not_found") . "<br />\r\n";
        }

        if ($app->tform->errorMessage == '') {
            //* if set server defaults.
            if (isset($_REQUEST['server_defaults']) && $_REQUEST['server_defaults'] == 'yes') {
                if ($result = $app->db->queryOneRecord('SELECT * FROM `sogo_config` WHERE `server_id`=' . intval($this->dataRecord['server_id']))) {
                    foreach ($result as $key => $value) {
                        if ($key != 'SOGoCustomXML' && $key != 'SOGoSuperUsernames' && 
                                $key != 'sogo_id' && $key != 'sys_userid' && $key != 'sys_groupid' && $key != 'sys_perm_user' && 
                                $key != 'sys_perm_group' && $key != 'sys_perm_other' && $key != 'server_id' && $key != 'server_name' && $key != 'domain_id' && $key != 'domain_name')
                            $this->dataRecord[$key] = $value;
                    }
                } else
                    $app->tform->errorMessage .= $app->tform->lng("errmsg_sogo_server_not_found") . "<br />\r\n";
            }
        }

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
