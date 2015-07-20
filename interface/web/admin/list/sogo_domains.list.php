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

$liste["name"] = "sogo_domains";
#$liste["table"] = "mail_domain";
$liste["table"] = "sogo_domains";
#$liste["table_idx"] = "domain_id";
$liste["table_idx"] = "sogo_id";
$liste["search_prefix"] = "search_";
$liste["records_per_page"] = "15";
$liste["file"] = "sogo_domains_list.php";
$liste["edit_file"] = "sogo_domains_edit.php";
$liste["delete_file"] = "sogo_domains_del.php";
$liste["paging_tpl"] = "templates/paging.tpl.htm";
$liste["auth"] = "yes";
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

$liste["item"][] = array(
    'field' => "server_id",
    'datatype' => "INTEGER",
    'formtype' => "SELECT",
    'op' => "like",
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
    'field' => "domain_name",
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
?>
