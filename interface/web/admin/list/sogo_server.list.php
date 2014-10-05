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

$liste['name'] = 'sogo_conifg';
$liste['table'] = 'sogo_config';
$liste['table_idx'] = 'sogo_id';
$liste['search_prefix'] = 'search_';
$liste['records_per_page'] = "15";
$liste['file'] = 'sogo_conifg_list.php';
$liste['edit_file'] = 'sogo_conifg_edit.php';
$liste['delete_file'] = 'sogo_conifg_del.php';
$liste['paging_tpl'] = 'templates/paging.tpl.htm';
$liste['auth'] = 'yes';
$liste['item'][] = array(
    'field' => 'server_name',
    'datatype' => 'VARCHAR',
    'filters' => array(
        0 => array(
            'event' => 'SHOW',
            'type' => 'IDNTOUTF8'
        )
    ),
    'formtype' => 'TEXT',
    'op' => 'like',
    'prefix' => '%',
    'suffix' => '%',
    'width' => '',
    'value' => '');

