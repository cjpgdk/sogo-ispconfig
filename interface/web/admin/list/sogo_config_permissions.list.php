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

$liste["name"] = "sogo_config_permissions_index";
$liste["table"] = "sogo_config_permissions_index";
$liste["table_idx"] = "scpi";
$liste["search_prefix"] = "search_";
$liste["records_per_page"] = "15";
$liste["file"] = "sogo_config_permissions_list.php";
$liste["edit_file"] = "sogo_config_permissions_edit.php";
$liste["delete_file"] = "sogo_config_permissions_del.php";
$liste["paging_tpl"] = "templates/paging.tpl.htm";
$liste["auth"] = "no"; // we dont need auth

/*
  scpi            :   table index
  scpi_is_global  :   default settings record
  scpi_type       :   client or reseller
  scpi_clients    :   list of client ids.
 */

$liste["item"][] = array(
    'field' => "scpi_is_global",
    'datatype' => "INTEGER",
    'formtype' => "SELECT",
    'op' => "=",
    'prefix' => "",
    'suffix' => "",
    'width' => "",
    'value' => array(
        0 => "No",
        1 => "Yes"
    )
);
$liste['item'][] = array(
    'field' => 'scpi_type',
    'datatype' => 'VARCHAR',
    'formtype' => 'SELECT',
    'op' => '=',
    'prefix' => '',
    'suffix' => '',
    'width' => '',
    'value' => array('client' => 'Client', 'reseller' => 'Reseller')
);

$liste['item'][] = array(
    'field' => 'scpi_clients',
    'datatype' => 'VARCHAR',
    'op' => 'like',
    'prefix' => '%',
    'suffix' => '%',
    'width' => ''
);