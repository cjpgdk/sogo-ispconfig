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
if (method_exists($app->auth, 'check_security_permissions')) {
    $app->auth->check_security_permissions('admin_allow_server_services');
} else {
    if (!$app->auth->is_admin())
        die('only allowed for administrators.');
}

if ($conf['demo_mode'] == true)
    $app->error('This function is disabled in demo mode.');

//* no harm in doing an update, plugin always does a direct sql select
$rec = $app->db->queryOneRecord("SELECT * FROM sogo_config WHERE sogo_id=".intval($_REQUEST['id']));

$drec = $app->db->quote(serialize(array('old' => $rec, 'new' => $rec)));
$app->db->query("INSERT INTO sys_datalog (dbtable,dbidx,server_id,action,tstamp,user,data) "
        . "VALUES "
        . "('sogo_config','sogo_id:{$_REQUEST['id']}','{$rec['server_id']}','u','" . time() . "','{$app->db->quote($_SESSION['s']['user']['username'])}','{$drec}')");

require_once 'list/sogo_server.list.php';
header("Location: {$liste["file"]}?msg=REBUILD_TRIGGERED&server_n={$rec['server_name']}");
