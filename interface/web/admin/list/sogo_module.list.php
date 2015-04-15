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

$liste["name"] = "sogo_module";
$liste["table"] = "sogo_module";
$liste["table_idx"] = "smid";
$liste["search_prefix"] = "search_";
$liste["records_per_page"] = "5000"; // one page
$liste["file"] = "sogo_module_settings_list.php";
$liste["edit_file"] = "sogo_module_settings.php";
$liste["delete_file"] = "sogo_module_settings_del.php";
$liste["paging_tpl"] = "templates/paging.tpl.htm";
$liste["auth"] = "yes";
/*
all_domains 	enum('y', 'n')
allow_same_instance 	enum('y', 'n')
sql_of_mail_server 	enum('y', 'n')
config_rebuild_on_mail_user_insert 	enum('n', 'y') 	
 */
$liste["item"][] = array(
    'field' => "server_id",
    'datatype' => "INTEGER",
    'formtype' => "SELECT",
    'op' => "=",
    'prefix' => "",
    'suffix' => "",
    'datasource' => array(
        'type' => 'SQL',
        'querystring' => 'SELECT `server_id`,`server_name` FROM `sogo_config` ORDER BY server_name',
        'keyfield' => 'server_id',
        'valuefield' => 'server_name'
    ),
    'width' => "",
    'value' => ""
);