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

$list_def_file = "list/sogo_domain.list.php";
$tform_def_file = "form/sogo_domains.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

if ($conf['demo_mode'] == true)
    $app->error('This function is disabled in demo mode.');

//* Check permissions for module
$app->auth->check_module_permissions('mail');

//$app->uses("tform_actions");
//$app->tform_actions->onDelete();
//* don't allow delete in mail module only reset
require $list_def_file;

if (!is_object($app->tform))
    $app->uses('tform');
if (!is_object($app->tpl))
    $app->uses('tpl');

$app->tform->loadFormDef($tform_def_file);


$_grant_access = false;
if (!$app->auth->is_admin()) {
    //* validate access.
    if ($app->tform->checkPerm(intval($_REQUEST['id']), 'u') == false)
        $app->error($app->tform->lng('error_no_delete_permission'), $liste["file"], true);

    $_grant_access = true;
} else {
    //* if admin then just run
    $_grant_access = true;
}

if ($_grant_access) {
    $app->load('tform_actions');
    if (!is_object($app->sogo_helper))
        $app->uses('sogo_helper');
    $app->tform_actions = new tform_actions();
    $app->tform_actions->id = intval($_REQUEST['id']);
    $_SESSION["s"]["form"]["tab"] = $app->tform->formDef['tab_default'];
    if (method_exists($app->auth, 'csrf_token_get')) {
        $csrf_token = $app->auth->csrf_token_get('sogo_domains');
        $_POST['_csrf_id'] = $csrf_token['csrf_id'];
        $_POST['_csrf_key'] = $csrf_token['csrf_key'];
        $app->tform_actions->dataRecord['_csrf_id'] = $_POST['_csrf_id'];
        $app->tform_actions->dataRecord['_csrf_key'] = $_POST['_csrf_key'];
    }


    $domain_config_fileds = $app->sogo_helper->getDomainConfigFields();
    if ($server_config = $app->db->queryOneRecord("SELECT * FROM `sogo_config` WHERE `sogo_id`=" . $app->tform_actions->id)) {
        foreach ($domain_config_fileds as $key => $value) {
            if (isset($server_config[$key])) {
                if ($key == "SOGoCustomXML")
                    continue;
                $app->tform_actions->dataRecord[$key] = $server_config[$key];
            }
        }
    }
    $app->tform_actions->onSubmit();
}



header("Location: " . $liste["file"]);
