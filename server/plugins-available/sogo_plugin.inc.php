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
 * @link https://github.com/cmjnisse/sogo-ispconfig original source code for sogo-ispconfig
 */
class sogo_plugin {

    var $plugin_name = 'sogo_plugin';
    var $class_name = 'sogo_plugin';

    function onInstall() {
        return false;
    }

    function onLoad() {
        global $app, $conf;
        $app->uses('sogo_helper,sogo_config');
        //* check if a config even exists
        $sql = "SELECT * FROM `sogo_config` WHERE `server_id`=" . intval($conf['server_id']);
        $server_default = $app->sogo_helper->getDB(false)->queryOneRecord($sql);
        if (!$server_default) {
            $app->log('SOGo plugin: No server configuration aborting...', LOGLEVEL_DEBUG);
            return;
        }
        $app->sogo_helper->load_module_settings($conf['server_id']);

        //* check sogo config before we register events
        if (
                !isset($conf['sogo_gnu_step_defaults']) || !isset($conf['sogo_gnu_step_defaults_sogod.plist']) ||
                !isset($conf['sogo_su_command']) || !isset($conf['sogo_tool_binary']) || /* !isset($conf['sogo_binary']) || */
                !isset($conf['sogo_database_name']) || !isset($conf['sogo_database_user']) ||
                !isset($conf['sogo_database_passwd']) || !isset($conf['sogo_database_host']) ||
                !isset($conf['sogo_database_port'])
        ) {
            //* @todo add a more reliable configuration check
            $app->log('SOGo configuration variables is missing in local config', LOGLEVEL_ERROR);
        } else {
            //* TB: sogo_config
            $app->plugins->registerEvent('sogo_config_update', $this->plugin_name, 'update_sogo_config');
            $app->plugins->registerEvent('sogo_config_delete', $this->plugin_name, 'remove_sogo_config');
            $app->plugins->registerEvent('sogo_config_insert', $this->plugin_name, 'insert_sogo_config');

            //* TB: sogo_domains
            $app->plugins->registerEvent('sogo_domains_update', $this->plugin_name, 'update_sogo_domain');
            $app->plugins->registerEvent('sogo_domains_delete', $this->plugin_name, 'remove_sogo_domain');
            $app->plugins->registerEvent('sogo_domains_insert', $this->plugin_name, 'insert_sogo_domain');

            //* TB: mail_domain
            $app->plugins->registerEvent('mail_domain_delete', $this->plugin_name, 'remove_sogo_mail_domain');
            $app->plugins->registerEvent('mail_domain_insert', $this->plugin_name, 'insert_sogo_mail_domain');
            $app->plugins->registerEvent('mail_domain_update', $this->plugin_name, 'update_sogo_mail_domain');

            //* TB: mail_user
            $app->plugins->registerEvent('mail_user_delete', $this->plugin_name, 'remove_sogo_mail_user');
            $app->plugins->registerEvent('mail_user_update', $this->plugin_name, 'update_sogo_mail_user');
            $app->plugins->registerEvent('mail_user_insert', $this->plugin_name, 'insert_sogo_mail_user');

            //* TB: mail_forwarding
            $app->plugins->registerEvent('mail_forwarding_delete', $this->plugin_name, 'remove_sogo_mail_user_alias');
            $app->plugins->registerEvent('mail_forwarding_insert', $this->plugin_name, 'insert_sogo_mail_user_alias');
            $app->plugins->registerEvent('mail_forwarding_update', $this->plugin_name, 'update_sogo_mail_user_alias');

            //* TB: sogo_module
            $app->plugins->registerEvent('sogo_module_delete', $this->plugin_name, 'remove_sogo_module_settings');
            $app->plugins->registerEvent('sogo_module_insert', $this->plugin_name, 'insert_sogo_module_settings');
            $app->plugins->registerEvent('sogo_module_update', $this->plugin_name, 'update_sogo_module_settings');

            if (version_compare($conf['app_version'], '3.0.4.6', ">")) {
                //* Register for remote actions
                $app->plugins->registerAction('sogo_mail_user_sync', $this->plugin_name, 'action_mail_user_sync');
                $app->plugins->registerAction('sogo_mail_user_alias', $this->plugin_name, 'action_mail_user_alias');
                $app->plugins->registerAction('sogo_mail_domain_uid', $this->plugin_name, 'action_mail_domain');
            }
        }
    }

    //* #START# remote actions

