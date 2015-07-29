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

$app->uses('tpl,tform,functions');
$app->load('tform_actions');


$tform_def_file = "form/sogo_domains_bulk.tform.php";

class tform_action extends tform_actions {

    private $domain_ids = null;

    public function onLoad() {
        $this->domain_ids = explode('|', getValue('dids', ''));
        $_REQUEST["id"] = 999999;
        parent::onLoad();
    }

    public function onShow() {
        parent::onShow();
    }

    function onSubmit() {
        global $app, $conf;

        if (getValue('dochange', false)) {
            unset($_POST['dochange'], $_POST['dids']);
            foreach ($_POST as $key => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                    $_POST[$key] = $value;
                }
                //* NOCHANGE or empty == do not update
                if (strtoupper($value) == 'NOCHANGE' || strpos(strtoupper($value), 'NOCHANGE') !== false || empty($value))
                    unset($_POST[$key]);
            }
            $updated_values = $_POST;
            unset($updated_values['_csrf_key'], $updated_values['_csrf_id'], $updated_values['phpsessid']);
            foreach ($this->domain_ids as $value) {
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
                            $app->db->datalogUpdate('sogo_domains', $updated_values, 'sogo_id', $sogo_did, FALSE);
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
                                $app->db->datalogSave('sogo_domains', 'UPDATE', 'sogo_id', $sogo_did, $sogo_domain, $new_rec, FALSE);
                            } else
                                $app->log("{$sogo_domain['domain_name']} were not updated, error while saving data", LOGLEVEL_WARN);
                        }
                    } else {
                        /*
                         * new config so no update
                         * server is not set from bulk config update
                         * and therefore we do not create new configurations here
                         */
                    }
                } else
                    $app->log("mail domain with id {$value}, do not exists in database", LOGLEVEL_WARN);
            }
            echo "HEADER_REDIRECT:admin/sogo_module_settings_list.php";
            exit;
        }
    }
    
    function onShowEdit() {
        global $app, $conf;
        $app->uses('sogo_helper');
        // bestehenden Datensatz anzeigen
        if ($app->tform->errorMessage == '') {
            $domainIds = "";
            $combined_config = array();
            $sogo_config_fields = $app->sogo_helper->getDomainConfigFields();

            foreach ($this->domain_ids as $value) {
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

            $record = $combined_config;
            if (empty($record) || !is_array($record))
                $app->error($app->lng('error_no_view_permission'));
        } else {
            $record = $app->tform->encode($this->dataRecord, $this->active_tab, false);
        }

        $this->dataRecord = $record;

        // Userdaten umwandeln
        $record = $app->tform->getHTML($this->dataRecord, $this->active_tab, 'EDIT');
        $record['id'] = $this->id;

        $app->tpl->setVar($record);

        $app->tpl->setVar('dids', implode('|', $this->domain_ids));
    }

    function onShowEnd() {
        global $app;
        foreach ($this->dataRecord as $key => $rvalue) {
            $app->tpl->setVar($key . 'DBValue', $rvalue);
        }
        parent::onShowEnd();
    }

}

$app->tform_action = new tform_action();
$app->tform_action->onLoad();