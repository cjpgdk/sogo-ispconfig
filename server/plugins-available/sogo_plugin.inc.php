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

class sogo_plugin {
    /*
     * @todo make use of the same column name for all emails not mix it with c_uid, c_imaplogin c_uid is master and should be the only column to check
     */

    var $plugin_name = 'sogo_plugin';
    var $class_name = 'sogo_plugin';

    /**
     * used to avoid duplicate queries on multi data updates in one session
     * @var array 
     */
    static private $_queryHash = array();

    function onInstall() {
        return false;
    }

    function onLoad() {
        global $app, $conf;
        $app->uses('sogo_helper,sogo_config');
        $app->sogo_helper->load_module_settings($conf['server_id']);

        //* check sogo config before we register events
        if (
                !isset($conf['sogo_gnu_step_defaults']) || !isset($conf['sogo_gnu_step_defaults_sogod.plist']) ||
                !isset($conf['sogo_su_command']) || !isset($conf['sogo_tool_binary']) || /* !isset($conf['sogo_binary']) || */
                !isset($conf['sogo_database_name']) || !isset($conf['sogo_database_user']) || !isset($conf['sogo_database_passwd']) || !isset($conf['sogo_database_host']) || !isset($conf['sogo_database_port'])
        ) {
            $app->sogo_helper->logWarn('SOGo configuration variables is missing in local config');
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
            $app->plugins->registerEvent('sogo_module_insert', $this->plugin_name, 'remove_sogo_module_settings');
            $app->plugins->registerEvent('sogo_module_update', $this->plugin_name, 'insert_sogo_module_settings');
            $app->plugins->registerEvent('sogo_module_delete', $this->plugin_name, 'update_sogo_module_settings');
        }
    }

    //* #START# SOGO MODULE SETTINGS (TB: sogo_module)

    public function update_sogo_module_settings($event_name, $data) {
        global $app, $conf;
        $app->sogo_helper->load_module_settings($conf['server_id']); //* reload they changed
    }

    public function insert_sogo_module_settings($event_name, $data) {
        //* should NEVER be called!
        /*
          global $app, $conf;
          $app->sogo_helper->load_module_settings($conf['server_id']); //* reload they changed
         */
    }

    public function remove_sogo_module_settings($event_name, $data) {
        //* should NEVER be called!
        /*
          global $app, $conf;
          $app->sogo_helper->load_module_settings($conf['server_id']); //* reload they changed
         */
    }

    //* #END# SOGO MODULE SETTINGS (TB: sogo_config)
    //* ##
    //* #START# SOGO CONFIG (TB: sogo_config)

    public function update_sogo_config($event_name, $data) {
        $method = "sogo_plugin::update_sogo_config():";
        $this->__buildSOGoConfig($method);
    }

    public function insert_sogo_config($event_name, $data) {
        $method = "sogo_plugin::insert_sogo_config():";
        $this->__buildSOGoConfig($method);
    }

