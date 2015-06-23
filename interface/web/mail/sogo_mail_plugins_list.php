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


require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
$list_def_file = "list/sogo_plugins.list.php";
$app->auth->check_module_permissions('mail');
$app->uses('listform_actions');
if (!$app->auth->is_admin()) {
    //* not admin filter inactive plugins
    /*
    @todo
        temporary solution
        the better option will by to set the proper values in db columns
        --- Client only plugin ---
        [sys_userid, sys_groupid] = set to clients values 
        [sys_perm_other] = Set to empty
        --- all clients ---
        [sys_userid, sys_groupid] = set to what ever..
        [sys_perm_other] = 'r'; r= read only for all
        
        do this in file.
        interface/web/admin/sogo_plugins_edit.php:64
        and 
        interface/web/admin/sogo_plugins_edit.php:88
    */
    $app->listform_actions->SQLExtWhere = " `active`='y'  AND (`client_id`='{$_SESSION['s']['user']['client_id']}' OR `client_id`='0') ";
}
$app->listform_actions->onLoad();
