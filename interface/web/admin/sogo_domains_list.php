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

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$list_def_file = "list/sogo_domains.list.php";

$app->auth->check_module_permissions('admin');
$app->uses('listform_actions, sogo_helper');

//* remove old saved domain id
unset($_SESSION['s']['module']["sogo_conifg_domain_id"]);

class listform_action extends listform_actions {}

$app->listform_action = new listform_action();
$app->listform_action->onLoad();