    /**
     * remote action mail user alias update/insert/delete
     * @param string $action_name the action name (sogo_mail_user_alias)
     * @param string|array $data an serialized string or array contaning email alias data 
     * @return void
     */
    public function action_mail_user_alias($action_name, $data) {
        if (!$this->_action_get_data_array($data, 'action_mail_user_alias')) {
            return;
        }
        if ($data['event'] == "mail:mail_alias:on_after_insert") {
            $this->insert_sogo_mail_user_alias('mail_forwarding_insert', array(
                'new' => $data['dataRecord'],
                'old' => $data['oldDataRecord'],
            ));
        } else if ($data['event'] == "mail:mail_alias:on_after_update") {
            $this->update_sogo_mail_user_alias('mail_forwarding_update', array(
                'new' => $data['dataRecord'],
                'old' => $data['oldDataRecord'],
            ));
        } else if ($data['event'] == "mail:mail_alias:on_after_delete") {
            $this->remove_sogo_mail_user_alias('mail_forwarding_delete', array(
                'new' => $data['dataRecord'],
                'old' => (!empty($data['oldDataRecord']) ? $data['oldDataRecord'] : $data['dataRecord']),
            ));
        }
    }

    /**
     * remote action mail domain update/insert/delete
     * @global array $conf
     * @param string $action_name the action name (sogo_mail_domain_uid)
     * @param string|array $data an serialized string or array contaning domain data 
     * @return void
     */
    public function action_mail_domain($action_name, $data) {
        global $conf;
        if (!$this->_action_get_data_array($data, 'action_mail_domain')) {
            return;
        }
        if ($data['event'] == "mail:mail_domain:on_after_insert") {
            $this->insert_sogo_mail_domain('mail_domain_insert', array(
                'new' => $data['dataRecord'],
                'old' => $data['oldDataRecord'],
            ));
        } else if ($data['event'] == "mail:mail_domain:on_after_update") {
            if (!isset($data['dataRecord']['domain_id']) && isset($data['id']))
                $data['dataRecord']['domain_id'] = $data['id'];
            else if (!isset($data['dataRecord']['domain_id']) && isset($data['dataRecord']['id']))
                $data['dataRecord']['domain_id'] = $data['dataRecord']['id'];
            else if (!isset($data['dataRecord']['domain_id']) && isset($data['oldDataRecord']['domain_id']))
                $data['dataRecord']['domain_id'] = $data['oldDataRecord']['domain_id'];
            $this->update_sogo_mail_domain('mail_domain_update', array(
                'new' => $data['dataRecord'],
                'old' => $data['oldDataRecord'],
            ));
        } else if ($data['event'] == "mail:mail_domain:on_after_delete") {
            /*
             * remote action, 
             * so we change the server id to this server, 
             * otherwise we try to delete sogo domain on wrong server
             */
            $data['dataRecord']['server_id'] = $conf['server_id'];
            if (!empty($data['oldDataRecord']))
                $data['oldDataRecord']['server_id'] = $conf['server_id'];
            $this->remove_sogo_mail_domain('mail_domain_delete', array(
                'new' => $data['dataRecord'],
                'old' => (!empty($data['oldDataRecord']) ? $data['oldDataRecord'] : $data['dataRecord']),
            ));
        }
    }

    /**
     * remote acion for mail users sync
     * @param string $action_name the action name (sogo_mail_user_sync)
     * @param string $domain_name the domain name to sync users for
     * @return void
     */
    public function action_mail_user_sync($action_name, $domain_name) {
        global $app;
        $app->sogo_helper->sync_mail_users($domain_name);
    }

    //* #END# remote actions
    //* ##
    //* #START# SOGO MODULE SETTINGS (TB: sogo_module)

