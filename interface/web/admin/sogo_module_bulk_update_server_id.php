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

$param_error = false;
if ((isset($_GET['dids']) && (strlen($_GET['dids']) == 0)) || !isset($_GET['dids']))
    $param_error = true;
$_domainIds = explode('|', $_GET['dids']);
if (!is_array($_domainIds) || count($_domainIds) <= 0)
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
    $app->log("_GET[dids]: {$_GET['dids']}", LOGLEVEL_DEBUG);
    $app->log("prin_r(\$_domainIds): " . print_r($_domainIds, true), LOGLEVEL_DEBUG);
}

//* check if post for server change
if (isset($_POST['dochange']) && isset($_POST['server_id'])) {
    if (sogo_helper::isExtendedDebug())
        $app->log("Starting SOGo server update on domains #" . implode(', #', $_domainIds), LOGLEVEL_DEBUG);
    //* hide db errors from output, DB class logs them to sys_log
    $_show_error_messages = $app->db->show_error_messages;
    $app->db->show_error_messages = false;

    $new_server_id = $app->functions->intval($_POST['server_id']);
    //* get the full default config for selected server.
    $new_server = $app->db->queryOneRecord("SELECT * FROM `sogo_config` WHERE `server_id`={$new_server_id}");
    if ($new_server === FALSE || (!isset($new_server['server_id']) || !isset($new_server['server_name']))) {
        $app->log("SOGo server with id {$new_server_id}, do not exists or have have no configuration.", LOGLEVEL_WARN);
        exit;
    }
    if (sogo_helper::isExtendedDebug())
        $app->log("update selected domains to sogo server {$new_server['server_name']}#{$new_server['server_id']}", LOGLEVEL_DEBUG);

    $app->uses('tpl,tform,functions');
    $app->load('tform_actions');
    foreach ($_domainIds as $value) {
        $mail_domain = $app->db->queryOneRecord("SELECT * FROM `mail_domain` WHERE `domain_id`=" . $app->functions->intval($value));
        $app->log("Start update of domain: {$value}#" . (isset($mail_domain['domain']) ? $mail_domain['domain'] : 'ERROR DOMAIN NOT FOUND'), LOGLEVEL_DEBUG);
        if (isset($mail_domain['domain_id']) && $mail_domain['domain_id'] == $value) {
            $sogo_domain = $app->db->queryOneRecord("SELECT * FROM `sogo_domains` WHERE `domain_id`=" . $app->functions->intval($mail_domain['domain_id']));
            $sogo_conf_exists = FALSE;
            if ($sogo_domain !== FALSE)
                $sogo_conf_exists = TRUE;

            if (!$sogo_conf_exists) {
                //* create new SOGo config
                $app->log("Create new sogo config for domain: {$value}#{$mail_domain['domain']}", LOGLEVEL_DEBUG);
                $sogo_domain = array();
                $sogo_domain['server_id'] = $new_server['server_id'];
                $sogo_domain['server_name'] = $new_server['server_name'];
                $sogo_domain['domain_id'] = $mail_domain['domain_id'];
                $sogo_domain['domain_name'] = $mail_domain['domain'];
                $sogo_domain['sys_userid'] = $mail_domain['sys_userid'];
                $sogo_domain['sys_groupid'] = $mail_domain['sys_groupid'];
                $sogo_domain['sys_perm_user'] = $mail_domain['sys_perm_user'];
                $sogo_domain['sys_perm_group'] = $mail_domain['sys_perm_group'];
                $sogo_domain['sys_perm_other'] = $mail_domain['sys_perm_other'];

                // $new_server //* all default data.
                $domain_defaults = $app->sogo_helper->getDomainConfigFields();
                foreach ($domain_defaults as $dvalue) {
                    $sogo_domain[$dvalue['name']] = (isset($new_server[$dvalue['name']]) ? $new_server[$dvalue['name']] : $dvalue['default']);
                }
                if (method_exists($app->db, 'datalogInsert')) {
                    $app->db->datalogInsert('sogo_domains', $sogo_domain, 'sogo_id');
                } else {
                    //* BLAH BLAH, to bad no new data, i hate sql queries
                }
            } else {
                //* update domain
                if ($sogo_domain['server_id'] != $new_server['server_id'] || $new_server['server_name'] != $new_server['server_name']) {
                    $app->log("Update required on domain: {$sogo_domain['domain_name']}", LOGLEVEL_DEBUG);
                    $index = $app->functions->intval($sogo_domain["sogo_id"]);
                    if (method_exists($app->db, 'datalogUpdate')) {
                        //* update the record and add it to jobqueue
                        $app->db->datalogUpdate('sogo_domains', array('server_id' => $new_server['server_id'], 'server_name' => $new_server['server_name']), 'sogo_id', $index, FALSE/* no forced update */);
                    } else {
                        //* update the record manually
                        $sql = "UPDATE `sogo_domains` SET `server_id` = '{$new_server['server_id']}',`server_name` = '{$app->db->quote($new_server['server_name'])}' WHERE `sogo_id` ={$index};";
                        if ($app->db->query($sql))
                            $app->log("Updated {$sogo_domain['domain_name']} from SOGo server [{$sogo_domain['server_name']}] to [{$new_server['server_name']}]");
                        //* check for errors, if none create a datalog change
                        if ($app->db->errorNumber === false || intval($app->db->errorNumber) == 0) {
                            $new_rec = $sogo_domain;
                            $new_rec['server_id'] = $new_server['server_id'];
                            $new_rec['server_name'] = $new_server['server_name'];
                            $app->db->datalogSave($tablename, 'UPDATE', 'sogo_id', $index, $sogo_domain, $new_rec, FALSE/* no forced update */);
                        } else
                            $app->log("{$sogo_domain['domain_name']} were not updated, error while saving data", LOGLEVEL_WARN);
                    }
                } else if (sogo_helper::isExtendedDebug())
                    $app->log("No need to update domain {$sogo_domain['domain_name']}", LOGLEVEL_DEBUG);
            }
        } else if (sogo_helper::isExtendedDebug())
            $app->log("mail domain with id {$value}, do not exists in database", LOGLEVEL_WARN);
    }
    //* change back to default, (properly not needed but just to be sure)
    $app->db->show_error_messages = $_show_error_messages;
    exit;
}