    public function remove_sogo_config($event_name, $data) {
        $method = "sogo_plugin::remove_sogo_config():";
        $this->__buildSOGoConfig($method);
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
        if (!isset($data['old']['domain_id']) && (intval($data['old']['domain_id']) <= 0)) {
            return;
        }
        $domain_name = (isset($data['old']['domain']) ? $data['old']['domain'] : (isset($data['old']['domain_name']) ? $data['old']['domain_name'] : ''));
        if (empty($domain_name)) {
            return;
        }
        if ($app->sogo_helper->isEqual($event_name, 'sogo_domains_delete')) {
            if ($app->sogo_helper->sogoTableExists($domain_name)) {
                if ($app->sogo_helper->has_mail_users($domain_name, true)) {
                    //* if users still exists for domain this is only a sogo domain config removal/reset
                    $this->__syncMailUsers($domain_name);
                } else {
                    //* no users left in db remove sogo tables
                    $app->sogo_helper->dropSOGoUsersTable($domain_name, $data['old']['domain_id']);
                }
            } else if ($app->sogo_helper->has_mail_users($domain_name, true)) {
                //* if users still exists for domain this is only a sogo domain config removal/reset
                $this->__create_sogo_table($domain_name);
            }
            $method = "sogo_plugin::remove_sogo_domain():";
            $this->__buildSOGoConfig($method);
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
        if (!$app->sogo_helper->isEqual($event_name, 'mail_forwarding_delete'))
            return;
        //* check this is an alias
        if (!$app->sogo_helper->isEqual($data['old']['type'], 'alias'))
            return;

        /**
         * @todo make single query to remove aliases (this lightens the load and the amount of sql queries)
         */
        list($source_user, $source_domain) = explode('@', $data['old']['source']);
        list($destination_user, $destination_domain) = explode('@', $data['old']['destination']);

        $app->sogo_helper->logDebug("sogo_plugin::remove_sogo_mail_user_alias(): {$data['old']['source']} => {$data['old']['destination']}");
        //* a simple sync should be ok 
        $this->__syncMailUsers($destination_domain);
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
        if (!$app->sogo_helper->isEqual($event_name, 'mail_forwarding_insert'))
            return;
        //* check this is an alias
        if (!$app->sogo_helper->isEqual($data['new']['type'], 'alias'))
            return;

        list($source_user, $source_domain) = explode('@', $data['new']['source']);
        list($destination_user, $destination_domain) = explode('@', $data['new']['destination']);

        $app->sogo_helper->logDebug("sogo_plugin::insert_sogo_mail_user_alias(): {$data['new']['source']} => {$data['new']['destination']}");

        //* don't sync on error
        if ($app->sogo_helper->check_alias_columns($destination_domain)) {
            $this->__syncMailUsers($destination_domain);
            $method = "sogo_plugin::insert_sogo_mail_user_alias():";
            $this->__buildSOGoConfig($method);
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
         * might need to add some restriction to this method to prevent constenly updating the db
         * this method is called for
         * Domain Alias     (aliasdomain)
         * Email Alias      (alias)
         * Email Forward    (forward)
         * Email Catchall   (catchall)
         * 
         */

        global $app;
        if (!$app->sogo_helper->isEqual($event_name, 'mail_forwarding_update'))
            return;

        list($old_source_user, $old_source_domain) = explode('@', $data['old']['source']);
        list($new_source_user, $new_source_domain) = explode('@', $data['new']['source']);
        list($old_destination_user, $old_destination_domain) = explode('@', $data['old']['destination']);
        list($new_destination_user, $new_destination_domain) = explode('@', $data['new']['destination']);
        $app->sogo_helper->check_alias_columns($new_destination_domain);
        $is_synced = FALSE;
        /*
         * all using __syncMailUsers
         * only done like this in case we need diferent actions on some of the changes
         */
        //* type changed
        if (!$app->sogo_helper->isEqual($data['old']['type'], $data['new']['type'])) {
            if (!$is_synced)
                $this->__syncMailUsers($new_destination_domain);
            $is_synced = TRUE;
        }
        //* server changed
        if (!$is_synced && !$app->sogo_helper->isEqual($data['old']['server_id'], $data['new']['server_id'])) {
            
        }
        //* alias changed
        if (!$app->sogo_helper->isEqual($data['old']['source'], $data['new']['source'])) {
            if (!$app->sogo_helper->isEqual($old_source_domain, $new_source_domain)) {
                //* domain changed
            }
            if (!$is_synced)
                $this->__syncMailUsers($new_destination_domain);
            $is_synced = TRUE;
        }
        //* destination changed
        if (!$app->sogo_helper->isEqual($data['old']['destination'], $data['new']['destination'])) {
            /**
             * @todo if destination changes add function to double check the alias counts on table and in ISPConfig
             */
            if (!$app->sogo_helper->isEqual($old_destination_domain, $new_destination_domain)) {
                //* domain changed
                $this->__syncMailUsers($old_destination_domain);
            }

            if (!$is_synced)
                $this->__syncMailUsers($new_destination_domain);
            $is_synced = TRUE;
        }
        //* active changed
        if (!$app->sogo_helper->isEqual($data['old']['active'], $data['new']['active'])) {
            if (!$is_synced)
                $this->__syncMailUsers($new_destination_domain);
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
        if (!$app->sogo_helper->isEqual($event_name, 'mail_user_insert'))
            return;

        list($user, $domain) = explode('@', $data['new']['email']);

        //* only sync active domains, if not active make sure all data in sogo is gone!
        if ($this->__checkStateDropDomain($domain)) {
            //* a simple sync should be ok 
            $this->__syncMailUsers($domain);
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
        if ($app->sogo_helper->isEqual($event_name, 'mail_user_update')) {
            list($old_user, $old_domain) = explode('@', $data['old']['email']);
            list($new_user, $new_domain) = explode('@', $data['new']['email']);
            //* in reponse to user/domain changed
            if ($data['old']['email'] != $data['new']['email']) {
                $app->sogo_helper->logDebug("sogo_plugin::update_sogo_mail_user(): change email, OLD:{$data['old']['email']} , NEW:{$data['new']['email']}");
                /*
                  we do this in "$this->remove_sogo_domain(...);"
                  $this->__deleteMailUser($data['old']['login']);
                 */
                $sync = FALSE;
                //* make sure new domain is created
                if ($old_domain != $new_domain) {
                    $this->__deleteMailUser($data['old']['login']); //* remove all related to old user 
                    if (!$app->sogo_helper->sogoTableExists($new_domain)) {
                        $this->__create_sogo_table($new_domain); //* allso syncs all users!
                        $sync = TRUE;
                    }
                }
                //* if only username is changed
                if ($old_user != $new_user) {
                    $this->__deleteMailUser($data['old']['login']); //* remove all related to old user 
                }
                //* e-mail is changed so sync it all
                if ($old_domain != $new_domain)
                    $this->__syncMailUsers($old_domain);
                if (!$sync)
                    $this->__syncMailUsers($new_domain);
                $sync = TRUE;
            }

            if ($data['old']['password'] != $data['new']['password']) {
                $app->sogo_helper->logDebug("sogo_plugin::update_sogo_mail_user(): change password, on {$data['new']['email']}");
                //* sync all based on new domain
                if (!$sync)
                    $this->__syncMailUsers($new_domain);
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
        if ($app->sogo_helper->isEqual($event_name, 'mail_user_delete'))
            $this->__deleteMailUser($data['old']['login']);
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
        if (!$app->sogo_helper->isEqual($event_name, 'mail_domain_insert'))
            return;

        if ($app->sogo_helper->has_mail_users($data['new']['domain'])) {
            $this->__create_sogo_table($data['new']['domain']);
            $method = "sogo_plugin::insert_sogo_mail_domain():";
            $this->__buildSOGoConfig($method);
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

            $change_domain = $change_server = FALSE;
            if ($data['old']['domain'] != $data['new']['domain']) {
                //* change domain name (sogo_domains)
                $app->db->query("UPDATE `sogo_domains` SET `domain_name` = '{$data['new']['domain']}' WHERE `domain_name` = '{$data['old']['domain']}' AND `domain_id` = '{$data['old']['domain_id']}';");

                $app->sogo_helper->logDebug("sogo_plugin::update_sogo_mail_domain(): change domain name [{$data['old']['domain']}] => [{$data['new']['domain']}] IN table sogo_domains");

                //* change domain name (SOGO db)
                $this->remove_sogo_domain('sogo_domains_delete', $data);
                $this->__create_sogo_table($data['new']['domain']);
                $change_domain = TRUE;
            }
            $owner_check = $app->db->queryOneRecord('SELECT `sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other` FROM `sogo_domains` WHERE `domain_id`=' . intval($data['old']['domain_id']));
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
                /*
                 * One would expect it function like any other domain update 
                 * BUT NO NO, no call to plugins if owner is changed!! 
                 */
                $app->db->query("UPDATE `sogo_domains` "
                        . "SET `sys_userid` = '{$data['new']['sys_userid']}'"
                        . ", `sys_groupid` = '{$data['new']['sys_groupid']}'"
                        . ", `sys_perm_user` = '{$data['new']['sys_perm_user']}'"
                        . ", `sys_perm_group` = '{$data['new']['sys_perm_group']}'"
                        . ", `sys_perm_other` = '{$data['new']['sys_perm_other']}'"
                        . " WHERE `domain_id` = '{$data['old']['domain_id']}';");

                $app->sogo_helper->logDebug("sogo_plugin::update_sogo_mail_domain(): change domain owner for domain [{$data['new']['domain']}] IN table sogo_domains");
            }

            if ($data['old']['server_id'] != $data['new']['server_id']) {
                //* change domain server
                $server_name = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . intval($data['new']['server_id']));
                $app->db->query("UPDATE `sogo_domains` "
                        . "SET `server_name` = '{$server_name['server_name']}'"
                        . ", `server_id` = '{$data['new']['server_id']}'"
                        . " WHERE `domain_id` = '{$data['old']['domain_id']}';");
                $app->sogo_helper->logDebug("sogo_plugin::update_sogo_mail_domain(): change server for domain [{$data['new']['domain']}] IN table sogo_domains to [{$server_name['server_name']}]");


                //* change server setting (SOGO db) && (SOGo config)
                $change_server = TRUE;
            }

            if ($data['new']['active'] == 'n') {
                $this->remove_sogo_domain('sogo_domains_delete', $data);
            } else if ($data['new']['active'] == 'y') {
                $this->__create_sogo_table($data['new']['domain']);
            }
            if ($change_domain) {
                /*
                 * if domain is changed as is see it ISPConfig raises the deleted functions and inserted functions
                 * so don't think this is necessary
                 * $data['old']['domain']
                 * $data['new']['domain']
                 */
            }

            if ($change_server) {
                /*
                 * if server is changed as is see it ISPConfig raises the deleted functions and inserted functions
                 * so don't think this is necessary
                 * $data['old']['server_id']
                 * $data['new']['server_id']
                 */
            }
            $method = "sogo_plugin::update_sogo_mail_domain():";
            $this->__buildSOGoConfig($method);
        }
    }

    /**
     * event to remove a mail domain's SOGo config
     * @global app $app
     * @param string $event_name
     * @param array $data array of old and new data
     * @todo Added call to rebuild SOGo config.!
     */
    public function remove_sogo_mail_domain($event_name, $data) {
        global $app;
        if ($event_name == 'mail_domain_delete') {
            if ((int) $data['old']['domain_id'] == $data['old']['domain_id'] && (intval($data['old']['domain_id']) > 0)) {
                $SOGoDomainID = $app->db->queryOneRecord("SELECT `sogo_id` FROM `sogo_domains` WHERE `domain_id`=" . intval($data['old']['domain_id']));
                //* delete SOGo domain config if exists
                if ($SOGoDomainID['sogo_id'] == intval($SOGoDomainID['sogo_id']) && (intval($SOGoDomainID['sogo_id']) > 0)) {
                    $app->db->datalogDelete('sogo_domains', 'sogo_id', $SOGoDomainID['sogo_id']);
                    $app->sogo_helper->logDebug("sogo_plugin::remove_sogo_mail_domain(): delete SOGo config for domain: {$data['old']['domain_id']}#{$data['old']['domain']}");
                } else {
                    /*
                     * if no config exists force a datalog to call event sogo_domains_delete
                     * this ensures all tables in sogo db is removed as well and keeping the plugin functionality of ISPConfig
                     * 
                     * IMPORTANT KEEP index == -1 (avoid deleting an existing records)
                     */
                    $app->db->datalogSave('sogo_domains', 'DELETE', 'sogo_id', -1, $data['old'], $data['new'], TRUE);
                }
            }
        }
    }

    //* #END# MAIL DOMAINS (TB: mail_domain)
    //* ##
    //* ## Helper methods

    /**
     * create mail domain table and sync mail user for use with SOGo
     * @global app $app
     * @global array $conf
     * @param string $domain_name
     * @return boolean
     */
    private function __create_sogo_table($domain_name) {
        global $app, $conf;

        if (!$app->sogo_helper->has_mail_users($domain_name, true)) {
            //* dont create no users
            $app->sogo_helper->logDebug("sogo_plugin::__create_sogo_table(): Refusing to create table for domain: {$domain_name}, NO USERS");
            return;
        }
        if ($app->sogo_helper->sogoTableExists($domain_name)) {
            $app->sogo_helper->logDebug("sogo_plugin::__create_sogo_table(): SOGo table exists for domain: {$domain_name}");
            return $this->__syncMailUsers($domain_name);
        }

        $sql = "
CREATE TABLE IF NOT EXISTS `{$app->sogo_helper->getValidSOGoTableName($domain_name)}` (
  `c_uid` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_cn` text CHARACTER SET utf8 NOT NULL,
  `c_name` varchar(500) CHARACTER SET utf8 NOT NULL,
  `mail` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_imaplogin` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_sievehostname` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_imaphostname` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_domain` varchar(255) CHARACTER SET utf8 NOT NULL,
  `c_password` varchar(255) CHARACTER SET utf8 NOT NULL";

        //* build up the mail aliases
        $acount_n = (int) $app->sogo_helper->get_max_alias_count($domain_name, 'n'); //* none active
        $acount_y = (int) $app->sogo_helper->get_max_alias_count($domain_name, 'y'); //* active
        $a_cnt = (int) ($acount_n + $acount_y);
        if ($a_cnt > 0) {
            //* append alias sql
            for ($index = 0; $index < $a_cnt; $index++) {
                $sql .= ",
    `alias_{$index}` varchar(500) CHARACTER SET utf8 NOT NULL";
            }
        }

        //* end sql statement
        $sql .= ",
  KEY `c_uid` (`c_uid`(333))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $sqlres = & $app->sogo_helper->sqlConnect();
        $result = $sqlres->query($sql) ? TRUE : FALSE;
        $app->log("sogo_plugin::__create_sogo_table(): add SOGo table for domain: {$domain_name}" . (!$result ? "\n\tERROR\t\n{$sql}" : ""), ($result ? LOGLEVEL_DEBUG : LOGLEVEL_ERROR));
        $result &= $this->__syncMailUsers($domain_name);
        return $result;
    }

    /**
     * sync all mail users and aliases for a given domain name
     * @global app $app
     * @param string $domain_name
     * @param boolean $imap_enabled if set to false will sync all email addresses, is set to true will only sync email addresses with imap enabled
     * @return boolean
     */
    private function __syncMailUsers($domain_name, $imap_enabled = true) {
        global $app;
        if (!$this->__checkStateDropDomain($domain_name))
            return false; //* do nothing
        //* create domain table if it do not exists
        if (!$app->sogo_helper->sogoTableExists($domain_name)) {
            $this->__create_sogo_table($domain_name);
        }
        $emails = $app->db->queryAllRecords("SELECT * FROM `mail_user` WHERE `email` LIKE '%@{$domain_name}'" . ($imap_enabled ? "AND `disableimap` = 'n'" : ""));
        if (!empty($emails)) {
            $domain_config = $app->sogo_helper->getDomainConfig($domain_name, true);
            if (!$domain_config || !is_array($domain_config)) {
                $app->sogo_helper->logError("SOGo Sync Mail Users - Unable to fetch the domain config for domain [{$domain_name}]");
                return false;
            }
            $domain_config['SOGoSieveServer'] = str_replace('{SERVERNAME}', $domain_config['server_name'], $domain_config['SOGoSieveServer']);
            $domain_config['SOGoIMAPServer'] = str_replace('{SERVERNAME}', $domain_config['server_name'], $domain_config['SOGoIMAPServer']);
            $_tmpSQL = array('users' => array(), 'alias' => array());
            foreach ($emails as $email) {
                if ($this->__sogo_mail_user_exists($email['login'], "{$app->sogo_helper->getValidSOGoTableName($domain_name)}")) {
                    $_tmpSQL['users'][] = "UPDATE `{$app->sogo_helper->getValidSOGoTableName($domain_name)}` SET "
                            . " `c_uid` = '{$app->sogo_helper->dbEscapeString($email['login'])}' ,"
                            . " `c_cn` = '{$app->sogo_helper->dbEscapeString($email['name'])}' ,"
                            . " `c_name` = '{$app->sogo_helper->dbEscapeString($email['login'])}' ,"
                            . " `mail` = '{$app->sogo_helper->dbEscapeString($email['email'])}' ,"
                            . " `c_imaplogin` = '{$app->sogo_helper->dbEscapeString($email['login'])}' ,"
                            . " `c_sievehostname` = '{$app->sogo_helper->dbEscapeString($domain_config['SOGoSieveServer'])}' ,"
                            . " `c_imaphostname` = '{$app->sogo_helper->dbEscapeString($domain_config['SOGoIMAPServer'])}' ,"
                            . " `c_domain` = '{$app->sogo_helper->dbEscapeString($domain_name)}' ,"
                            . " `c_password` = '{$app->sogo_helper->dbEscapeString($email['password'])}' "
                            . " WHERE `c_uid`='{$app->sogo_helper->dbEscapeString($email['login'])}';";
                } else {
                    $_tmpSQL['users'][] = "INSERT INTO `{$app->sogo_helper->getValidSOGoTableName($domain_name)}` "
                            . "(`c_uid`, `c_cn`, `c_name`, `mail`, `c_imaplogin`, `c_sievehostname`, `c_imaphostname`, `c_domain`, `c_password`) "
                            . "VALUES "
                            . "("
                            . "'{$app->sogo_helper->dbEscapeString($email['login'])}', "
                            . "'{$app->sogo_helper->dbEscapeString($email['name'])}', "
                            . "'{$app->sogo_helper->dbEscapeString($email['login'])}', "
                            . "'{$app->sogo_helper->dbEscapeString($email['email'])}', "
                            . "'{$app->sogo_helper->dbEscapeString($email['login'])}', "
                            . "'{$app->sogo_helper->dbEscapeString($domain_config['SOGoSieveServer'])}', "
                            . "'{$app->sogo_helper->dbEscapeString($domain_config['SOGoIMAPServer'])}', "
                            . "'{$app->sogo_helper->dbEscapeString($domain_name)}', "
                            . "'{$app->sogo_helper->dbEscapeString($email['password'])}'"
                            . ");";
                }
                $mail_aliases = $app->db->queryAllRecords("SELECT `source` FROM `mail_forwarding` WHERE `destination` = '{$app->sogo_helper->dbEscapeString($email['login'])}' AND `type`='alias' AND `active`='y';");
                //* get alias columns in table for domain
                $dtacount = (int) $app->sogo_helper->getSOGoTableAliasColumnCount($domain_name);

                $aliasSQL = "UPDATE `{$app->sogo_helper->getValidSOGoTableName($domain_name)}` SET ";
                //* only do alias update if a column exists for it 
                if ($dtacount > 0) {
                    $ac = 0;
                    foreach ($mail_aliases as $key => $value) {
                        $aliasSQL .= " `alias_{$ac}` = '{$app->sogo_helper->dbEscapeString($value['source'])}' ,";
                        $ac++;
                        //* must be a better way but, need some results here so break on max alias columns in tb
                        if ($dtacount == $ac)
                            break;
                    }
                    $acount_n = (int) $app->sogo_helper->get_max_alias_count($domain_name, 'n'); //* none active
                    $acount_y = (int) $app->sogo_helper->get_max_alias_count($domain_name, 'y'); //* active
                    $a_cnt = (int) ($acount_n + $acount_y);
                    //* if mail_forward table holds more aliases than columns in sogo table limit to number in sogo table
                    if ($a_cnt > $dtacount) {
                        $a_cnt = $dtacount;
                    } else {
                        $a_cnt = ($a_cnt < $dtacount ? $dtacount : $a_cnt);
                    }

                    for ($ac; $ac < $a_cnt; $ac++) {
                        $aliasSQL .= " `alias_{$ac}` = '' ,";
                    }
                    $_tmpSQL['alias'][] = trim($aliasSQL, ',')
                            . " WHERE "
                            . " `c_uid` = '{$app->sogo_helper->dbEscapeString($email['login'])}' AND"
                            . " `c_cn` = '{$app->sogo_helper->dbEscapeString($email['name'])}' AND"
                            . " `c_name` = '{$app->sogo_helper->dbEscapeString($email['login'])}' AND"
                            . " `mail` = '{$app->sogo_helper->dbEscapeString($email['email'])}' AND"
                            . " `c_imaplogin` = '{$app->sogo_helper->dbEscapeString($email['login'])}' AND"
                            . " `c_sievehostname` = '{$app->sogo_helper->dbEscapeString($domain_config['SOGoSieveServer'])}' AND"
                            . " `c_imaphostname` = '{$app->sogo_helper->dbEscapeString($domain_config['SOGoIMAPServer'])}' AND"
                            . " `c_domain` = '{$app->sogo_helper->dbEscapeString($domain_name)}' AND"
                            . " `c_password` = '{$app->sogo_helper->dbEscapeString($email['password'])}';";
                }
                /*
                 * server_id
                 * name
                 * disableimap
                 * disablesieve
                 * disablesieve-filter
                 */
            }
            $sqlres = & $app->sogo_helper->sqlConnect();
            foreach ($_tmpSQL['users'] as $value) {
                $_queryHash = md5($value); //* avoid multiple of the same query
                if (in_array($_queryHash, self::$_queryHash))
                    continue;
                if (!$sqlres->query($value)) {
                    $app->sogo_helper->logError("sogo_plugin::__syncMailUsers(): sync users failed for domain [{$domain_name}]." . PHP_EOL . "SQL: {$value}" . PHP_EOL . "SQL Error: " . $sqlres->error . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 1));
                }
                self::$_queryHash[] = $_queryHash;
            }
            foreach ($_tmpSQL['alias'] as $value) {
                $_queryHash = md5($value); //* avoid multiple of the same query
                if (in_array($_queryHash, self::$_queryHash))
                    continue;
                if (!$sqlres->query($value)) {
                    $app->sogo_helper->logError("sogo_plugin::__syncMailUsers(): sync users aliases failed for domain [{$domain_name}]." . PHP_EOL . "SQL: {$value}" . PHP_EOL . "SQL Error: " . $sqlres->error . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 1));
                }
                self::$_queryHash[] = $_queryHash;
            }
        }
        $app->sogo_helper->logDebug("Sync Mail Users in {$domain_name}");
        return TRUE;
    }

    /**
     * 
     * @global app $app
     * @param type $email
     * @param type $table
     * @return type
     */
    private function __sogo_mail_user_exists($email, $table) {
        global $app;
        $sqlres = & $app->sogo_helper->sqlConnect();
        $usr = $sqlres->query("SELECT `c_imaplogin` FROM {$table} WHERE `c_imaplogin`='{$app->sogo_helper->dbEscapeString($email)}'");
        return ($usr !== FALSE && count($usr->fetch_assoc()) > 0 ? TRUE : FALSE);
    }

    /**
     * method to remove a sogo user from sogo storage
     * @global app $app
     * @global array $conf
     * @param string $email the email address to remove
     */
    private function __deleteMailUser($email) {
        global $app, $conf;
        if (!empty($email) && (strpos($email, '@') !== FALSE)) {
            $cmd_arg = escapeshellarg("{$conf['sogo_tool_binary']}") . " remove " . escapeshellarg("{$email}");
            $cmd = str_replace('{command}', $cmd_arg, $conf['sogo_su_command']);
            $app->sogo_helper->logDebug("sogo_plugin::remove_sogo_mail_user() \n\t - CALL:{$cmd}");
            exec($cmd);
            $usrDom = explode('@', $email);
            $sqlres = & $app->sogo_helper->sqlConnect();
            $sqlres->query("DELETE FROM `{$app->sogo_helper->getValidSOGoTableName($usrDom[1])}` WHERE `c_uid` = '{$sqlres->escape_string($email)}'");
            if ($sqlres->error)
                $app->sogo_helper->logDebug("sogo_plugin::remove_sogo_mail_user() \n\t - SQL Error: {$sqlres->error}");
        }
    }

    private function __checkStateDropDomain($domain, $domain_id = -1) {
        global $app;
        if (!$app->sogo_helper->is_domain_active($domain)) {
            //* not active
            if ($app->sogo_helper->sogoTableExists($domain)) {
                //* check if users exists in table, delete them with SOGo if they do
                $domain_table = $app->sogo_helper->getValidSOGoTableName($domain);
                $sqlres = & $app->sogo_helper->sqlConnect();
                if ($tmp = $sqlres->query("SELECT `c_imaplogin` FROM `{$sqlres->escape_string($domain_table)}`;")) {
                    while ($obj = $tmp->fetch_object()) {
                        if (isset($obj->c_imaplogin)) {
                            //* only deletes from SOGo db
                            $this->__deleteMailUser($obj->c_imaplogin);
                        }
                    }
                }
                $app->sogo_helper->dropSOGoUsersTable($domain, $domain_id);
            }
            return false;
        }
        return true;
    }

    /**
     * 
     * @global app $app
     * @global array $conf
     * @param type $method
     */
    private function __buildSOGoConfig($method) {
        global $app, $conf;
        $app->sogo_helper->logDebug("buildSOGoConfig: called by [{$method}]");
        //* get server config (CURRENT RUNNING server config)
        if ($sconf = $app->sogo_helper->getServerConfig()) {
            $sconf['SOGoMailListViewColumnsOrder'] = explode(',', $sconf['SOGoMailListViewColumnsOrder']);
            $sconf['SOGoCalendarDefaultRoles'] = explode(',', $sconf['SOGoCalendarDefaultRoles']);
            
            //* if called more then once
            if(!is_object($app->sogo_config) && !class_exists('sogo_config'))
                $app->uses('sogo_config');
            else if(!is_object($app->sogo_config) && class_exists('sogo_config'))
                $app->sogo_config = new sogo_config();
            
            //* build XML document
            $app->sogo_config->createConfig(array('sogod' => $sconf));
            //* holder for builded domain xml config
            $sogodomsconf = "";
            //* query mail domains active and based on module settings
            if ($mail_domains = $app->sogo_helper->getMailDomainNames('y')) {
                //* on success loop mail domains, prepare config
                foreach ($mail_domains as $value) {
                    if (!$app->sogo_helper->sogoTableExists($value['domain']))
                        continue;
                    $dconf = $app->sogo_helper->getDomainConfig($value['domain'], TRUE);

                    //* get domain config template for domains (conf-custom then main conf)
                    $tpl = $app->sogo_helper->getTemplateObject("sogo_domain.master");
                    if ($tpl !== null && $tpl instanceof tpl) {
                        //* loop domain config
                        foreach ($dconf as $key => $value2) {
                            if (($sconf[$key] == $value2 || $key == 'server_name') && ($key != 'SOGoSMTPServer')) {
                                //* skip config settings that is default the server!
                            } else if ($key == 'SOGoSuperUsernames') {
                                $_arr = explode(',', $dconf['SOGoSuperUsernames']);
                                $arr = array();
                                foreach ($_arr as $value3)
                                    $arr[] = array('SOGoSuperUsername' => $value3);
                                $tpl->setLoop('SOGoSuperUsernames', $arr);
                            } else if ($key == 'SOGoCalendarDefaultRoles') {
                                if (implode(',', $sconf[$key]) == $dconf['SOGoCalendarDefaultRoles'])
                                    continue;
                                $_arr = explode(',', $dconf['SOGoCalendarDefaultRoles']);
                                $arr = array();
                                foreach ($_arr as $value3)
                                    $arr[] = array('SOGoCalendarDefaultRole' => $value3);
                                $tpl->setLoop('SOGoCalendarDefaultRoles', $arr);
                            } else if ($key == 'SOGoMailListViewColumnsOrder') {
                                if (implode(',', $sconf[$key]) == $dconf['SOGoMailListViewColumnsOrder'])
                                    continue;
                                $_arr = explode(',', $dconf['SOGoMailListViewColumnsOrder']);
                                $arr = array();
                                foreach ($_arr as $value3)
                                    $arr[] = array('SOGoMailListViewColumn' => $value3);
                                $tpl->setLoop('SOGoMailListViewColumnsOrder', $arr);
                            } else
                                $tpl->setVar($key, $value2); //* default isset as normal var
                        }
                        $tpl->setVar('domain', $value['domain']);
                        $tpl->setVar('SOGOUNIQID', md5($value['domain']));
                        $tpl->setVar('CONNECTIONVIEWURL', "mysql://{$conf['sogo_database_user']}:{$conf['sogo_database_passwd']}@{$conf['sogo_database_host']}:{$conf['sogo_database_port']}/{$conf['sogo_database_name']}/{$app->sogo_helper->getValidSOGoTableName($value['domain'])}");
                        $tpl->setVar($conf['sogo_domain_extra_vars']);
                        $MailFieldNames = array();
                        $dtacount = (int) $app->sogo_helper->getSOGoTableAliasColumnCount($value['domain']); //* get alias columns in table for domain
                        for ($i = 0; $i < $dtacount; $i++) {
                            $MailFieldNames[] = array('MailFieldName' => 'alias_' . $i);
                        }
                        $tpl->setLoop('MailFieldNames', $MailFieldNames); //* set alias names loop
                        $sogodomsconf .= str_replace(array('{SERVERNAME}', '{domain}'), array($dconf['server_name'], $value['domain']), $tpl->grab());
                    }

                    //$app->sogo_helper->logDebug(print_r($sconf, TRUE));
                    //$app->sogo_helper->logDebug(print_r($dconf, TRUE));
                    //$app->sogo_helper->logDebug(print_r($sogodomsconf, TRUE));
                }
                //* END: mail domains loop
            }
            $this_server = $app->sogo_helper->getServer((int) $conf['server_id']);
            $replace_vars = array('{SOGODOMAINSCONF}', '{SOGOUSERN}', '{SOGOUSERPW}', '{MYSQLHOST}', '{MYSQLPORT}', '{SOGODB}', '{SERVERNAME}',);
            $replace_values = array($sogodomsconf, $conf['sogo_database_user'], $conf['sogo_database_passwd'], $conf['sogo_database_host'], $conf['sogo_database_port'], $conf['sogo_database_name'], $this_server['server_name'],);
            //* replace default vars in default sogo config
            $sogod = $app->sogo_config->getConfigReplace(sogo_config::CONFIG_FULL, $replace_vars, $replace_values);
            //* replace default vars in sogo config (sogod.plist)
            $sogodplist = $app->sogo_config->getConfigReplace(sogo_config::CONFIG_PLIST, $replace_vars, $replace_values);

            //* load it as DOMDocument Object (this validates the XML)
            if ($app->sogo_config->loadSOGoConfigString($sogod) !== FALSE) {
                unset($app->sogo_config); //$app->sogo_config->clearAll(); //* unset everything (- ~5kB per. domain)
                $result = TRUE;
                if (file_exists($conf['sogo_gnu_step_defaults']))
                    copy($conf['sogo_gnu_step_defaults'], $conf['sogo_gnu_step_defaults'] . ".back");
                $result = file_put_contents($conf['sogo_gnu_step_defaults'], $sogod); //* try writing to the file
                //* debug the result
                $app->sogo_helper->logDebug("{$method} Write file [{$conf['sogo_gnu_step_defaults']}] " . ($result ? "Succeeded" : "Failed") . " (CONFIG var: sogo_gnu_step_defaults)");
                //* check if file exists (sogod.plist)
                if (file_exists($conf['sogo_gnu_step_defaults_sogod.plist'])) {
                    copy($conf['sogo_gnu_step_defaults_sogod.plist'], $conf['sogo_gnu_step_defaults_sogod.plist'] . ".back");
                    $result = file_put_contents($conf['sogo_gnu_step_defaults_sogod.plist'], $sogodplist);
                    $app->sogo_helper->logDebug("{$method} Write file [{$conf['sogo_gnu_step_defaults_sogod.plist']}] " . ($result ? "Succeeded" : "Failed") . " (CONFIG var: sogo_gnu_step_defaults_sogod.plist)");
                }
                //* test the result
                if ($result) {
                    //* log more debug and restart
                    $app->sogo_helper->logDebug("{$method} rebuilded SOGo config OK");
                    $app->services->restartServiceDelayed('sogo', 'restart');
                } else {
                    //* log error somthing when't wrong (check: /var/log/ispconfig/cron.log)
                    $app->sogo_helper->logError("{$method} Unable to rebuild and/or save new SOGo config...");
                }
            } else {
                //* in case we build invalid SOGo config create error
                $app->sogo_helper->logError("SOGo Config is not valid:" . PHP_EOL . implode(PHP_EOL, libxml_get_errors()));
                //* only log FULL configuration in debug mode
                $app->sogo_helper->logDebug("Failed SOGo XML Config:" . PHP_EOL . $sogod);
            }
        } else {
            $app->sogo_helper->logDebug("SOGo Server config not found");
        }
    }

}
