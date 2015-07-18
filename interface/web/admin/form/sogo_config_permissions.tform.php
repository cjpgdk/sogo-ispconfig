<?php

/*
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
 */
$form["title"] = "SOGo user configuration permissions";
$form["description"] = "";
$form["name"] = "sogo_config_permissions_index";
$form["action"] = "sogo_config_permissions_edit.php";
$form["db_table"] = "sogo_config_permissions_index";
$form["db_table_idx"] = "scpi";
$form["db_history"] = "no";
$form["tab_default"] = "type";
$form["list_default"] = "sogo_config_permissions_list.php";
$form["auth"] = 'no'; // yes / no
$form["auth_preset"]["userid"] = 1; // 0 = id of the user, > 0 id must match with id of current user
$form["auth_preset"]["groupid"] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form["auth_preset"]["perm_user"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_group"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_other"] = ''; //r = read, i = insert, u = update, d = delete

$clients_list = array();
if (isset($_REQUEST['scpi_type'])) {
    if ($_REQUEST['scpi_type'] == 'client') {
        $clients = $app->db->queryAllRecords("SELECT `client_id`,`contact_name`,`username` FROM `client` WHERE `limit_client` = 0");
        foreach ($clients as $value) {
            $clients_list[$value['client_id']] = $value['username'] . " (" . $value['contact_name'] . ")";
        }
    } else if ($_REQUEST['scpi_type'] == 'reseller') {
        $resellers = $app->db->queryAllRecords("SELECT `client_id`,`contact_name`,`username` FROM `client` WHERE (`limit_client` > 0 or `limit_client` = -1)");
        foreach ($resellers as $value) {
            $clients_list[$value['client_id']] = $value['username'] . " (" . $value['contact_name'] . ")";
        }
    }
    //*  filter out all assigned clients
    if (!empty($clients_list)) {
        $tmp = $app->db->queryAllRecords("SELECT `scpi_clients` FROM `sogo_config_permissions_index` WHERE `scpi_is_global`=0 AND `scpi` != " . intval($_REQUEST['id']));
        foreach ($tmp as $value) {
            $_cid = explode(',', $value['scpi_clients']);
            foreach ($_cid as $value)
                unset($clients_list[$value]);
        }
        unset($tmp);
    }
}
if (empty($clients_list)) {
    $clients_list['invalid'] = $app->lng("You do not have any clients left to assign to this type");
}
/*
  scpi            :   table index
  scpi_is_global  :   default settings record
  scpi_type       :   client or reseller
  scpi_clients    :   list of client ids.
 */
$form["tabs"]['type'] = array(
    'title' => "Type",
    'width' => 70,
    'template' => "templates/sogo_config_permissions_type_edit.htm",
    'fields' => array(
        "scpi_is_global" => array(
            'datatype' => "INTEGER",
            'formtype' => "TEXT",
            'default' => 0,
            'value' => 0,
        ),
        "scpi_type" => array(
            'datatype' => "VARCHAR",
            'formtype' => "SELECT",
            'default' => 'client',
            'value' => array('client' => 'Client', 'reseller' => 'Reseller')
        )
    )
);

$form["tabs"]['clients'] = array(
    'title' => "Clients",
    'width' => 70,
    'template' => "templates/sogo_config_permissions_clients_edit.htm",
    'fields' => array(
        "scpi_is_global" => array(
            'datatype' => "INTEGER",
            'formtype' => "TEXT",
            'default' => 0,
            'value' => 0,
        ),
        'scpi_clients' => array(
            'datatype' => 'TEXT',
            'formtype' => 'CHECKBOXARRAY',
            'default' => '',
            'separator' => ',',
            'value' => $clients_list,
        ),
    )
);

$form["tabs"]['permissions'] = array(
    'title' => "Permissions",
    'width' => 70,
    'template' => "templates/sogo_config_permissions_permissions_edit.htm",
    'fields' => array(),
    'plugins' => array(
        'permission_records' => array(
            'class' => 'plugin_sogopermissionslistview',
            'options' => array(
                'listdef' => 'list/sogo_config_permission_records.list.php',
                'sqlextwhere' => " scp_index = " . @$app->functions->intval(@$_REQUEST['id']),
                'sql_order_by' => " ORDER BY scp_name",
                'record_list' => 'lib/sogo_config_permissions_records_names.php',
                'permission_index' => @$app->functions->intval(@$_REQUEST['id'])
            )
        )
    )
);
