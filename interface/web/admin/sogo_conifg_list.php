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

$list_def_file = "list/sogo_server.list.php";

//* unset old edit id!
unset($_SESSION['s']['module']["sogo_conifg_server_id"]);

$app->auth->check_module_permissions('admin');
$app->uses('listform_actions,sogo_helper');

class listform_action extends listform_actions {

    /** @global app $app */
    public function onLoad() {
        global $app;
        $app->uses('tpl,listform,tform');

        $_system_servers = $app->sogo_helper->listSystemServers(FALSE);
        $system_servers = array();
        foreach ($_system_servers as $value) {
            $system_servers[] = array('system_server_id' => $value->id, 'system_server_name' => $value->name);
        }
        $app->tpl->setLoop('system_servers', $system_servers);
        unset($_system_servers, $system_servers);

        parent::onLoad();
    }

    /** @global app $app */
    public function onShow() {
        global $app;
        if (isset($_GET['msg'])) {
            $app->tpl->setVar('msg', sprintf($app->listform->wordbook['REBUILD_TRIGGERED'], $_GET['server_n']));
        }
        parent::onShow();
    }

}

$app->listform_action = new listform_action();
$app->listform_action->onLoad();
