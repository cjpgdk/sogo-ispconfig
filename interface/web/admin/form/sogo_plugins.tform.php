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



$form["title"] = "Client Plugins";
$form["description"] = "Any type of plugins you have to use in order to integrate SOGo these will be listed under 'Email' for your clients";
$form["name"] = "sogo_plugins";
$form["action"] = "sogo_plugins_edit.php";
$form["db_table"] = "sogo_plugins";
$form["db_table_idx"] = "spid";
$form["db_history"] = "yes";
$form["tab_default"] = "plugins";
$form["list_default"] = "sogo_plugins_list.php";
$form["auth"] = 'yes';
$form["auth_preset"]["userid"] = 0;
$form["auth_preset"]["groupid"] = 0;
$form["auth_preset"]["perm_user"] = 'riud';
$form["auth_preset"]["perm_group"] = 'riud';
$form["auth_preset"]["perm_other"] = 'r'; //* all can read by default
$form["tabs"]['plugins'] = array(
    'title' => "SOGo Plugins",
    'width' => 70,
    'template' => "templates/sogo_plugins_edit.htm",
    'fields' => array(
        "active" => array(
            'datatype' => "VARCHAR",
            'formtype' => "SELECT",
            'default' => 'y',
            'value' => array(
                'y' => "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>",
                'n' => "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>"
            )
        ),
        'client_id' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'SELECT',
            'default' => 0,
            'datasource' => array('type' => 'SQL',
                'querystring' => "SELECT client_id,CONCAT(contact_name,' :: ',username) as name FROM client WHERE {AUTHSQL} ORDER BY contact_name",
                'keyfield' => 'client_id',
                'valuefield' => 'name'
            ),
            'value' => array(0 => 'All')
        ),
        'name' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'Plugin Name',
            'validators' => array(
                0 => array(
                    'type' => 'NOTEMPTY',
                    'errmsg' => 'pluginname_error_empty'
                ),
            ),
            'value' => '',
            'maxlength' => '255'
        ),
        'description' => array(
            'datatype' => 'TEXT',
            'formtype' => 'TEXT',
            'default' => 'Plugin Name',
            'value' => '',
            'maxlength' => '999999'
        ),
        'filetype' => array(
            'datatype' => "VARCHAR",
            'formtype' => "SELECT",
            'default' => 'download',
            'value' => array(
                'download' => "Upload File",
                'link' => "Link to file"
            )
        ),
        'file' => array(
            'datatype' => 'TEXT',
            'formtype' => 'TEXT',
            'default' => 'http://example.com/plugin.ext',
            'validators' => array(
                0 => array(
                    'type' => 'NOTEMPTY',
                    'errmsg' => 'pluginfile_error_empty'
                ),
            ),
            'value' => '',
            'maxlength' => '999999'
        ),
    )
);