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
    //* for this version (Update 10) only we check if admin is allowed 
    $app->auth->check_security_permissions('admin_allow_server_services');
}

$app->uses('tpl,tform,functions');
$app->load('tform_actions,sogo_helper');

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
                $this->_server_id = intval($_GET['sid']);
                $this->_server_name = $result['server_name'];
            }
        }
        parent::onLoad();
    }

    /** @global app $app */
    public function onShowEnd() {
        global $app;
        if (isset($_GET['sid']) && $this->_server_id !== FALSE && $this->_server_name !== FALSE) {
            $app->tpl->setVar('server_id', $this->_server_id);
            $app->tpl->setVar('server_name', $this->_server_name);
        }
        parent::onShowEnd();
    }

}

$page = new tform_action();
$page->onLoad();
