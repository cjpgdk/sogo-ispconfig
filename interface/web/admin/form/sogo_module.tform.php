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
 *  @author Christian M. Jensen <christian@cmjscripter.net>
 *  @copyright 2015 Christian M. Jensen
 *  @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

$form["title"] = "SOGo Settings";
$form["description"] = "Change the way the server plugin builds the configuration";
$form["name"] = "sogo_module";
$form["action"] = "sogo_module_settings.php";
$form["db_table"] = "sogo_module";
$form["db_table_idx"] = "smid";
$form["db_history"] = "yes";
$form["tab_default"] = "module";
$form["list_default"] = "sogo_module_settings_list.php";
$form["auth"] = 'yes';
$form["auth_preset"]["userid"] = 0;
$form["auth_preset"]["groupid"] = 0;
$form["auth_preset"]["perm_user"] = 'riu';
$form["auth_preset"]["perm_group"] = 'riu';
$form["auth_preset"]["perm_other"] = '';
$form["tabs"]['module'] = array(
    'title' => "SOGo Module Settings",
    'width' => 70,
    'template' => "templates/sogo_module_edit.htm",
    'fields' => array(
        'server_id' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'TEXT',
            'default' => 0,
            'value' => '',
            'maxlength' => '',
            'required' => 1,
            'width' => 100,
        ),
        'all_domains' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'y',
            'value' => array(
                'n' => $app->lng('No'),
                'y' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'allow_same_instance' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'y',
            'value' => array(
                'n' => $app->lng('No'),
                'y' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'config_rebuild_on_mail_user_insert' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'y',
            'value' => array(
                'n' => $app->lng('No'),
                'y' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
    )
);
//* empty tab handled by javascript and ajax
$form["tabs"]['override'] = array(
    'title' => "SOGo Config",
    'width' => 70,
    'template' => "templates/sogo_module_domains_config_edit.htm",
    'fields' => array(),
);

