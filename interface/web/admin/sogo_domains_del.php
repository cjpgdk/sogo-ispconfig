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

$list_def_file = "list/sogo_domains.list.php";
$tform_def_file = "form/sogo_domains.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

if ($conf['demo_mode'] == true)
    $app->error('This function is disabled in demo mode.');

//* Check permissions for module
$app->auth->check_module_permissions('admin');
if (method_exists($app->auth, 'check_security_permissions')) {
    $app->auth->check_security_permissions('admin_allow_server_services');
} else {
    if (!$app->auth->is_admin())
        die('only allowed for administrators.');
}

$app->uses("tform_actions,sogo_helper");
$app->load("functions");

//* get domain config id from domain id
$dId = (int) (isset($_REQUEST["domain_id"]) ? intval($_REQUEST["domain_id"]) : 0);
$dConfId = (int) $app->sogo_helper->getDomainConfigIndex($dId);

$_REQUEST['id'] = $dConfId;

$app->tform_actions->onDelete();