    public function update_sogo_module_settings($event_name, $data) {
        global $app, $conf;

        //$app->sogo_helper->load_module_settings($conf['server_id']); //* reload they changed
        $app->sogo_helper->module_settings->all_domains = ($data['new']['all_domains'] == 'y' ? TRUE : FALSE);
        $app->sogo_helper->module_settings->allow_same_instance = ($data['new']['allow_same_instance'] == 'y' ? TRUE : FALSE);
        $app->sogo_helper->module_settings->config_rebuild_on_mail_user_insert = ($data['new']['config_rebuild_on_mail_user_insert'] == 'y' ? TRUE : FALSE);

        //* mails on the SOGo server
        if ($db_mail_domains = $app->sogo_helper->get_mail_domain_names('y')) {
            $create_mail_domains = array();
            $ok_mail_domains = array();
            foreach ($db_mail_domains as $value) {
                if (!$app->sogo_helper->sogo_table_exists($value['domain']))
                    $create_mail_domains[] = $value['domain'];
                else
                    $ok_mail_domains[] = $value['domain'];
            }
            $db_mail_domains = array_merge($ok_mail_domains, $create_mail_domains);
        }
        //* get domains to remove
        if (isset($db_mail_domains) && is_array($db_mail_domains) && !empty($db_mail_domains))
            $remove_mail_domains = $app->sogo_helper->getDB()->queryAllRecords("SELECT * "
                    . "FROM `mail_domain` WHERE "
                    . "`domain` NOT IN ('" . implode("','", $db_mail_domains) . "')");
        else
            $remove_mail_domains = $app->sogo_helper->getDB()->queryAllRecords("SELECT * FROM `mail_domain`");

        //$app->log("Mail domains for this server: \n" . print_r($db_mail_domains, true), LOGLEVEL_DEBUG);
        //$app->log("Mail domains to remove: \n" . print_r($remove_mail_domains, true), LOGLEVEL_DEBUG);
        //* create domains
        if (isset($create_mail_domains) && is_array($create_mail_domains)) {
            foreach ($create_mail_domains as $value) {
                if (!$app->sogo_helper->sogo_table_exists($value)) {
                    $app->log("Creating domain: " . $value, LOGLEVEL_DEBUG);
                    $this->insert_sogo_mail_domain(mail_domain_insert, array(
                        'new' => array('domain' => $value),
                        'old' => array(),
                    ));
                }
            }
        }
        //* remove domains
        if (isset($remove_mail_domains) && is_array($remove_mail_domains)) {
            foreach ($remove_mail_domains as $value) {
                if ($app->sogo_helper->sogo_table_exists($value['domain'])) {
                    $app->log("Droping domain: " . $value['domain'], LOGLEVEL_DEBUG);
                    $value['server_id'] = $conf['server_id']; //* force server to this one.
                    $this->remove_sogo_mail_domain('mail_domain_delete', array(
                        'new' => $value,
                        'old' => $value,
                    ));
                } else {
                    $app->log("Not Droping domain: " . $value['domain'], LOGLEVEL_DEBUG);
                }
            }
        }

        $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
    }

    public function insert_sogo_module_settings($event_name, $data) {
        $this->update_sogo_module_settings($event_name, $data);
    }

    public function remove_sogo_module_settings($event_name, $data) {
        global $app, $conf;
        $app->log("SOGo configuration is deleted removing all domains and related configurations for SOGo", LOGLEVEL_DEBUG);
        if ($remove_mail_domains = $app->sogo_helper->getDB()->queryAllRecords("SELECT `domain`, `domain_id` FROM `mail_domain`")) {
            foreach ($remove_mail_domains as $value) {
                if ($app->sogo_helper->sogo_table_exists($value['domain'])) {
                    $domain_table = $app->sogo_helper->get_valid_sogo_table_name($value['domain']);
                    $sqlres = & $app->sogo_helper->sqlConnect();
                    if ($tmp = $sqlres->query("SELECT `c_uid` FROM `{$sqlres->escape_string($domain_table)}`;")) {
                        while ($obj = $tmp->fetch_object()) {
                            if (isset($obj->c_uid)) {
                                $app->sogo_helper->delete_mail_user($obj->c_uid);
                            }
                        }
                    }
                    $app->sogo_helper->drop_sogo_users_table($value['domain'], $value['domain_id']);
                }
            }
        }
        if (file_exists($conf['sogo_gnu_step_defaults']))
            if (@unlink($conf['sogo_gnu_step_defaults']))
                @touch($conf['sogo_gnu_step_defaults']); //* needs to be there.!

            if (file_exists($conf['sogo_gnu_step_defaults'] . ".back"))
            @unlink($conf['sogo_gnu_step_defaults'] . ".back");
        if (file_exists($conf['sogo_gnu_step_defaults'] . ".bck"))
            @unlink($conf['sogo_gnu_step_defaults'] . ".bck");

        if (file_exists($conf['sogo_gnu_step_defaults_sogod.plist']))
            if (@unlink($conf['sogo_gnu_step_defaults_sogod.plist']))
                @touch($conf['sogo_gnu_step_defaults_sogod.plist']); //* needs to be there, if it exists

            if (file_exists($conf['sogo_gnu_step_defaults_sogod.plist'] . ".back"))
            @unlink($conf['sogo_gnu_step_defaults_sogod.plist'] . ".back");

        //* try to delete .lck folder if it exists.
        $lck_dir = str_replace(".GNUstepDefaults", ".lck", $conf['sogo_gnu_step_defaults']);
        if (file_exists($lck_dir) || is_dir($lck_dir))
            @rmdir($lck_dir);

        //* 'sogoForeceRestart', stop and start memcached and SOGo
        $app->services->restartServiceDelayed('sogoForeceRestart', 'restart');
    }

