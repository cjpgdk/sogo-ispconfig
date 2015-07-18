<?php

/*
 * Copyright (C) 2015 Christian M. Jensen
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

$list_def_file = "list/sogo_config_permissions.list.php";

$app->auth->check_module_permissions('admin');
$app->uses('listform_actions');

class listform_action extends listform_actions {

    /** @global app $app */
    public function prepareDataRow($rec) {
        global $app;
        $is_global = $rec['scpi_is_global'];
        $rec = parent::prepareDataRow($rec);
        if ($rec['scpi_clients'] == '*' && $rec['scpi_type'] == 'Client') {
            $rec['scpi_clients'] = $app->listform->wordbook['TXT_ALL_CLIENTS'];
        } else if ($rec['scpi_clients'] == '*' && $rec['scpi_type'] == 'Reseller') {
            $rec['scpi_clients'] = $app->listform->wordbook['TXT_ALL_RESELLERS'];
        } else {
            $str_new = "";
            $client_ids = explode(',', $rec['scpi_clients']);
            if (is_array($client_ids) && count($client_ids) > 0) {
                foreach ($client_ids as $client_id) {
                    if ($client = $app->db->queryOneRecord("SELECT `contact_name`,`username` FROM `client` WHERE `client_id`=" . intval($client_id))) {
                        if (isset($client['contact_name']) && isset($client['username'])) {
                            $str_new .= $client['contact_name'] . " : " . $client['username'] . "<br>";
                        }
                    }
                }
            }
            if (!empty($str_new))
                $rec['scpi_clients'] = $str_new;
        }
        $rec['is_global'] = $is_global;
        $rec['scpi_type'] = $app->listform->wordbook['TXT_' . strtoupper($rec['scpi_type'])];
        return $rec;
    }

}

$app->listform_action = new listform_action();
$app->listform_action->onLoad();
