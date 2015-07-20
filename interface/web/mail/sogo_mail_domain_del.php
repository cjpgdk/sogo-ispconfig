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



$_grant_access = false;
if (!$app->auth->is_admin()) {
    //* validate access.
    $app->tform->loadFormDef($tform_def_file);
    if ($app->tform->checkPerm(intval($_REQUEST['id']), 'u') == false)
        $app->error($app->tform->lng('error_no_delete_permission'), $liste["file"], true);

    $_grant_access = true;
} else {
    //* if admin then just run
    $_grant_access = true;
}
if ($_grant_access) {
    $app->load('tform_actions');

    class tform_action extends tform_actions {

        static private $load_domain_id = null;
        static private $load_domain_name = null;
        static private $load_server_id = null;

        public function onLoad() {
            global $app, $tform_def_file;

            $this->id = intval($_REQUEST["id"]);
            $app->tform->loadFormDef($tform_def_file);
            $_SESSION["s"]["form"]["tab"] = $app->tform->formDef['tab_default'];
            $this->activeTab = $app->tform->formDef['tab_default'];

            $tmp_sogo_domain = $app->db->queryOneRecord('SELECT `sogo_id`,`domain_id`,`domain_name`,`server_id`,`server_name` FROM `sogo_domains` WHERE `sogo_id`=' . $this->id);
            self::$load_domain_id = $tmp_sogo_domain['domain_id'];
            self::$load_domain_name = $tmp_sogo_domain['domain_name'];
            self::$load_server_id = $tmp_sogo_domain['server_id'];
        }

        public function onBeforeUpdate() {
            global $app;

            if ($app->tform->errorMessage == '') {
                // fix server_name must be sogo server name
                if ($result = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . intval($this->dataRecord['server_id']))) {
                    if (isset($result['server_name']))
                        $this->dataRecord['server_name'] = $result['server_name'];
                } else
                    $app->tform->errorMessage .= $app->tform->lng("errmsg_server_id_not_found") . "<br />\r\n";

                // fix domain_name must be sogo server name
                if ($result = $app->db->queryOneRecord('SELECT `domain` FROM `mail_domain` WHERE `domain_id`=' . intval($this->dataRecord['domain_id']))) {
                    if (isset($result['domain']))
                        $this->dataRecord['domain_name'] = $result['domain'];
                } else
                    $app->tform->errorMessage .= $app->tform->lng("errmsg_domain_id_not_found") . "<br />\r\n";
            }

            if ($app->tform->errorMessage == '') {
                if ($result = $app->db->queryOneRecord('SELECT * FROM `sogo_config` WHERE `server_id`=' . intval($this->dataRecord['server_id']))) {
                    foreach ($result as $key => $value) {
                        if ($key != 'SOGoCustomXML' && $key != 'SOGoSuperUsernames' &&
                                $key != 'sogo_id' && $key != 'sys_userid' && $key != 'sys_groupid' && $key != 'sys_perm_user' &&
                                $key != 'sys_perm_group' && $key != 'sys_perm_other' && $key != 'server_id' && $key != 'server_name' && $key != 'domain_id' && $key != 'domain_name')
                            $this->dataRecord[$key] = $value;
                    }
                }
            }

            parent::onBeforeUpdate();
        }

        public function onAfterUpdate() {
            $this->__fixDomainOwner();
            parent::onAfterUpdate();
        }

        /** @global app $app */
        private function __fixDomainOwner() {
            global $app;
            $result = $app->db->queryOneRecord('SELECT `sys_userid`,`sys_groupid`,`sys_perm_user`,`sys_perm_group`,`sys_perm_other` FROM `mail_domain` WHERE `domain_id`=' . intval(self::$load_domain_id));
            if (isset($result['sys_userid']) && isset($result['sys_groupid']) && isset($result['sys_perm_user']) && isset($result['sys_perm_group']) && isset($result['sys_perm_other'])) {
                $app->db->query("UPDATE `sogo_domains` SET "
                        . "`sys_userid` = '" . intval($result['sys_userid']) . "', "
                        . "`sys_groupid` = '" . intval($result['sys_groupid']) . "', "
                        . "`sys_perm_user` = '{$result['sys_perm_user']}', "
                        . "`sys_perm_group` = '{$result['sys_perm_group']}', "
                        . "`sys_perm_other` = '{$result['sys_perm_other']}' "
                        . "WHERE `sogo_id` ='" . /* self::$domain_config_index */ $this->id . "' AND `domain_id` ='" . self::$load_domain_id . "';");
            }
        }

    }

    $app->tform_action = new tform_action();
    $app->tform_action->onLoad();

    if ($_config = $app->db->queryOneRecord("SELECT * FROM `sogo_domains` WHERE `sogo_id`=" . $app->tform_action->id)) {
        $app->tform_action->dataRecord = $_config;
        $app->tform_action->dataRecord['SOGoCustomXML'] = '';
    }
    if (method_exists($app->auth, 'csrf_token_get')) {
        $csrf_token = $app->auth->csrf_token_get('sogo_domains');
        $_POST['_csrf_id'] = $csrf_token['csrf_id'];
        $_POST['_csrf_key'] = $csrf_token['csrf_key'];
        $app->tform_action->dataRecord['_csrf_id'] = $_POST['_csrf_id'];
        $app->tform_action->dataRecord['_csrf_key'] = $_POST['_csrf_key'];
    }
    $app->tform_action->onSubmit();
}



//header("Location: " . $liste["file"]);
