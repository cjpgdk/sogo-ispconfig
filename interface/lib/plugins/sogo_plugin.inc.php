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
 * @author Christian M. Jensen <christian@cmjscripter.net>
 * @copyright 2015 Christian M. Jensen
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */
class sogo_plugin {

    var $plugin_name = 'sogo_plugin';
    var $class_name = 'sogo_plugin';

    function onLoad() {
        global $app;
        //Register for the events
        /*
          [MODULE]:[FORM NAME]:on_insert_save
          [MODULE]:[FORM NAME]:on_after_insert
         * 
          [MODULE]:[FORM NAME]:on_check_delete
          [MODULE]:[FORM NAME]:on_before_delete
          [MODULE]:[FORM NAME]:on_after_delete
         * 
          [MODULE]:[FORM NAME]:on_update_save
          [MODULE]:[FORM NAME]:on_after_update
         */

        //* mail_user 
        $app->plugin->registerEvent('mail:mail_user:on_after_insert', $this->plugin_name, 'register_change_for_sogo');
        $app->plugin->registerEvent('mail:mail_user:on_after_update', $this->plugin_name, 'register_change_for_sogo');
        $app->plugin->registerEvent('mail:mail_user:on_after_delete', $this->plugin_name, 'register_change_for_sogo');

        //* mail_domain
        $app->plugin->registerEvent('mail:mail_domain:on_after_insert', $this->plugin_name, 'register_change_for_sogo');
        $app->plugin->registerEvent('mail:mail_domain:on_after_update', $this->plugin_name, 'register_change_for_sogo');
        $app->plugin->registerEvent('mail:mail_domain:on_after_delete', $this->plugin_name, 'register_change_for_sogo');

        //* mail_alias
        $app->plugin->registerEvent('mail:mail_alias:on_after_insert', $this->plugin_name, 'register_change_for_sogo');
        $app->plugin->registerEvent('mail:mail_alias:on_after_update', $this->plugin_name, 'register_change_for_sogo');
        $app->plugin->registerEvent('mail:mail_alias:on_after_delete', $this->plugin_name, 'register_change_for_sogo');
    }

