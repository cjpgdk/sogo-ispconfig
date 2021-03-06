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
    //* admin go home :)
    $liste["name"] = "sogo_plugins";
} elseif ($app->auth->has_clients($app->auth->get_user_id())) {
    $liste["name"] = "sogo_plugins_reseller";
} else {
    $liste["name"] = "sogo_plugins_user";
}
$liste["table"] = "sogo_plugins";
$liste["table_idx"] = "spid";
$liste["search_prefix"] = "search_";
$liste["records_per_page"] = "15";
$liste["file"] = "sogo_mail_plugins_list.php";
/* i want to do this but is it needed for resellers to upload plugins.!
  if ($app->auth->is_admin() || $app->auth->has_clients($app->auth->get_user_id())) {
  $liste["edit_file"] = "sogo_plugins_edit.php";
  $liste["delete_file"] = "sogo_plugins_del.php";
  } else {
 */
$liste["edit_file"] = $liste["file"];
$liste["delete_file"] = $liste["file"];
//}
$liste["paging_tpl"] = "templates/paging.tpl.htm";
$liste["auth"] = "yes";

if ($app->auth->is_admin()) {
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
}
if ($app->auth->is_admin() || $app->auth->has_clients($app->auth->get_user_id())) {
    $liste['item'][] = array(
        'field' => 'client_id',
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'op' => '=',
        'prefix' => '',
        'suffix' => '',
        'datasource' => array(
            'type' => 'SQL',
            'querystring' => "SELECT client_id,CONCAT(contact_name, ' :: ', username) as name FROM client WHERE {AUTHSQL} ORDER BY contact_name",
            'keyfield' => 'client_id',
            'valuefield' => 'name'
        ),
        'width' => '',
        'value' => array(0 => 'All')
    );
}
$liste['item'][] = array(
    'field' => 'name',
    'datatype' => 'VARCHAR',
    'op' => '=',
    'prefix' => '',
    'suffix' => '',
    'width' => ''
);
$liste['item'][] = array(
    'field' => 'description',
    'datatype' => 'VARCHAR',
    'op' => 'like',
    'prefix' => '%',
    'suffix' => '%',
    'width' => ''
);
