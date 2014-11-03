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

if ($app->auth->is_admin()) {
    $liste["name"] = "sogo_domains";
} elseif ($app->auth->has_clients($app->auth->get_user_id())) {
    $liste["name"] = "sogo_domains_reseller";
} else {
    $liste["name"] = "sogo_domains_user";
}
$liste["table"] = "mail_domain";
$liste["table_idx"] = "domain_id";
$liste["search_prefix"] = "search_";
$liste["records_per_page"] = "15";
$liste["file"] = "sogo_mail_domain_list.php";
$liste["edit_file"] = "sogo_mail_domains_edit.php";
$liste["delete_file"] = "sogo_mail_domains_del.php";
$liste["paging_tpl"] = "templates/paging.tpl.htm";
$liste["auth"] = "yes";


$liste["item"][] = array(
    'field' => "active",
    'datatype' => "VARCHAR",
    'formtype' => "SELECT",
    'op' => "=",
    'prefix' => "",
    'suffix' => "",
    'width' => "",
    'value' => array(
        'y' => "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>",
        'n' => "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>"
    )
);

if ($app->auth->is_admin()) {
    $liste["item"][] = array(
        'field' => "sys_groupid",
        'datatype' => "INTEGER",
        'formtype' => "SELECT",
        'op' => "=",
        'prefix' => "",
        'suffix' => "",
        'datasource' => array(
            'type' => 'SQL',
            'querystring' => 'SELECT groupid, name FROM sys_group WHERE groupid != 1 ORDER BY name',
            'keyfield' => 'groupid',
            'valuefield' => 'name'
        ),
        'width' => "",
        'value' => ""
    );
} else if ($app->auth->has_clients($app->auth->get_user_id())) {
    $rclients = $app->db->queryOneRecord("SELECT `groups` FROM `sys_user` WHERE `userid`={$app->auth->get_user_id()}");
    if (!isset($rclients['groups']))
        $rclients['groups'] = "";
    $liste["item"][] = array(
        'field' => "sys_groupid",
        'datatype' => "INTEGER",
        'formtype' => "SELECT",
        'op' => "=",
        'prefix' => "",
        'suffix' => "",
        'datasource' => array(
            'type' => 'SQL',
            'querystring' => 'SELECT `username`,`userid` FROM `sys_user` WHERE `userid` IN (' . $rclients['groups'] . ')',
            'keyfield' => 'userid',
            'valuefield' => 'username'
        ),
        'width' => "",
        'value' => ""
    );
}

$liste["item"][] = array(
    'field' => "server_id",
    'datatype' => "INTEGER",
    'formtype' => "SELECT",
    'op' => "=",
    'prefix' => "",
    'suffix' => "",
    'datasource' => array(
        'type' => 'SQL',
        'querystring' => 'SELECT a.server_id, a.server_name FROM server a, mail_domain b WHERE (a.server_id = b.server_id) AND ({AUTHSQL-B}) ORDER BY a.server_name',
        'keyfield' => 'server_id',
        'valuefield' => 'server_name'
    ),
    'width' => "",
    'value' => ""
);

$liste["item"][] = array(
    'field' => "domain",
    'datatype' => "VARCHAR",
    'filters' => array(
        0 => array('event' => 'SHOW',
            'type' => 'IDNTOUTF8'
        )
    ),
    'formtype' => "TEXT",
    'op' => "like",
    'prefix' => "%",
    'suffix' => "%",
    'width' => "",
    'value' => ""
);
