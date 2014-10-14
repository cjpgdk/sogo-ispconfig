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

$form["title"] = "SOGo Module Settings";
$form["description"] = "Change the behaviour of the sogo module, and the way it builds the configuration";
$form["name"] = "sogo_module";
$form["action"] = "sogo_module_settings.php";
$form["db_table"] = "sogo_module";
$form["db_table_idx"] = "smid";
$form["db_history"] = "yes";
$form["tab_default"] = "module";
$form["list_default"] = "sogo_module_settings.php"; //* no list
$form["auth"] = 'yes';
$form["auth_preset"]["userid"] = 0;
$form["auth_preset"]["groupid"] = 0;
$form["auth_preset"]["perm_user"] = 'riu'; //* NO DELETE 
$form["auth_preset"]["perm_group"] = 'riu'; //* NO DELETE 
$form["auth_preset"]["perm_other"] = '';
$form["tabs"]['module'] = array(
    'title' => "SOGo Module Settings",
    'width' => 70,
    'template' => "templates/sogo_module_edit.htm",
    'fields' => array(
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
        'sql_of_mail_server' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'n',
            'value' => array(
                'n' => $app->lng('No'),
                'y' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        )
    )
);