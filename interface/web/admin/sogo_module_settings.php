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

$tform_def_file = "form/sogo_module.tform.php";

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
    /** @global app $app */
    public function onShowEnd() {
        global $app;
        
        //* custom tab, needs custom data :o\
        if ($this->active_tab == "override") {
            //* list of mail domains
            $result = $app->db->queryAllRecords("SELECT `mail_domain`.*, `sogo_domains`.server_id as sogo_server_id FROM `mail_domain` LEFT JOIN `sogo_domains` ON `mail_domain`.`domain_id`=`sogo_domains`.`domain_id` WHERE `mail_domain`.active='y'");
            $app->tpl->setLoop('mail_domains', $result);
            
            $result = $app->db->queryAllRecords("SELECT `server_id`,`server_name` FROM `sogo_config` ORDER BY server_name");
            $app->tpl->setLoop('sogo_servers', $result);
        }
        parent::onShowEnd();
    }
}

$page = new tform_action();
$page->onLoad();