//* show server change form.
$app->uses('tpl,functions');

include ISPC_ROOT_PATH . '/lib/lang/' . $_SESSION['s']['language'] . '.lng';

if (is_array($wb))
    $wb_global = $wb;

$lng_file = ISPC_ROOT_PATH . "/web/admin/lib/lang/" . $_SESSION["s"]["language"] . "_sogo_module_bulk_update_server_id.lng";
if (!file_exists($lng_file))
    $lng_file = ISPC_ROOT_PATH . "/web/admin/lib/lang/en_sogo_module_bulk_update_server_id.lng";
include $lng_file;

if (is_array($wb_global)) {
    $wb = $app->functions->array_merge($wb_global, $wb);
    unset($wb_global);
}

$app->tpl->newTemplate("listpage.tpl.htm");
$app->tpl->setInclude('content_tpl', 'templates/sogo_module_bulk_update_server_id.htm');

$app->tpl->setVar($wb);
unset($wb);

$domainIds = '';
foreach ($_domainIds as $value)
    if (((string) intval($value) == $value )&& $app->sogo_helper->domainSOGoConfigExists($value))
        $domainIds .= intval($value) . '|';
$app->tpl->setVar("dids", trim($domainIds, '|'));

$result = $app->db->queryAllRecords("SELECT `server_id`,`server_name` FROM `sogo_config` ORDER BY server_name");
$app->tpl->setLoop('sogo_servers', $result);

$app->tpl->pparse();
