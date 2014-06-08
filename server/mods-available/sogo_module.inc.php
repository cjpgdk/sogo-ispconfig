<?php

/*
 * Copyright (C) 2014 Christian M. Jensen
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
 */
 
class sogo_module {

    var $module_name = 'sogo_module';
    var $class_name = 'sogo_module';
    var $actions_available = array(
        'fake_tb_sogo_update',
        'fake_tb_sogo_delete',
        'fake_tb_sogo_insert',
    );

    function onInstall() {
        global $conf;

        return true;
    }

    function onLoad() {
        global $app;

        $app->plugins->announceEvents($this->module_name, $this->actions_available);

        $app->modules->registerTableHook('fake_tb_sogo', $this->module_name, 'process');

        $app->services->registerService('sogo', $this->module_name, 'restartSOGo');
    }

    function restartSOGo($action = 'restart') {
        global $app, $conf;
        if (file_exists($conf['init_scripts'] . '/sogo'))
            exec($conf['init_scripts'] . '/sogo ' . $action);
        else if (file_exists($conf['init_scripts'] . '/sogod'))
            exec($conf['init_scripts'] . '/sogod ' . $action);
    }

    function process($tablename, $action, $data) {
        global $app;
        switch ($tablename) {
            case 'fake_tb_sogo':
                if ($action == 'i')
                    $app->plugins->raiseEvent('fake_tb_sogo_insert', $data);
                if ($action == 'u')
                    $app->plugins->raiseEvent('fake_tb_sogo_update', $data);
                if ($action == 'd')
                    $app->plugins->raiseEvent('fake_tb_sogo_delete', $data);
                break;
        }
    }

}

?>