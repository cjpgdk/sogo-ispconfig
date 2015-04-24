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

function getValue($n, $d = false) {
    if (isset($_GET[$n]) && !empty($_GET[$n]))
        return $_GET[$n];
    if (isset($_POST[$n]) && !empty($_POST[$n]))
        return $_POST[$n];
    return $d;
}

$param_error = false;
if (!getValue('dids'))
    $param_error = true;
$_domainIds = explode('|', getValue('dids', ''));
if (isset($_domainIds) && !is_array($_domainIds) || count($_domainIds) <= 0)
    $param_error = true;
if ($param_error) {
    //* @todo set page as 404 or 500 error to simply kill the ajax request
    echo "HEADER_REDIRECT:admin/sogo_module_settings_list.php";
    exit;
}
require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$app->auth->check_module_permissions('admin');
if (method_exists($app->auth, 'check_security_permissions')) {
    $app->auth->check_security_permissions('admin_allow_server_services');
}
$app->uses('sogo_helper');

if (sogo_helper::isExtendedDebug()) {
    $app->log("_GET[dids]: " . getValue('dids'), LOGLEVEL_DEBUG);
    $app->log("prin_r(\$_domainIds): " . print_r($_domainIds, true), LOGLEVEL_DEBUG);
}

//* show configuration change form.
$app->uses('tpl,functions');

if (getValue('dochange', false)) {
    unset($_POST['dochange'],$_POST['dids']);
    foreach ($_POST as $key => $value) {
        if (is_array($value)){
            $_POST[$key] = implode(',', $value);
            $value = implode(',', $value);
        }
        if ($value == 'NOCHANGE' || strpos($value, 'NOCHANGE') !== false)
            unset($_POST[$key]);
    }
    $updated_values = $_POST;
    foreach ($_domainIds as $value) {
        $value = $app->functions->intval($value);
        $mail_domain = $app->db->queryOneRecord("SELECT `domain`,`domain_id` FROM `mail_domain` WHERE `domain_id`={$value}");
        $app->log("Start update of domain: {$value}#" . (isset($mail_domain['domain']) ? $mail_domain['domain'] : 'ERROR DOMAIN NOT FOUND'), LOGLEVEL_DEBUG);
        if (isset($mail_domain['domain_id']) && $mail_domain['domain_id'] == $value &&
                $app->sogo_helper->domainSOGoConfigExists($value)) {
            $sogo_did = $app->sogo_helper->getDomainConfigIndex($value);
            if ($sogo_did > 0) {
                //* update current config
                $sogo_domain = $app->db->queryOneRecord("SELECT * FROM `sogo_domains` WHERE `sogo_id` ={$sogo_did};");
                $app->log("Update domain: {$sogo_domain['domain_name']}", LOGLEVEL_DEBUG);
                if (method_exists($app->db, 'datalogUpdate')) {
                    //* update the record and add it to jobqueue
                    $app->db->datalogUpdate('sogo_domains', $updated_values, 'sogo_id', $sogo_did, FALSE/* no forced update */);
                } else {
                    $new_rec = $sogo_domain;
                    //* update the record manually
                    $querystr = "";
                    foreach ($updated_values as $key => $value) {
                        $querystr .= "`{$key}` = '{$app->db->quote($value)}', ";
                        $new_rec[$key] = $value;
                    }
                    $querystr = trim($querystr, ',');
                    $sql = "UPDATE `sogo_domains` SET {$querystr} WHERE `sogo_id` ={$sogo_did};";
                    $app->db->query($sql);
                    //* check for errors, if none create a datalog change
                    if ($app->db->errorNumber === false || intval($app->db->errorNumber) == 0) {
                        $app->db->datalogSave($tablename, 'UPDATE', 'sogo_id', $sogo_did, $sogo_domain, $new_rec, FALSE/* no forced update */);
                    } else
                        $app->log("{$sogo_domain['domain_name']} were not updated, error while saving data", LOGLEVEL_WARN);
                }
            } else {
                //* new config so no update
            }
        } else
            $app->log("mail domain with id {$value}, do not exists in database", LOGLEVEL_WARN);
    }
    echo "HEADER_REDIRECT:admin/sogo_module_settings_list.php";
    exit;
}


include ISPC_ROOT_PATH . '/lib/lang/' . $_SESSION['s']['language'] . '.lng';

if (is_array($wb))
    $wb_global = $wb;

$lng_file = ISPC_ROOT_PATH . "/web/admin/lib/lang/" . $_SESSION["s"]["language"] . "_sogo_module_bulk_update_config.lng";
if (!file_exists($lng_file))
    $lng_file = ISPC_ROOT_PATH . "/web/admin/lib/lang/en_sogo_module_bulk_update_config.lng";
include $lng_file;

if (is_array($wb_global)) {
    $wb = $app->functions->array_merge($wb_global, $wb);
    unset($wb_global);
}

$app->tpl->newTemplate("listpage.tpl.htm"); //* simple display compared to tabbed_form.htm
$app->tpl->setVar($wb);
unset($wb);

$domainIds = '';
$combined_config = array();
$sogo_config_fields = $app->sogo_helper->getDomainConfigFields();
foreach ($_domainIds as $value) {
    if ((string) intval($value) == $value) {
        if ($result = $app->db->queryOneRecord("SELECT * FROM `sogo_domains` WHERE `domain_id`=" . intval($value))) {
            $domainIds .= intval($value) . '|';
            //* remove none SOGo config keys
            foreach ($result as $key => $rvalue)
                if (!isset($sogo_config_fields[$key]))
                    unset($result[$key]);
            unset($rvalue);
            //* combine the configs to one
            if (empty($combined_config))
                $combined_config = $result;
            else {
                /*
                  set combined config key to NOCHANGE
                  if not the same on all domains
                 */
                foreach ($result as $key => $rvalue) {
                    if ($combined_config[$key] != $rvalue)
                        $combined_config[$key] = 'NOCHANGE';
                }
            }
        }
    }
}
//* STOLEN FROM tform classes
if (!is_object($app->tform))
    $app->uses('tform');
$app->tform->loadFormDef("form/sogo_domains_bulk.tform.php");
$record = $app->tform->encode($combined_config, 'domain', false);
$record = $app->tform->getHTML($record, 'domain', 'EDIT');
$record['dids'] = trim($domainIds, '|');
$app->tpl->setVar($record);
//* set database value.
foreach ($combined_config as $key => $rvalue) {
    $app->tpl->setVar($key . 'DBValue', $rvalue);
}
$app->tform->showForm();
$app->tpl_defaults();
$app->tpl->pparse();