    //* #END# SOGO MODULE SETTINGS (TB: sogo_config)
    //* ##
    //* #START# SOGO CONFIG (TB: sogo_config)

    public function update_sogo_config($event_name, $data) {
        global $app, $conf;
        $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
    }

    public function insert_sogo_config($event_name, $data) {
        global $app, $conf;
        $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
    }

    public function remove_sogo_config($event_name, $data) {
        global $app, $conf;
        $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
    }

    //* #END# SOGO CONFIG (TB: sogo_config)
    //* ##
    //* #START# SOGO DOMAINS (TB: sogo_domains)

    public function update_sogo_domain($event_name, $data) {
        //* for now we just do it like this.!
        $data['old'] = $data['new'];
        $this->remove_sogo_domain('sogo_domains_delete', $data);
    }

    public function insert_sogo_domain($event_name, $data) {
        //* for now we just do it like this.!
        $data['old'] = $data['new'];
        $this->remove_sogo_domain('sogo_domains_delete', $data);
    }

    /**
     * event to remove a SOGo domain config from SOGo db
     * @global app $app
     * @global array $conf
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function remove_sogo_domain($event_name, $data) {
        global $app, $conf;
        if (!isset($data['old']['domain_id']) || (intval($data['old']['domain_id']) <= 0)) {
            return;
        }
        $domain_name = (isset($data['old']['domain']) ? $data['old']['domain'] : (isset($data['old']['domain_name']) ? $data['old']['domain_name'] : ''));
        if (empty($domain_name)) {
            return;
        }
        if ($event_name == 'sogo_domains_delete') {
            $keep_table = true;
            if ($app->sogo_helper->module_settings->all_domains && $app->sogo_helper->module_settings->allow_same_instance) {
                //* allow all domains + same instance
                $keep_table = true;
            } else if (!$app->sogo_helper->module_settings->all_domains && $app->sogo_helper->module_settings->allow_same_instance) {
                //* allow only domains with sogo domain config + same instance
                $keep_table = false;
                if ($app->sogo_helper->config_exists_domain($domain_name) !== false)
                    $keep_table = true;
            } else if ($app->sogo_helper->module_settings->all_domains && !$app->sogo_helper->module_settings->allow_same_instance) {
                //* allow all domains but only for this server
                $keep_table = false;
                if ($data['old']['domain']['server_id'] == $conf['server_id'])
                    $keep_table = true;
            } else if (!$app->sogo_helper->module_settings->all_domains && !$app->sogo_helper->module_settings->allow_same_instance) {
                //* allow only domains with sogo domain config and located on this server
                $keep_table = false;
                if ($app->sogo_helper->config_exists_domain($domain_name) !== false && ($data['old']['domain']['server_id'] == $conf['server_id']))
                    $keep_table = true;
            }

            if ($app->sogo_helper->sogo_table_exists($domain_name)) {
                if ($app->sogo_helper->has_mail_users($domain_name, true) && $keep_table) {
                    //* if users still exists for domain this is only a sogo domain config removal/reset
                    $app->sogo_helper->sync_mail_users($domain_name);
                } else {
                    //* no mail users left in db [mail_user] remove sogo tables

                    $domain_table = $app->sogo_helper->get_valid_sogo_table_name($domain_name);
                    $sqlres = & $app->sogo_helper->sqlConnect();
                    if ($tmp = $sqlres->query("SELECT `c_uid` FROM `{$sqlres->escape_string($domain_table)}`;")) {
                        while ($obj = $tmp->fetch_object()) {
                            if (isset($obj->c_uid)) {
                                $app->sogo_helper->delete_mail_user($obj->c_uid);
                            }
                        }
                    }

                    $app->sogo_helper->drop_sogo_users_table($domain_name, $data['old']['domain_id']);
                }
            } else if ($app->sogo_helper->has_mail_users($domain_name, true) && $keep_table) {
                //* if users still exists for domain this is only a sogo domain config removal/reset
                $app->sogo_helper->create_sogo_table($domain_name);
            }
            $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
        }
    }

    //* #END# SOGO DOMAINS (TB: sogo_domains)
    //* ##
    //* #START# MAIL ALIASES (TB: mail_forwarding)

    /**
     * event to delete aliases from the sogo tables
     * @global app $app
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function remove_sogo_mail_user_alias($event_name, $data) {
        global $app;
        //* check event
        if ($event_name != 'mail_forwarding_delete')
            return;
        //* check this is an alias
        if (!isset($data['old']['type']) || ($data['old']['type'] != 'alias')) {
            if (!isset($data['new']['type']) || ($data['new']['type'] != 'alias'))
                return;
        }

        /**
         * @todo make single query to remove aliases (this lightens the load and the amount of sql queries)
         */
        list($source_user, $source_domain) = explode('@', $data['old']['source']);
        list($destination_user, $destination_domain) = explode('@', $data['old']['destination']);
        if ($destdomenc = $app->sogo_helper->idn_encode($destination_domain))
            $destination_domain = $destdomenc;

