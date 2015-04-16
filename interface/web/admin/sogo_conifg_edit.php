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

$tform_def_file = "form/sogo_config.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$app->auth->check_module_permissions('admin');
if (method_exists($app->auth, 'check_security_permissions')) {
    $app->auth->check_security_permissions('admin_allow_server_services');
} else {
    if (!$app->auth->is_admin())
        die('only allowed for administrators.');
}

$app->uses('tpl,tform,functions');
$app->load('tform_actions');

class tform_action extends tform_actions {

    static private $_server_id = FALSE;
    static private $_server_name = FALSE;

    /** @global app $app */
    public function onLoad() {
        global $app;
        if (isset($_GET['sid'])) {
            $result = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . intval($_GET['sid']));
            if (!isset($result['server_name'])) {
                //* server do not exists.!
                echo "HEADER_REDIRECT:admin/sogo_conifg_list.php";
                exit;
            } else {
                self::$_server_id = intval($_GET['sid']);
                self::$_server_name = $result['server_name'];
            }
        }
        parent::onLoad();
    }

    /** @global app $app */
    public function onShowEnd() {
        global $app;
        if (isset($_GET['sid']) && self::$_server_id !== FALSE && self::$_server_name !== FALSE) {
            $app->tpl->setVar('server_id', self::$_server_id);
            $app->tpl->setVar('server_name', self::$_server_name);
        }
        parent::onShowEnd();
    }
    
    public function onAfterUpdate() {
        $this->createModuleConf();
        parent::onAfterUpdate();
    }
    public function onAfterInsert() {
        $this->createModuleConf();
        parent::onAfterInsert();
    }
    /** @global app $app */
    private function createModuleConf() {
        global $app;
        if($this->id <= 0)
            return;
        $tmp = $app->db->queryOneRecord("SELECT * FROM `sogo_module` WHERE `server_id`=".intval($this->dataRecord['server_id']));
        $insert_data = array();
        $insert_data['server_id']=$this->dataRecord['server_id'];
        $insert_data['sys_userid']=1;
        $insert_data['sys_groupid']=0;
        $insert_data['sys_perm_user']="ru";
        $insert_data['sys_perm_group']="ru";
        $insert_data['sys_perm_other']="";
        if(!isset($tmp['smid']) && method_exists($app->db, 'datalogInsert'))
            $app->db->datalogInsert('sogo_module', $insert_data, 'smid');
        unset($tmp);
    }

}

$page = new tform_action();
$page->onLoad();
