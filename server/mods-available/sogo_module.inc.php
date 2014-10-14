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
        //* TB: sogo_config
        'sogo_config_update',
        'sogo_config_delete',
        'sogo_config_insert',
        //* TB: sogo_domains
        'sogo_domains_update',
        'sogo_domains_delete',
        'sogo_domains_insert',
        //* TB: sogo_module
        'sogo_module_update',
        'sogo_module_delete',
        'sogo_module_insert',
    );

    function onInstall() {
        global $conf;
        return true;
    }

    function onLoad() {
        global $app;

        $app->plugins->announceEvents($this->module_name, $this->actions_available);
        
        $app->modules->registerTableHook('sogo_config', $this->module_name, 'process');
        
        $app->modules->registerTableHook('sogo_domains', $this->module_name, 'process');
        
        $app->modules->registerTableHook('sogo_module', $this->module_name, 'process');

        $app->services->registerService('sogo', $this->module_name, 'restartSOGo');

        $app->services->registerService('sogoForeceRestart', $this->module_name, 'foreceRestart');
    }

    function restartSOGo($action = 'restart') {
        global $app, $conf;
        if (file_exists($conf['init_scripts'] . '/sogo'))
            exec($conf['init_scripts'] . '/sogo ' . $action);
        else if (file_exists($conf['init_scripts'] . '/sogod'))
            exec($conf['init_scripts'] . '/sogod ' . $action);
    }
    
    //* in some rare cases we need to stop and start sogo and memcached to make it all work
    function foreceRestart($action=NULL) {
        
        //* Stop sogo
        if (file_exists($conf['init_scripts'] . '/sogo'))
            exec($conf['init_scripts'] . '/sogo stop');
        else if (file_exists($conf['init_scripts'] . '/sogod'))
            exec($conf['init_scripts'] . '/sogod stop');
        
        //* Stop memcached
        if (file_exists($conf['init_scripts'] . '/memcached'))
            exec($conf['init_scripts'] . '/memcached stop');
        
        //* Start memcached
        if (file_exists($conf['init_scripts'] . '/memcached'))
            exec($conf['init_scripts'] . '/memcached start');
        
        //* Start sogo
        if (file_exists($conf['init_scripts'] . '/sogo'))
            exec($conf['init_scripts'] . '/sogo start');
        else if (file_exists($conf['init_scripts'] . '/sogod'))
            exec($conf['init_scripts'] . '/sogod start');
    }

    function process($tablename, $action, $data) {
        global $app;
        switch ($tablename) {
            case 'sogo_config':
                if ($action == 'i')
                    $app->plugins->raiseEvent('sogo_config_insert', $data);
                if ($action == 'u')
                    $app->plugins->raiseEvent('sogo_config_update', $data);
                if ($action == 'd')
                    $app->plugins->raiseEvent('sogo_config_delete', $data);
                break;
            case 'sogo_domains':
                if ($action == 'i')
                    $app->plugins->raiseEvent('sogo_domains_insert', $data);
                if ($action == 'u')
                    $app->plugins->raiseEvent('sogo_domains_update', $data);
                if ($action == 'd')
                    $app->plugins->raiseEvent('sogo_domains_delete', $data);
                break;
            case 'sogo_module':
                if ($action == 'i')
                    $app->plugins->raiseEvent('sogo_module_insert', $data);
                if ($action == 'u')
                    $app->plugins->raiseEvent('sogo_module_update', $data);
                if ($action == 'd')
                    $app->plugins->raiseEvent('sogo_module_delete', $data);
                break;
        }
    }
}