        $app->log("sogo_plugin::remove_sogo_mail_user_alias(): {$data['old']['source']} => {$data['old']['destination']}", LOGLEVEL_DEBUG);
        //* a simple sync should be ok
        $app->sogo_helper->sync_mail_users($destination_domain);
        return TRUE;
    }

    /**
     * event to add new alias to sogo tables
     * @global app $app
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function insert_sogo_mail_user_alias($event_name, $data) {
        global $app;
        //* check event
        if ($event_name != 'mail_forwarding_insert')
            return;
        //* check this is an alias
        if (!isset($data['new']['type']) || ($data['new']['type'] != 'alias'))
            return;

        list($source_user, $source_domain) = explode('@', $data['new']['source']);
        list($destination_user, $destination_domain) = explode('@', $data['new']['destination']);
        if ($destdomenc = $app->sogo_helper->idn_encode($destination_domain))
            $destination_domain = $destdomenc;


        //* check table and create
        if (!$app->sogo_helper->sogo_table_exists($destination_domain)) {
            $app->sogo_helper->create_sogo_table($destination_domain, false);
        }
        //* destination mail has imap access?
        if (!$app->sogo_helper->has_imap_access($data['new']['destination'])) {
            $app->log("sogo_plugin::insert_sogo_mail_user_alias(): Imap Access denied: {$data['new']['destination']}", LOGLEVEL_DEBUG);
            return;
        }
        //* create_sogo_table can fail so we check again.
        if (!$app->sogo_helper->sogo_table_exists($destination_domain)){
            $app->log("No table for domain: {$destination_domain}, so not creating alias for sogo", LOGLEVEL_DEBUG);
            return;
        }

        $app->log("sogo_plugin::insert_sogo_mail_user_alias(): {$data['new']['source']} => {$data['new']['destination']}", LOGLEVEL_DEBUG);

        //* don't sync on error
        if ($app->sogo_helper->check_alias_columns($destination_domain)) {
            $app->sogo_helper->sync_mail_users($destination_domain);
            $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
        }
        return TRUE;
    }

    /**
     * event to update mail user aliases when they get changed/updated
     * @global app $app
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function update_sogo_mail_user_alias($event_name, $data) {

        /*
         * 
         * @todo might need to add some restriction to this method to prevent constenly updating the db
         * this method is called for
         * Domain Alias     (aliasdomain)
         * Email Alias      (alias)
         * Email Forward    (forward)
         * Email Catchall   (catchall)
         * 
         */

        global $app;
        if ($event_name != 'mail_forwarding_update')
            return;

        list($old_source_user, $old_source_domain) = explode('@', $data['old']['source']);
        list($new_source_user, $new_source_domain) = explode('@', $data['new']['source']);
        list($old_destination_user, $old_destination_domain) = explode('@', $data['old']['destination']);
        list($new_destination_user, $new_destination_domain) = explode('@', $data['new']['destination']);
        if ($olddomenc = $app->sogo_helper->idn_encode($old_destination_domain))
            $old_destination_domain = $olddomenc;
        if ($newdomenc = $app->sogo_helper->idn_encode($new_destination_domain))
            $new_destination_domain = $newdomenc;
        //* check / create the required alias columns
        $app->sogo_helper->check_alias_columns($new_destination_domain);
        $is_synced = FALSE;
        /*
         * all using $app->sogo_helper->sync_mail_users
         * only done like this in case we need diferent actions on some of the changes
         */
        //* type changed
        if ($data['old']['type'] != $data['new']['type']) {
            if (!$is_synced)
                $app->sogo_helper->sync_mail_users($new_destination_domain);
            $is_synced = TRUE;
        }
        //* server changed
        if (!$is_synced && $data['old']['server_id'] != $data['new']['server_id']) {
            
        }
        //* alias changed
        if ($data['old']['source'] != $data['new']['source']) {
            if ($old_source_domain != $new_source_domain) {
                //* domain changed
            }
            if (!$is_synced)
                $app->sogo_helper->sync_mail_users($new_destination_domain);
            $is_synced = TRUE;
        }
        //* destination changed
        if ($data['old']['destination'] != $data['new']['destination']) {
            /**
             * @todo if destination changes add function to double check the alias counts on table and in ISPConfig
             */
            if ($old_destination_domain != $new_destination_domain) {
                //* domain changed
                $app->sogo_helper->sync_mail_users($old_destination_domain);
            }

            if (!$is_synced)
                $app->sogo_helper->sync_mail_users($new_destination_domain);
            $is_synced = TRUE;
        }
        //* active changed
        if ($data['old']['active'] != $data['new']['active']) {
            if (!$is_synced)
                $app->sogo_helper->sync_mail_users($new_destination_domain);
            $is_synced = TRUE;
        }
    }

    //* #END# MAIL ALIASES (TB: mail_forwarding)
    //* ##
    //* #START# MAIL USERS (TB: mail_user)

    /**
     * event to add new mail users to sogo domain table
     * @global app $app
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function insert_sogo_mail_user($event_name, $data) {
        global $app;
        //* check event
        if ($event_name != 'mail_user_insert')
            return;

        list($user, $domain) = explode('@', $data['new']['email']);

        //* only sync active domains, if not active make sure all data in sogo is gone!
        if ($app->sogo_helper->check_domain_state_drop($domain)) {
            //* a simple sync should be ok 
            $app->sogo_helper->sync_mail_users($domain);
            if ($app->sogo_helper->module_settings->config_rebuild_on_mail_user_insert) {
                $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
            }
        }
        return TRUE;
    }

    /**
     * event to update mail users in sogo table
     * @global app $app
     * @global array $conf
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function update_sogo_mail_user($event_name, $data) {
        global $app, $conf;
        if ($event_name == 'mail_user_update') {
            list($old_user, $old_domain) = explode('@', $data['old']['email']);
            list($new_user, $new_domain) = explode('@', $data['new']['email']);

            //* avoid annoying errors
            if (($app->sogo_helper->idn_decode($data['old']['email']) == $data['new']['email']) &&
                    ($app->sogo_helper->idn_encode($data['new']['email']) == $data['old']['email'])) {
                $data['new']['email'] = $app->sogo_helper->idn_encode($data['new']['email']);
                list($new_user, $new_domain) = explode('@', $data['new']['email']);
            }
            if (($app->sogo_helper->idn_encode($data['old']['email']) == $data['new']['email']) &&
                    ($app->sogo_helper->idn_decode($data['new']['email']) == $data['old']['email'])) {
                $data['old']['email'] = $app->sogo_helper->idn_decode($data['old']['email']);
                list($old_user, $old_domain) = explode('@', $data['old']['email']);
            }

            //* in reponse to user/domain changed
            if ($data['old']['email'] != $data['new']['email']) {
                $app->log("sogo_plugin::update_sogo_mail_user(): change email, OLD:{$data['old']['email']} , NEW:{$data['new']['email']}", LOGLEVEL_DEBUG);

                $sync = FALSE;
                //* make sure new domain is created
                if ($old_domain != $new_domain) {
                    $app->sogo_helper->delete_mail_user($data['old']['login']); //* remove all related to old user 
                    if (!$app->sogo_helper->sogo_table_exists($new_domain)) {
                        $app->sogo_helper->create_sogo_table($new_domain); //* also syncs all users!
                        $sync = TRUE;
                    }
                }
                //* if only username is changed
                if ($old_user != $new_user) {
                    $app->sogo_helper->delete_mail_user($data['old']['login']); //* remove all related to old user 
                }
                //* e-mail is changed so sync it all
                if ($old_domain != $new_domain)
                    $app->sogo_helper->sync_mail_users($old_domain);
                if (!$sync)
                    $app->sogo_helper->sync_mail_users($new_domain);
                $sync = TRUE;
            }

            if ($data['old']['password'] != $data['new']['password']) {
                $app->log("sogo_plugin::update_sogo_mail_user(): change password, on {$data['new']['email']}", LOGLEVEL_DEBUG);
                //* sync all based on new domain
                if (!$sync)
                    $app->sogo_helper->sync_mail_users($new_domain);
            }
        }
    }

    /**
     * event to remove a sogo user from sogo storage
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function remove_sogo_mail_user($event_name, $data) {
        global $app;
        if ($event_name == 'mail_user_delete') {
            $dom = @explode("@", $data['old']['login']);
            $app->sogo_helper->sync_mail_users(@$dom[1]);
        }
    }

    //* #END# MAIL USERS (TB: mail_user)
    //* ##
    //* #START# MAIL DOMAINS (TB: mail_domain)

    /**
     * event to create sogo domain table on new mail domains
     * @global app $app
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function insert_sogo_mail_domain($event_name, $data) {
        global $app;
        //* check event
        if ($event_name != 'mail_domain_insert')
            return;
        //* if domain has users, create related SOGo config
        if ($app->sogo_helper->has_mail_users($data['new']['domain'])) {
            $app->sogo_helper->create_sogo_table($data['new']['domain']);
            $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
        }
    }

    /**
     * event to update / configure mail domains for sogo (+ create/delete sogo domain table)
     * @global app $app
     * @global array $conf
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function update_sogo_mail_domain($event_name, $data) {
        global $app, $conf;
        if ($event_name == "mail_domain_update") {

            //* avoid annoying errors
            if ($app->sogo_helper->idn_decode($data['old']['domain']) == $data['new']['domain']) {
                $data['new']['domain'] = $app->sogo_helper->idn_encode($data['new']['domain']);
            }

            $change_domain = $change_server = FALSE;
            if ($data['old']['domain'] != $data['new']['domain']) {
                //* change domain name (sogo_domains)
                $app->sogo_helper->getDB()->query("UPDATE `sogo_domains` SET `domain_name` = '{$data['new']['domain']}', `domain_id` = '{$data['new']['domain_id']}' WHERE `domain_id` = '{$data['old']['domain']}' AND `domain_id` = '{$data['old']['domain_id']}';");

                $app->log("sogo_plugin::update_sogo_mail_domain(): change domain name [{$data['old']['domain']}] => [{$data['new']['domain']}] IN table sogo_domains", LOGLEVEL_DEBUG);

                //* change domain name (SOGO db)
                $this->remove_sogo_domain('sogo_domains_delete', $data);
                $app->sogo_helper->create_sogo_table($data['new']['domain']);
                $change_domain = TRUE;
            }
            $owner_check = $app->sogo_helper->getDB()->queryOneRecord('SELECT `sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other` FROM `sogo_domains` WHERE `domain_id`=' . intval($data['old']['domain_id']));
            if ($owner_check !== FALSE && ( (
                    ($data['old']['sys_userid'] != $data['new']['sys_userid']) ||
                    ($data['old']['sys_groupid'] != $data['new']['sys_groupid']) ||
                    ($data['old']['sys_perm_user'] != $data['new']['sys_perm_user']) ||
                    ($data['old']['sys_perm_group'] != $data['new']['sys_perm_group']) ||
                    ($data['old']['sys_perm_other'] != $data['new']['sys_perm_other'])
                    ) || (
                    ($owner_check['sys_userid'] != $data['new']['sys_userid']) ||
                    ($owner_check['sys_groupid'] != $data['new']['sys_groupid']) ||
                    ($owner_check['sys_perm_user'] != $data['new']['sys_perm_user']) ||
                    ($owner_check['sys_perm_group'] != $data['new']['sys_perm_group']) ||
                    ($owner_check['sys_perm_other'] != $data['new']['sys_perm_other'])
                    ) )
            ) {
                //* change domain owner
                $app->sogo_helper->getDB()->query("UPDATE `sogo_domains` "
                        . "SET `sys_userid` = '{$data['new']['sys_userid']}'"
                        . ", `sys_groupid` = '{$data['new']['sys_groupid']}'"
                        . ", `sys_perm_user` = '{$data['new']['sys_perm_user']}'"
                        . ", `sys_perm_group` = '{$data['new']['sys_perm_group']}'"
                        . ", `sys_perm_other` = '{$data['new']['sys_perm_other']}'"
                        . " WHERE `domain_id` = '{$data['old']['domain_id']}';");

                $app->log("sogo_plugin::update_sogo_mail_domain(): change owner of domain [{$data['new']['domain']}] IN table sogo_domains", LOGLEVEL_DEBUG);
            }

            if ($data['old']['server_id'] != $data['new']['server_id']) {
                // started using "SOGo server id" not "mail server id"
                // //* change server
                // $server_name = $app->sogo_helper->getDB()->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . intval($data['new']['server_id']));
                // $app->sogo_helper->getDB()->query("UPDATE `sogo_domains` "
                //         . "SET `server_name` = '{$server_name['server_name']}'"
                //         . ", `server_id` = '{$data['new']['server_id']}'"
                //         . " WHERE `domain_id` = '{$data['old']['domain_id']}';");
                // $app->log("sogo_plugin::update_sogo_mail_domain(): change server for domain [{$data['new']['domain']}] IN table sogo_domains to [{$server_name['server_name']}]", LOGLEVEL_DEBUG);
                //* change server setting (SOGO db) && (SOGo config)
                $change_server = TRUE;
            }

            if ($data['new']['active'] == 'n') {
                $this->remove_sogo_domain('sogo_domains_delete', $data);
            } else if ($data['new']['active'] == 'y') {
                $app->sogo_helper->create_sogo_table($data['new']['domain']);
            }
            if ($change_domain) {
                /*
                 * if domain is changed as i see it ISPConfig raises the deleted functions and inserted functions
                 * so don't think this is necessary
                 * $data['old']['domain']
                 * $data['new']['domain']
                 */
            }

            if ($change_server) {
                /*
                 * if server is changed as i see it ISPConfig raises the deleted functions and inserted functions
                 * so don't think this is necessary
                 * $data['old']['server_id']
                 * $data['new']['server_id']
                 */
            }
            $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
        }
    }

    /**
     * event to remove a mail domain's SOGo config
     * @global app $app
     * @param string $event_name
     * @param array $data array of old and new data
     */
    public function remove_sogo_mail_domain($event_name, $data) {
        global $app;
        if ($event_name == 'mail_domain_delete') {
            $app->log("Delete domain {$data['old']['domain_id']}#{$data['old']['domain']}", LOGLEVEL_DEBUG);
            if ((int) $data['old']['domain_id'] == $data['old']['domain_id'] && (intval($data['old']['domain_id']) > 0)) {
                $SOGoDomainID = $app->sogo_helper->getDB()->queryOneRecord("SELECT `sogo_id` FROM `sogo_domains` WHERE `domain_id`=" . intval($data['old']['domain_id']));
                //* delete SOGo domain config if exists
                if ($SOGoDomainID['sogo_id'] == intval($SOGoDomainID['sogo_id']) && (intval($SOGoDomainID['sogo_id']) > 0)) {
                    $app->log("Delete sogo domain config: {$SOGoDomainID['sogo_id']}", LOGLEVEL_DEBUG);
                    $app->sogo_helper->getDB()->datalogDelete('sogo_domains', 'sogo_id', $SOGoDomainID['sogo_id']);
                    $app->log("sogo_plugin::remove_sogo_mail_domain(): delete SOGo config for domain: {$data['old']['domain_id']}#{$data['old']['domain']}", LOGLEVEL_DEBUG);
                } else {
                    $app->log("No SOGo config, create fake configuration datalog delete", LOGLEVEL_DEBUG);
                    //* no config exists for domain, force a datalog to call event sogo_domains_delete
                    $app->sogo_helper->getDB()->datalogSave('sogo_domains', 'DELETE', 'sogo_id', -1, $data['old'], $data['new'], TRUE);
                }
            } else {
                $app->log("Delete domain validation error: {$data['old']['domain_id']}#{$data['old']['domain']}", LOGLEVEL_DEBUG);
            }
        }
    }

    //* #END# MAIL DOMAINS (TB: mail_domain)
    //* ##
    //* ## Helper methods

    /**
     * procces data from remote actions into an array
     * @global app $app
     * @param mixed $data
     * @return boolean
     */
    private function _action_get_data_array(& $data, $a) {
        global $app;
        if (!is_array($data)) {
            try {
                $data = unserialize($data);
            } catch (Exception $ex) {
                $app->log("{$a}('', DATA_ARRAY): DATA_ARRAY is not a valid serialized string" . PHP_EOL . "Exception: " . $ex->getMessage() . PHP_EOL . "Trace: " . $ex->getTraceAsString(), LOGLEVEL_DEBUG);
                return false;
            }
        }
        if (is_array($data) && (!isset($data['oldDataRecord']) || !isset($data['dataRecord']))) {
            $app->log("{$a}('', DATA_ARRAY): DATA_ARRAY is not valid" . (!isset($data['oldDataRecord']) ? ' : Missing index oldDataRecord ' : '') . (!isset($data['dataRecord']) ? ' : Missing index dataRecord ' : ''), LOGLEVEL_DEBUG);
            return false;
        }
        return true;
    }

    public function __destruct() {
        global $app;
        unset($app->sogo_helper);
    }

}