    /** @global app $app */
    function register_change_for_sogo($event_name, $page_form) {
        global $app, $conf;
        $app->uses('sogo_helper');

        //* get vars for mail user events
        if (strpos($event_name, 'mail_user') !== false) {
            //* get mail domain.
            $email_domain = explode('@', $page_form->dataRecord['email']);
            if (isset($email_domain[1]))
                $email_domain = $email_domain[1];
            else {
                $app->log('Email domain from email failed: ' . $page_form->dataRecord['email'], LOGLEVEL_WARN);
                return;
            }
            $sql = "SELECT * FROM sogo_domains WHERE domain_name = '{$app->sogo_helper->getDB()->quote($email_domain)}'";
            $data = $app->sogo_helper->getDB()->queryOneRecord($sql);
        }

        //* get vars for mail domain events
        if (strpos($event_name, 'mail_domain') !== false) {
            $domain = (isset($page_form->dataRecord['domain']) ? $page_form->dataRecord['domain'] :
                            (isset($page_form->oldDataRecord['domain']) ? $page_form->oldDataRecord['domain'] : ''));
            //* check if SOGo i handled by this server, or if we create a new event for other server
            $sql = "SELECT * FROM sogo_domains WHERE domain_name = '{$app->sogo_helper->getDB()->quote($domain)}'";
            $data = $app->sogo_helper->getDB()->queryOneRecord($sql);
        }

        //* get vars for mail alias events
        if (strpos($event_name, 'mail_alias') !== false) {
            
        }

        //* check if SOGo is handled by this server, or if we create a new event for other server
        if (isset($data['server_id']) && $data['server_id'] == $conf['server_id']) {
            $app->log('Data server is the same as this server.!', LOGLEVEL_DEBUG);
            return;
        } else if (!isset($data['server_id'])) {
            //* in cases where no SOGo config exists announce the new domain to all servers with allow_same_instance active
            $sogo_servers = array();
            $sql = "SELECT `server_id` FROM `sogo_module` WHERE allow_same_instance='y'";
            $tmp = $app->sogo_helper->getDB()->queryAllRecords($sql);
            if (is_array($tmp)) {
                $sogo_servers = $tmp;
                unset($tmp);
            } else
                return;
        }

        //* handle events for remote SOGo server
        switch (strtolower($event_name)) {
            //* mail_domain insert / update / delete
            case "mail:mail_alias:on_after_update":
            /*
              [INTERFACE]: page_action Object
              (
              [id] => 3
              [activeTab] =>
              [dataRecord] => Array
              (
              [destination] => sadf@sadsddg.ld
              [active] => y
              [id] => 3
              [type] => alias
              [next_tab] =>
              [phpsessid] => 8qdeqragqj8f2alrh9k0l9ih06
              [source] => dsfgff@sadsddg.ld
              [server_id] => 1
              )
             * 
              [oldDataRecord] => Array
              (
              [forwarding_id] => 3
              [sys_userid] => 1
              [sys_groupid] => 0
              [sys_perm_user] => riud
              [sys_perm_group] => riud
              [sys_perm_other] =>
              [server_id] => 1
              [source] => dsfg@sadsddg.ld
              [destination] => sadf@sadsddg.ld
              [type] => alias
              [active] => y
              )

              )
            */
            case "mail:mail_alias:on_after_delete":
                /*
                 [id] => 3
    [activeTab] => 
    [dataRecord] => Array
        (
            [forwarding_id] => 3
            [sys_userid] => 1
            [sys_groupid] => 0
            [sys_perm_user] => riud
            [sys_perm_group] => riud
            [sys_perm_other] => 
            [server_id] => 1
            [source] => dsfgff@sadsddg.ld
            [destination] => sadf@sadsddg.ld
            [type] => alias
            [active] => y
        )

    [oldDataRecord] => 
                 */
            case "mail:mail_alias:on_after_insert":
                /*
                  [id] => 3
                  [activeTab] =>
                  [dataRecord] => Array
                  (
                  [destination] => sadf@sadsddg.ld
                  [active] => y
                  [id] =>
                  [type] => alias
                  [next_tab] =>
                  [phpsessid] => 8qdeqragqj8f2alrh9k0l9ih06
                  [source] => dsfg@sadsddg.ld
                  [server_id] => 1
                  )
                 * 
                  [oldDataRecord] =>
                 */
                $app->log(print_r($page_form, true), LOGLEVEL_DEBUG);
                break;
            //* mail_domain insert / update / delete
            case "mail:mail_domain:on_after_update":
            case "mail:mail_domain:on_after_delete":
            case "mail:mail_domain:on_after_insert":
                if (!isset($data['server_id'])) {
                    foreach ($sogo_servers as $key => $value) {
                        $this->create_mail_domain_event(array(
                            'event' => $event_name,
                            'dataRecord' => is_array($page_form->dataRecord) ? $page_form->dataRecord : array(), /* fail safe */
                            'oldDataRecord' => is_array($page_form->oldDataRecord) ? $page_form->oldDataRecord : array(), /* fail safe */
                                ), $value['server_id']);
                    }
                } else {
                    $this->create_mail_domain_event(array(
                        'event' => $event_name,
                        'dataRecord' => is_array($page_form->dataRecord) ? $page_form->dataRecord : array(),
                        'oldDataRecord' => is_array($page_form->oldDataRecord) ? $page_form->oldDataRecord : array(),
                            ), $data['server_id']);
                }
                break;
            //* mail_user insert / update / delete
            case "mail:mail_user:on_after_insert": {
                    if (strtolower(trim($app->sogo_helper->getSOGoModuleConf($data['server_id'], 'config_rebuild_on_mail_user_insert'))) == 'y') {
                        $app->sogo_helper->getDB()->datalogSave('sogo_domains', 'update', 'domain_name', $email_domain, $data, $data, true);
                    } else {
                        if (!isset($data['server_id'])) {
                            foreach ($sogo_servers as $key => $value) {
                                $this->create_mail_user_sync_event($email_domain, $value['server_id']);
                            }
                        } else {
                            $app->log('Rebuild SOGo on mail user insert Disabled', LOGLEVEL_DEBUG);
                            $this->create_mail_user_sync_event($email_domain, $data['server_id']);
                        }
                    }
                }
                break;
            case "mail:mail_user:on_after_update":
            case "mail:mail_user:on_after_delete":
                if (!isset($data['server_id'])) {
                    foreach ($sogo_servers as $key => $value) {
                        $this->create_mail_user_sync_event($email_domain, $value['server_id']);
                    }
                } else {
                    $this->create_mail_user_sync_event($email_domain, $data['server_id']);
                }
                break;
            default:
                $app->log('Unknown event: ' . $event_name, LOGLEVEL_DEBUG);
                break;
        }
    }

    function create_mail_domain_event($data, $server_id) {
        global $app;
        $app->log('Register remote action [sogo_mail_domain_uid] for domain ' . $data['dataRecord']['domain'] . ', on server ' . $server_id, LOGLEVEL_DEBUG);
        $sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
                "VALUES (" . (int) $server_id . ", " . time() . ", 'sogo_mail_domain_uid', '" . $app->sogo_helper->getDB()->quote(serialize($data)) . "', 'pending', '')";
        $app->sogo_helper->getDB()->query($sql);
    }

    function create_mail_user_sync_event($domain_name, $server_id) {
        global $app;
        $app->log('Register remote action [sogo_mail_user_sync] for domain ' . $domain_name . ', on server ' . $server_id, LOGLEVEL_DEBUG);
        $sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
                "VALUES (" . (int) $server_id . ", " . time() . ", 'sogo_mail_user_sync', '{$domain_name}', 'pending', '')";
        $app->sogo_helper->getDB()->query($sql);
    }

}
