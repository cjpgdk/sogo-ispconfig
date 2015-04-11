<?php

/**
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
 *  @author Christian M. Jensen <christian@cmjscripter.net>
 *  @copyright 2014 Christian M. Jensen
 *  @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */
class sogo_plugin {

    var $plugin_name = 'sogo_plugin';
    var $class_name = 'sogo_plugin';

    function onLoad() {
        global $app;
        //Register for the events
        $app->plugin->registerEvent('mail:mail_user:on_after_insert', $this->plugin_name, 'register_sogo_for_sogo');
        $app->plugin->registerEvent('mail:mail_user:on_after_update', $this->plugin_name, 'register_sogo_for_sogo');
        $app->plugin->registerEvent('mail:mail_user:on_after_delete', $this->plugin_name, 'register_sogo_for_sogo');
    }

    /** @global app $app */
    function register_sogo_for_sogo($event_name, $page_form) {
        global $app;
        $app->uses('sogo_helper');
        if (strtolower(trim($app->sogo_helper->getSOGoModuleConf(1, 'config_rebuild_on_mail_user_insert'))) == 'y') {
            $email_domain = explode('@', $page_form->dataRecord['email']);
            if (isset($email_domain[1]))
                $email_domain = $email_domain[1];
            else {
                $app->log('Email domain from email faild: ' . $page_form->dataRecord['email'], LOGLEVEL_WARN);
                return;
            }
            $sql = "SELECT * FROM sogo_domains WHERE domain_name = '{$app->db->quote($email_domain)}'";
            $data = $app->db->queryOneRecord($sql);
            if ($data['server_id'] == $conf['server_id']) {
                $app->log('Data server is is the same as this server.!', LOGLEVEL_DEBUG);
                return; //* handled by server plugin, if server is SOGo else it's invalid..!
            }
            if (property_exists($app, 'dbmaster') && method_exists($app->dbmaster, 'datalogSave')) {
                $app->dbmaster->datalogSave('sogo_domains', 'update', 'domain_name', $email_domain, $data, $data, true);
            } else if (property_exists($app, 'db') && method_exists($app->db, 'datalogSave')) {
                $app->db->datalogSave('sogo_domains', 'update', 'domain_name', $email_domain, $data, $data, true);
            } else {
                $app->log('No database connection object found', LOGLEVEL_DEBUG);
            }
        } else {
            $app->log('Rebuild SOGo on mail user update/insert/delete [Disabled]', LOGLEVEL_DEBUG);
        }
    }

}
