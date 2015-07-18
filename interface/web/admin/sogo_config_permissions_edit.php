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
 * @copyright 2015 Christian M. Jensen
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

$tform_def_file = "form/sogo_config_permissions.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$app->auth->check_module_permissions('admin');
if (method_exists($app->auth, 'check_security_permissions')) {
    $app->auth->check_security_permissions('admin_allow_server_services');
} else {
    if (!$app->auth->is_admin())
        die('only allowed for administrators.');
}
//* is ajax, and is ajax allowed?
if (isset($_REQUEST['ajax_k']) &&
        isset($_REQUEST['ajax']) &&
        isset($_SESSION['s']['module']["_sogo_config_permissions_ajax_key"]) &&
        ($_REQUEST['ajax'] == $_SESSION['s']['module']["_sogo_config_permissions_ajax_key"]) &&
        ($_REQUEST['ajax_k'] == $_SESSION['s']['module']["_sogo_config_permissions_ajax_key"]) &&
        ($_REQUEST['ajax'] == $_REQUEST['ajax_k'])) {

    //* is the required data isset
    if (isset($_REQUEST['permission_id']) &&
            isset($_REQUEST['permission_index']) &&
            isset($_REQUEST['permission_name']) &&
            isset($_REQUEST['permission_allowed'])) {

        //* @todo really check how secure this is

        $permission_id = $_REQUEST['permission_id'];
        $permission_index = $_REQUEST['permission_index'];
        $permission_name = $_REQUEST['permission_name'];
        $permission_allowed = ($_REQUEST['permission_allowed'] == 'y' ? 'n' : 'y'); //* it's done reversed

        if ($permission_id == -1) {
            //* new row
            $_sql = "INSERT INTO `sogo_config_permissions` (`sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other`, `scp`, `scp_index`, `scp_name`, `scp_allow`) "
                    . " VALUES (0, 0, 'ru', 'ru', '', NULL, " . intval($permission_index) . ", '{$app->db->quote($permission_name)}', '{$permission_allowed}');";
        } else {
            $_sql = "UPDATE `sogo_config_permissions` SET `scp_allow` = '{$permission_allowed}' WHERE `scp` = " . intval($permission_id);
        }
        $app->db->query($_sql);
    }
    //* key can only be used one time per action.
    unset($_SESSION['s']['module']["_sogo_config_permissions_ajax_key"]);
    exit;
}
//* set a randome key, used to validate the ajax request
$_SESSION['s']['module']["_sogo_config_permissions_ajax_key"] = md5(microtime(true));

$app->uses('tpl,tform,functions');
$app->load('tform_actions');

class tform_action extends tform_actions {

    public function onShowEdit() {
        parent::onShowEdit();
        global $app;
        if (isset($this->dataRecord) && isset($this->dataRecord['scpi_type']) && isset($this->dataRecord['scpi_is_global']) && $this->dataRecord['scpi_is_global'] == 1) {
            $app->tpl->setVar('scpi_type_disabled', $this->dataRecord['scpi_type']);
        }
        if (isset($this->dataRecord) && isset($this->dataRecord['scpi_clients']) && isset($this->dataRecord['scpi_is_global']) && $this->dataRecord['scpi_is_global'] == 1) {
            $app->tpl->setVar('scpi_clients_disabled', $this->dataRecord['scpi_clients']);
        }
        //* set a randome key, used to validate the ajax request
        $app->tpl->setVar('ajax_key', $_SESSION['s']['module']["_sogo_config_permissions_ajax_key"]);

        //* needed to keep the clients on tab change "permissions => clients"
        if ($this->active_tab == "permissions") {
            $app->tpl->setVar('scpi_type', $this->dataRecord['scpi_type']);
        }
    }

}

$page = new tform_action();
$page->onLoad();
