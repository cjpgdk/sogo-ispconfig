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
$app->auth->check_module_permissions('admin');
//* for this version (Update 10) 
if (method_exists($app->auth, 'check_security_permissions')) {
    //* only we check if admin is allowed 
    $app->auth->check_security_permissions('admin_allow_server_services');
}

if ($conf['demo_mode'] == true)
    $app->error('This function is disabled in demo mode.');

//* no harm in doing an update, plugin always does a direct sql select
$app->db->datalogSave('sogo_config', 'UPDATE', 'sogo_id', $_REQUEST['id'], array(
    'sogo_id' => $_REQUEST['id'],
    'old' => 'TRUE',
    'new' => 'FALSE'), array(
    'sogo_id' => $_REQUEST['id'],
    'old' => 'FALSE',
    'new' => 'TRUE'), TRUE);
require_once 'list/sogo_server.list.php';
header("Location: " . $liste["file"]);
