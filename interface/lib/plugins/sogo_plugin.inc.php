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
        global $app, $conf;
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

        /*
          !Don't see the need for it, it uses server_id so auto synced to correct server
          //* sogo_domains
          $app->plugin->registerEvent('admin:sogo_domains:on_after_insert', $this->plugin_name, 'mail_user_on_after_insert');
          $app->plugin->registerEvent('admin:sogo_domains:on_after_update', $this->plugin_name, 'mail_user_on_after_update');
          $app->plugin->registerEvent('admin:sogo_domains:on_after_delete', $this->plugin_name, 'mail_user_on_after_delete');
         */

        //* mail_user 
        $app->plugin->registerEvent('mail:mail_user:on_after_insert', $this->plugin_name, 'mail_user_on_after_insert');
        $app->plugin->registerEvent('mail:mail_user:on_after_update', $this->plugin_name, 'mail_user_on_after_update');
        $app->plugin->registerEvent('mail:mail_user:on_after_delete', $this->plugin_name, 'mail_user_on_after_delete');

        //* mail_domain
        $app->plugin->registerEvent('mail:mail_domain:on_after_insert', $this->plugin_name, 'mail_domain_on_after_insert');
        $app->plugin->registerEvent('mail:mail_domain:on_after_update', $this->plugin_name, 'mail_domain_on_after_update');
        $app->plugin->registerEvent('mail:mail_domain:on_after_delete', $this->plugin_name, 'mail_domain_on_after_delete');

        //* mail_alias
        $app->plugin->registerEvent('mail:mail_alias:on_after_insert', $this->plugin_name, 'mail_alias_on_after_insert');
        $app->plugin->registerEvent('mail:mail_alias:on_after_update', $this->plugin_name, 'mail_alias_on_after_update');
        $app->plugin->registerEvent('mail:mail_alias:on_after_delete', $this->plugin_name, 'mail_alias_on_after_delete');
    }
    
    /*
      mail_alias event functions
     */

    function mail_alias_on_after_insert($event_name, $page_form) {

        $destination = @$page_form->dataRecord['destination'];
        if ($destination === false || empty($destination))
            return $app->log("Missing destination for alias ID#" . (isset($page_form->dataRecord['id']) ? $page_form->dataRecord['id'] : -1), LOGLEVEL_DEBUG);
        $source = @$page_form->dataRecord['source'];
        if ($source === false || empty($source))
            return $app->log("Missing source for alias ID#" . (isset($page_form->dataRecord['id']) ? $page_form->dataRecord['id'] : -1), LOGLEVEL_DEBUG);
        list($destination_user, $destination_domain) = explode('@', $destination);


        $event_data = array(
            'event' => $event_name,
            'dataRecord' => is_array($page_form->dataRecord) ? $page_form->dataRecord : array(),
            'oldDataRecord' => is_array($page_form->oldDataRecord) ? $page_form->oldDataRecord : array(),
        );

        $this->create_mail_alias_event($event_data, $destination_domain);
    }

    function mail_alias_on_after_update($event_name, $page_form) {

        $destination = @$page_form->dataRecord['destination'];
        if ($destination === false || empty($destination))
            return $app->log("Missing destination for alias ID#" . (isset($page_form->dataRecord['id']) ? $page_form->dataRecord['id'] : -1), LOGLEVEL_DEBUG);
        $source = @$page_form->dataRecord['source'];
        if ($source === false || empty($source))
            return $app->log("Missing source for alias ID#" . (isset($page_form->dataRecord['id']) ? $page_form->dataRecord['id'] : -1), LOGLEVEL_DEBUG);
        list($destination_user, $destination_domain) = explode('@', $destination);


        $event_data = array(
            'event' => $event_name,
            'dataRecord' => is_array($page_form->dataRecord) ? $page_form->dataRecord : array(),
            'oldDataRecord' => is_array($page_form->oldDataRecord) ? $page_form->oldDataRecord : array(),
        );

        $this->create_mail_alias_event($event_data, $destination_domain);
    }

    function mail_alias_on_after_delete($event_name, $page_form) {

        $destination = @$page_form->dataRecord['destination'];
        if ($destination === false || empty($destination))
            return $app->log("Missing destination for alias ID#" . (isset($page_form->dataRecord['id']) ? $page_form->dataRecord['id'] : -1), LOGLEVEL_DEBUG);
        $source = @$page_form->dataRecord['source'];
        if ($source === false || empty($source))
            return $app->log("Missing source for alias ID#" . (isset($page_form->dataRecord['id']) ? $page_form->dataRecord['id'] : -1), LOGLEVEL_DEBUG);
        list($destination_user, $destination_domain) = explode('@', $destination);


        $event_data = array(
            'event' => $event_name,
            'dataRecord' => is_array($page_form->dataRecord) ? $page_form->dataRecord : array(),
            'oldDataRecord' => is_array($page_form->oldDataRecord) ? $page_form->oldDataRecord : array(),
        );

        $this->create_mail_alias_event($event_data, $destination_domain);
    }

    function create_mail_alias_event($event_data, $domain_name) {
        global $app;
        $_server_id = -1;
        if ($data = $app->db->queryOneRecord("SELECT * FROM sogo_domains WHERE domain_name = '{$app->db->quote($domain_name)}'")) {
            $_server_id = $data['server_id'];
            if (!$this->is_mail_server($data['server_id'])) {

                $app->log('Register remote action [sogo_mail_user_alias] for alias ' . $data['dataRecord']['source'] . ', on server ' . $_server_id, LOGLEVEL_DEBUG);
                $sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
                        "VALUES (" . (int) $_server_id . ", " . time() . ", 'sogo_mail_user_alias', '" . $app->db->quote(serialize($event_data)) . "', 'pending', '')";
                $app->db->query($sql);
            }
        }
        unset($data);

        //* SOGo server with 'allow_same_instance == y', if any
        $sql = "SELECT `server_id` FROM `sogo_module` WHERE allow_same_instance='y' AND `server_id`!='{$app->db->quote(intval($_server_id))}'";
        if ($tmp = $app->db->queryAllRecords($sql)) {
            foreach ($tmp as $sid) {

                $app->log('Register remote action [sogo_mail_user_alias] for alias ' . $data['dataRecord']['source'] . ', on server ' . $sid, LOGLEVEL_DEBUG);
                $sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
                        "VALUES (" . (int) $sid . ", " . time() . ", 'sogo_mail_user_alias', '" . $app->db->quote(serialize($event_data)) . "', 'pending', '')";
                $app->db->query($sql);
            }
        }
    }

    /*
      mail_domain event functions
     */

    function mail_domain_on_after_insert($event_name, $page_form) {

        $domain = (isset($page_form->dataRecord['domain']) ? $page_form->dataRecord['domain'] :
                        (isset($page_form->oldDataRecord['domain']) ? $page_form->oldDataRecord['domain'] : ''));

        $event_data = array(
            'event' => $event_name,
            'dataRecord' => is_array($page_form->dataRecord) ? $page_form->dataRecord : array(),
            'oldDataRecord' => is_array($page_form->oldDataRecord) ? $page_form->oldDataRecord : array(),
        );
        $this->create_mail_domain_event($event_data, $domain);
    }

    function mail_domain_on_after_update($event_name, $page_form) {

        $domain = (isset($page_form->dataRecord['domain']) ? $page_form->dataRecord['domain'] :
                        (isset($page_form->oldDataRecord['domain']) ? $page_form->oldDataRecord['domain'] : ''));

        $event_data = array(
            'event' => $event_name,
            'dataRecord' => is_array($page_form->dataRecord) ? $page_form->dataRecord : array(),
            'oldDataRecord' => is_array($page_form->oldDataRecord) ? $page_form->oldDataRecord : array(),
        );
        $this->create_mail_domain_event($event_data, $domain);
    }

    function mail_domain_on_after_delete($event_name, $page_form) {

        $domain = (isset($page_form->dataRecord['domain']) ? $page_form->dataRecord['domain'] :
                        (isset($page_form->oldDataRecord['domain']) ? $page_form->oldDataRecord['domain'] : ''));

        $event_data = array(
            'event' => $event_name,
            'dataRecord' => is_array($page_form->dataRecord) ? $page_form->dataRecord : array(),
            'oldDataRecord' => is_array($page_form->oldDataRecord) ? $page_form->oldDataRecord : array(),
        );
        $this->create_mail_domain_event($event_data, $domain);
    }

    function create_mail_domain_event($event_data, $domain_name) {
        global $app;

        $_server_id = -1;
        if ($data = $app->db->queryOneRecord("SELECT * FROM sogo_domains WHERE domain_name = '{$app->db->quote($domain_name)}'")) {
            $_server_id = $data['server_id'];
            if (!$this->is_mail_server($data['server_id'])) {

                $app->log('Register remote action [sogo_mail_domain_uid] for domain ' . $domain_name . ', on server ' . $_server_id, LOGLEVEL_DEBUG);
                $sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
                        "VALUES (" . (int) $_server_id . ", " . time() . ", 'sogo_mail_domain_uid', '" . $app->db->quote(serialize($event_data)) . "', 'pending', '')";
                $app->db->query($sql);
            }
        }
        unset($data);

        //* SOGo server with 'allow_same_instance == y', if any
        $sql = "SELECT `server_id` FROM `sogo_module` WHERE allow_same_instance='y' AND `server_id`!='{$app->db->quote(intval($_server_id))}'";
        if ($tmp = $app->db->queryAllRecords($sql)) {
            foreach ($tmp as $sid) {

                $app->log('Register remote action [sogo_mail_domain_uid] for domain ' . $domain_name . ', on server ' . $sid, LOGLEVEL_DEBUG);
                $sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
                        "VALUES (" . (int) $sid . ", " . time() . ", 'sogo_mail_domain_uid', '" . $app->db->quote(serialize($event_data)) . "', 'pending', '')";
                $app->db->query($sql);
            }
        }
    }

    /*
      mail_user event functions
     */

    function mail_user_on_after_delete($event_name, $page_form) {
        global $app;
        /*
          $app->log('mail_user_on_after_delete: ' . $event_name .
          PHP_EOL . 'dataRecord: ' . print_r($page_form->dataRecord, TRUE), LOGLEVEL_DEBUG);
         */
        $new_mail = $new_mail_user = $new_mail_domain = "";
        if (isset($page_form->dataRecord['email']) && (strpos($page_form->dataRecord['email'], '@') !== false)) {
            $new_mail = $page_form->dataRecord['email'];
            list($new_mail_user, $new_mail_domain) = explode('@', $new_mail);
        }
        if (!empty($new_mail)) {
            $this->create_mail_user_sync_event($new_mail_domain);
        } else {
            $app->log("sogo_plugin::mail_user_on_after_insert(): no mail domain found", LOGLEVEL_DEBUG);
        }
    }

    function mail_user_on_after_insert($event_name, $page_form) {
        global $app;
        /*
          $app->log('mail_user_on_after_update: ' . $event_name .
          PHP_EOL . 'dataRecord: ' . print_r($page_form->dataRecord, TRUE), LOGLEVEL_DEBUG);
         */
        $new_mail = $new_mail_user = $new_mail_domain = "";
        if (isset($page_form->dataRecord['email']) && (strpos($page_form->dataRecord['email'], '@') !== false)) {
            $new_mail = $page_form->dataRecord['email'];
            list($new_mail_user, $new_mail_domain) = explode('@', $new_mail);
        }
        if (!empty($new_mail)) {
            $this->create_mail_user_sync_event($new_mail_domain);
        } else {
            $app->log("sogo_plugin::mail_user_on_after_insert(): no mail domain found", LOGLEVEL_DEBUG);
        }
    }

    function mail_user_on_after_update($event_name, $page_form) {
        global $app;
        /*
          $app->log('mail_user_on_after_update: ' . $event_name .
          PHP_EOL . 'dataRecord: ' . print_r($page_form->dataRecord, TRUE) .
          PHP_EOL . 'oldDataRecord: ' . print_r($page_form->oldDataRecord, TRUE), LOGLEVEL_DEBUG);
         */
        $new_mail = $new_mail_user = $new_mail_domain = "";
        if (isset($page_form->dataRecord['email']) && (strpos($page_form->dataRecord['email'], '@') !== false)) {
            $new_mail = $page_form->dataRecord['email'];
            list($new_mail_user, $new_mail_domain) = explode('@', $new_mail);
        }
        $old_mail = $old_mail_user = $old_mail_domain = "";
        if (isset($page_form->oldDataRecord['email']) && (strpos($page_form->oldDataRecord['email'], '@') !== false)) {
            $old_mail = $page_form->oldDataRecord['email'];
            list($old_mail_user, $old_mail_domain) = explode('@', $old_mail);
        }
        if ((!empty($new_mail) && !empty($old_mail)) && ($new_mail != $old_mail)) {
            //* mail change
            $this->create_mail_user_sync_event($new_mail_domain);
            $this->create_mail_user_sync_event($old_mail_domain);
        } else {
            if (!empty($new_mail)) {
                $this->create_mail_user_sync_event($new_mail_domain);
            } else if (!empty($old_mail)) {
                $this->create_mail_user_sync_event($old_mail_domain);
            } else {
                $app->log("sogo_plugin::mail_user_on_after_update(): no mail domain found", LOGLEVEL_DEBUG);
            }
        }
    }

    function create_mail_user_sync_event($domain_name) {
        global $app;
        $app->uses('sogo_helper');
        $_server_id = -1;
        if ($data = $app->db->queryOneRecord("SELECT * FROM sogo_domains WHERE domain_name = '{$app->db->quote($domain_name)}'")) {
            $_server_id = $data['server_id'];
            if (!$this->is_mail_server($data['server_id'])) {

                $app->log('Register remote action [sogo_mail_user_sync] for domain ' . $domain_name . ', on server ' . $data['server_id'], LOGLEVEL_DEBUG);
                $sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
                        "VALUES (" . (int) $data['server_id'] . ", " . time() . ", 'sogo_mail_user_sync', '{$domain_name}', 'pending', '')";
                $app->db->query($sql);

                //* fix for missing user sync. 
                if (strtolower(trim($app->sogo_helper->getSOGoModuleConf($data['server_id'], 'config_rebuild_on_mail_user_insert'))) == 'y')
                    $app->db->datalogSave('sogo_domains', 'update', 'domain_name', $domain_name, $data, $data, true);
            }
        }
        unset($data);
        //* SOGo server with 'allow_same_instance == y', if any
        $sql = "SELECT `server_id` FROM `sogo_module` WHERE allow_same_instance='y' AND `server_id`!='{$app->db->quote(intval($_server_id))}'";
        if ($tmp = $app->db->queryAllRecords($sql)) {
            foreach ($tmp as $sid) {

                $app->log('Register remote action [sogo_mail_user_sync] for domain ' . $domain_name . ', on server ' . $sid, LOGLEVEL_DEBUG);
                $sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
                        "VALUES (" . (int) $sid . ", " . time() . ", 'sogo_mail_user_sync', '{$domain_name}', 'pending', '')";
                $app->db->query($sql);

                //* fix for missing user sync. 
                if (strtolower(trim($app->sogo_helper->getSOGoModuleConf($sid, 'config_rebuild_on_mail_user_insert'))) == 'y') {
                    if ($data = $app->db->queryOneRecord("SELECT * FROM sogo_domains WHERE domain_name = '{$app->db->quote($domain_name)}'")) {
                        $app->db->datalogSave('sogo_domains', 'update', 'domain_name', $domain_name, $data, $data, true);
                    }
                    unset($data);
                }
            }
        }
    }

    /*
      Helper functions
     */

    private function is_mail_server($server_id) {
        global $app;
        if ($server_cnf = $app->db->queryOneRecord('SELECT mail_server FROM server WHERE server_id = ' . intval($server_id))) {
            return intval($server_cnf['mail_server']) == 1 ? true : false;
        }
        return false;
    }

}
