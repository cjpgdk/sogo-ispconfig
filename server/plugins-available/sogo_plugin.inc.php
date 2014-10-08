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
        }
    }

    private function __buildSOGoConfig($method) {
        global $app, $conf;
        $app->sogo_helper->logDebug("buildSOGoConfig: called by [{$method}]");
        //* get server config (CURRENT RUNNING server config)
        if ($sconf = $this->__get_server_config()) {
            $sconf['SOGoMailListViewColumnsOrder'] = explode(',', $sconf['SOGoMailListViewColumnsOrder']);
            $sconf['SOGoCalendarDefaultRoles'] = explode(',', $sconf['SOGoCalendarDefaultRoles']);

            //* build XML document
            $app->sogo_config->createConfig(array('sogod' => $sconf));
            //* holder for builded domain xml config
            $sogodomsconf = "";
            //* SQL to select the mail domains
            $mail_domains_sql = "SELECT `domain` FROM `mail_domain`";
            //* we use tpl class to build the domain config xml 
            $app->uses('tpl');
            //* query mail domains
            if ($mail_domains = $app->db->queryAllRecords($mail_domains_sql)) {
                //* on success loop mail domains, prepare config
                foreach ($mail_domains as $value) {
                    //* get full config for this domain.!
                    $dconf = $this->__get_domain_config($value['domain'], TRUE);
                    //* get domain config template for domains (conf-custom then main conf)
                    $tpl = NULL;
                    if (file_exists(ISPC_ROOT_PATH . "/conf-custom/sogo_domain.master")) $tpl = new tpl(ISPC_ROOT_PATH . "/conf-custom/sogo_domain.master");
                    else if (file_exists(ISPC_ROOT_PATH . "/conf/sogo_domain.master")) $tpl = new tpl(ISPC_ROOT_PATH . "/conf/sogo_domain.master");
                    if ($tpl !== null && $tpl instanceof tpl) {
                        //* loop domain config
                        foreach ($dconf as $key => $value2) {
                            if ($sconf[$key] == $value2) {
                                //* skip config settings that is default the server!
                            } else if ($key == 'SOGoSuperUsernames') {
                                //* force array on selected item
                                $_arr = explode(',', $dconf['SOGoSuperUsernames']);
                                $arr = array();
                                foreach ($_arr as $value3)
                                    $arr[] = array('SOGoSuperUsername' => $value3);
                                $tpl->setLoop('SOGoSuperUsernames', $arr);
                            } else if ($key == 'SOGoCalendarDefaultRoles') {
                                if (implode(',', $sconf[$key]) == $dconf['SOGoCalendarDefaultRoles']) continue;
                                //* force array on selected item
                                $_arr = explode(',', $dconf['SOGoCalendarDefaultRoles']);
                                $arr = array();
                                foreach ($_arr as $value3)
                                    $arr[] = array('SOGoCalendarDefaultRole' => $value3);
                                $tpl->setLoop('SOGoCalendarDefaultRoles', $arr);
                            } else if ($key == 'SOGoMailListViewColumnsOrder') {
                                if (implode(',', $sconf[$key]) == $dconf['SOGoMailListViewColumnsOrder']) continue;
                                //* force array on selected item
                                $_arr = explode(',', $dconf['SOGoMailListViewColumnsOrder']);
                                $arr = array();
                                foreach ($_arr as $value3)
                                    $arr[] = array('SOGoMailListViewColumn' => $value3);
                                $tpl->setLoop('SOGoMailListViewColumnsOrder', $arr);
                            } else $tpl->setVar($key, $value2); //* default isset as normal var
                        }
                        //* set domain name var
                        $tpl->setVar('domain', $value['domain']);
                        //* use md5 as uniq is based on domain name
                        $tpl->setVar('SOGOUNIQID', md5($value['domain']));
                        //* set connection view
                        $tpl->setVar('CONNECTIONVIEWURL', "mysql://{$conf['sogo_database_user']}:{$conf['sogo_database_passwd']}@{$conf['sogo_database_host']}:{$conf['sogo_database_port']}/{$conf['sogo_database_name']}/{$app->sogo_helper->get_valid_sogo_table_name($value['domain'])}_users");
                        //* set vars from static config in config.local file
                        $tpl->setVar($conf['sogo_domain_extra_vars']);
                        //* fetch and set alias columns names
                        $MailFieldNames = array();
                        $dtacount = (int) $app->sogo_helper->get_sogo_table_alias_column_count($value['domain']); //* get alias columns in table for domain
                        for ($i = 0; $i < $dtacount; $i++) {
                            $MailFieldNames[] = array('MailFieldName' => 'alias_' . $i);
                        }
                        $tpl->setLoop('MailFieldNames', $MailFieldNames); //* set alias names loop
                        //* grab the build config xml and append to (holder for builded domain xml config)
                        $sogodomsconf .= $tpl->grab();
                    }

                    //$app->sogo_helper->logDebug(print_r($sconf, TRUE));
                    //$app->sogo_helper->logDebug(print_r($dconf, TRUE));
                }
                //* END: mail domains loop
            }
            //* replace default vars in default sogo config
            $app->sogo_config->sogod = str_replace(array(
                '{{SOGODOMAINSCONF}}',
                '${SOGOUSERN}', /* to be removed */
                '{SOGOUSERN}',
                '${SOGOUSERPW}', /* to be removed */
                '{SOGOUSERPW}',
                '${MYSQLHOST}', /* to be removed */
                '{MYSQLHOST}',
                '${MYSQLPORT}', /* to be removed */
                '{MYSQLPORT}',
                '${SOGODB}', /* to be removed */
                '{SOGODB}',
                    ), array(
                $sogodomsconf,
                $conf['sogo_database_user'], /* to be removed */
                $conf['sogo_database_user'],
                $conf['sogo_database_passwd'], /* to be removed */
                $conf['sogo_database_passwd'],
                $conf['sogo_database_host'], /* to be removed */
                $conf['sogo_database_host'],
                $conf['sogo_database_port'], /* to be removed */
                $conf['sogo_database_port'],
                $conf['sogo_database_name'], /* to be removed */
                $conf['sogo_database_name'],
                    ), $app->sogo_config->sogod);
            //* replace default vars in sogo config (sogod.plist)
            $app->sogo_config->sogodplist = str_replace(array(
                '{{SOGODOMAINSCONF}}',
                '${SOGOUSERN}', /* to be removed */
                '{SOGOUSERN}',
                '${SOGOUSERPW}', /* to be removed */
                '{SOGOUSERPW}',
                '${MYSQLHOST}', /* to be removed */
                '{MYSQLHOST}',
                '${MYSQLPORT}', /* to be removed */
                '{MYSQLPORT}',
                '${SOGODB}', /* to be removed */
                '{SOGODB}',
                    ), array(
                $sogodomsconf,
                $conf['sogo_database_user'], /* to be removed */
                $conf['sogo_database_user'],
                $conf['sogo_database_passwd'], /* to be removed */
                $conf['sogo_database_passwd'],
                $conf['sogo_database_host'], /* to be removed */
                $conf['sogo_database_host'],
                $conf['sogo_database_port'], /* to be removed */
                $conf['sogo_database_port'],
                $conf['sogo_database_name'], /* to be removed */
                $conf['sogo_database_name'],
                    ), $app->sogo_config->sogodplist);

            //* load it as DOMDocument Object (this validates the XML)
            if ($app->sogo_config->loadSOGoConfigString($app->sogo_config->sogod) !== FALSE) {
                $result = TRUE;
                file_exists($conf['sogo_gnu_step_defaults'])
                    copy($conf['sogo_gnu_step_defaults'], $conf['sogo_gnu_step_defaults'] . ".".time()); //* create backup
                $result = file_put_contents($conf['sogo_gnu_step_defaults'], $app->sogo_config->sogod);//* try writing to the file
    
                //* debug the result
                $app->sogo_helper->logDebug("{$method} Write file [{$conf['sogo_gnu_step_defaults']}] " . ($result ? "Succeeded" : "Failed") . " (CONFIG var: sogo_gnu_step_defaults)");
                //* check if file exists (sogod.plist)
                if (file_exists($conf['sogo_gnu_step_defaults_sogod.plist'])) {
                    copy($conf['sogo_gnu_step_defaults_sogod.plist'], $conf['sogo_gnu_step_defaults_sogod.plist'] . ".last");
                    $result = file_put_contents($conf['sogo_gnu_step_defaults_sogod.plist'], $app->sogo_config->sogodplist);
                    //* debug the result
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
                $app->sogo_helper->logDebug("Failed SOGo XML Config:" . PHP_EOL . $app->sogo_config->sogod);
            }
        } else {
            $app->sogo_helper->logDebug("Server config not found");
        }
    }

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
        if (!isset($data['old']['domain_id']) && (intval($data['old']['domain_id']) <= 0)) {  //* required
            $app->sogo_helper->logDebug("Invalid call to sogo_plugin::sogo_domain_delete({$event_name}, Array)\n" . print_r($data, true));
            return;
        }
        $domain_name = (isset($data['old']['domain']) ? $data['old']['domain'] : (isset($data['old']['domain_name']) ? $data['old']['domain_name'] : ''));
        if (empty($domain_name)) {  //* required
            $app->sogo_helper->logDebug("Invalid call to sogo_plugin::sogo_domain_delete({$event_name}, Array)\n" . print_r($data, true));
            return;
        }
        /*
          $domain_id = intval($data['old']['domain_id']);
          $sogo_id = isset($data['old']['sogo_id']) ? $data['old']['sogo_id'] : -1;
         */
        if ($event_name == 'sogo_domains_delete') {
            //* config delete only
            if ($app->sogo_helper->sogo_table_exists($domain_name)) {
                /*
                  $sogo_db = & $app->sogo_helper->sqlConnect();
                  //* delete all users data before we remove the domain table
                  $usersmail = $sogo_db->query("SELECT `mail` FROM `{$app->sogo_helper->get_valid_sogo_table_name($domain_name)}_users`");
                  while ($obj = $usersmail->fetch_object()) {
                  $this->__delete_mail_user($obj->mail);
                  }
                  //* delete the domain table
                  $this->__drop_sogo_users_table($domain_name, $data['old']['domain_id']);

                  //* all removed create it again with server defaults
                  $this->__create_sogo_table($domain_name);
                 */
                $this->__sync_mail_users($domain_name);
            } else {
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
        if (!$app->sogo_helper->isEqual($event_name, 'mail_forwarding_delete')) return;
        //* check this is an alias
        if (!$app->sogo_helper->isEqual($data['old']['type'], 'alias')) return;

        //$app->sogo_helper->logDebug("sogo_plugin::remove_sogo_mail_user_alias(): Started.");
        /**
         * @todo make single query to remove aliases (this lightens the load and the amount of sql queries)
         */
        list($source_user, $source_domain) = explode('@', $data['old']['source']);
        list($destination_user, $destination_domain) = explode('@', $data['old']['destination']);

        //* a simple sync should be ok 
        $this->__sync_mail_users($destination_domain);

        //$app->sogo_helper->logDebug("sogo_plugin::remove_sogo_mail_user_alias(): ENDED.");
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
        if (!$app->sogo_helper->isEqual($event_name, 'mail_forwarding_insert')) return;
        //* check this is an alias
        if (!$app->sogo_helper->isEqual($data['new']['type'], 'alias')) return;

        $app->sogo_helper->logDebug("sogo_plugin::insert_sogo_mail_user_alias(): Started.");

        list($source_user, $source_domain) = explode('@', $data['new']['source']);
        list($destination_user, $destination_domain) = explode('@', $data['new']['destination']);

        //* get total alias count for domain
        $acount_n = (int) $app->sogo_helper->get_max_alias_count($destination_domain, 'n'); //* none active
        $acount_y = (int) $app->sogo_helper->get_max_alias_count($destination_domain, 'y'); //* active
        $acount = (int) ($acount_n + $acount_y);

        $dtacount = (int) $app->sogo_helper->get_sogo_table_alias_column_count($destination_domain); //* get alias columns in table for domain
        $has_error = FALSE;
        if ($dtacount < $acount) {
            //* update domain table
            $sql = array();
            for ($index = 0; $index < intval(($acount - $dtacount)); $index++) {
                $_i = (int) ($dtacount + $index);
                $sql[] = "ALTER TABLE `{$app->sogo_helper->get_valid_sogo_table_name($destination_domain)}_users` ADD `alias_{$_i}` VARCHAR( 500 ) NOT NULL ";
            }
            $sqlres = & $app->sogo_helper->sqlConnect();
            foreach ($sql as $value) {
                $app->sogo_helper->logDebug("sogo_plugin::insert_sogo_mail_user_alias(): EXEC Query. " . PHP_EOL . $value);
                if (!$sqlres->query($value)) {
                    $app->sogo_helper->logError("sogo_plugin::insert_sogo_mail_user_alias(): update domain table for [{$destination_domain}], FAILD" . PHP_EOL . "SQL: {$value}" . PHP_EOL . "SQL Error: " . $sqlres->error . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 1));
                    $has_error = TRUE;
                }
            }
        }
        //* don't sync on error
        if (!$has_error) {
            $this->__sync_mail_users($destination_domain);
            $method = "sogo_plugin::insert_sogo_mail_user_alias():";
            $this->__buildSOGoConfig($method);
        }
        $app->sogo_helper->logDebug("sogo_plugin::insert_sogo_mail_user_alias(): ENDED.");
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
        if (!$app->sogo_helper->isEqual($event_name, 'mail_forwarding_update')) return;

        list($old_source_user, $old_source_domain) = explode('@', $data['old']['source']);
        list($new_source_user, $new_source_domain) = explode('@', $data['new']['source']);
        list($old_destination_user, $old_destination_domain) = explode('@', $data['old']['destination']);
        list($new_destination_user, $new_destination_domain) = explode('@', $data['new']['destination']);

        $is_synced = FALSE;
        /*
         * all using __sync_mail_users
         * only done like this in case we need diferent actions on some of the changes
         */
        if (!$app->sogo_helper->isEqual($data['old']['type'], $data['new']['type'])) {
            //* type changed
            if (!$is_synced) $this->__sync_mail_users($new_destination_domain);
            $is_synced = TRUE;
        }
        if (!$is_synced && !$app->sogo_helper->isEqual($data['old']['server_id'], $data['new']['server_id'])) {
            //* server changed
        }
        if (!$app->sogo_helper->isEqual($data['old']['source'], $data['new']['source'])) {
            //* alias changed
            if (!$is_synced) $this->__sync_mail_users($new_destination_domain);
            $is_synced = TRUE;
        }
        if (!$app->sogo_helper->isEqual($data['old']['destination'], $data['new']['destination'])) {
            //* destination changed
            if (!$is_synced) $this->__sync_mail_users($new_destination_domain);
            $is_synced = TRUE;
        }
        if (!$app->sogo_helper->isEqual($data['old']['active'], $data['new']['active'])) {
            //* active changed
            if (!$is_synced) $this->__sync_mail_users($new_destination_domain);
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
        if (!$app->sogo_helper->isEqual($event_name, 'mail_user_insert')) return;

        list($user, $domain) = explode('@', $data['new']['email']);

        //* a simple sync should be ok 
        $this->__sync_mail_users($domain);

//      [new] => Array
//          (
//            [server_id] => 1
//            [email] => mail03@example.com
//            [login] => mail03@example.com
//            [password] => $1$p8seVRTM$u00/0c45CClqv/KzzfEm81
//            [name] => mail03
//            [postfix] => y
//            [access] => y
//            [disableimap] => n
//            [disablepop3] => n
//            [disabledeliver] => n
//            [disablesmtp] => n
//            [disablesieve] => n
//            [disablesieve-filter] => n
//            [disablelda] => n
//            [disablelmtp] => n
//            [disabledoveadm] => n
//          )
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
        if ($event_name == "mail_user_update") {
            list($old_user, $old_domain) = explode('@', $data['old']['email']);
            list($new_user, $new_domain) = explode('@', $data['new']['email']);
            //* in reponse to user/domain changed
            if ($data['old']['email'] != $data['new']['email']) {
                $app->sogo_helper->logDebug("sogo_plugin::update_sogo_mail_user(): change email, OLD:{$data['old']['email']} , NEW:{$data['new']['email']}");
                /*
                  we do this in "$this->remove_sogo_domain(...);"
                  $this->__delete_mail_user($data['old']['login']);
                 */
                $sync = FALSE;
                //* make sure new domain is created
                if ($old_domain != $new_domain) {
                    $this->__delete_mail_user($data['old']['login']); //* remove all related to old user 
                    if (!$app->sogo_helper->sogo_table_exists($new_domain)) {
                        $this->__create_sogo_table($new_domain); //* allso syncs all users!
                        $sync = TRUE;
                    }
                }
                //* if only username is changed
                if ($old_user != $new_user) {
                    $this->__delete_mail_user($data['old']['login']); //* remove all related to old user 
                }
                //* e-mail is changed so sync it all
                if ($old_domain != $new_domain) $this->__sync_mail_users($old_domain);
                if (!$sync) $this->__sync_mail_users($new_domain);
                $sync = TRUE;
            }

            if ($data['old']['password'] != $data['new']['password']) {
                $app->sogo_helper->logDebug("sogo_plugin::update_sogo_mail_user(): change password, on {$data['new']['email']}");
                //* sync all based on new domain
                if (!$sync) $this->__sync_mail_users($new_domain);
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
        if ($app->sogo_helper->isEqual($event_name, 'mail_user_delete')) $this->__delete_mail_user($data['old']['login']);
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
        if (!$app->sogo_helper->isEqual($event_name, 'mail_domain_insert')) return;

        $this->__create_sogo_table($data['new']['domain']);
        $method = "sogo_plugin::insert_sogo_mail_domain():";
        $this->__buildSOGoConfig($method);
//    [new] => Array
//        (
//            [domain_id] => 12
//            [sys_userid] => 1
//            [sys_groupid] => 0
//            [sys_perm_user] => riud
//            [sys_perm_group] => ru
//            [sys_perm_other] =>
//            [server_id] => 1
//            [domain] => detygheghet.com
//            [dkim] => n
//            [dkim_selector] => default
//            [dkim_private] =>
//            [dkim_public] =>
//            [active] => y
//        )
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
     * delete a SOGo mail domain table from SOGo db
     * @global app $app
     * @param string $domain_name
     * @param integer $domain_id
     */
    private function __drop_sogo_users_table($domain_name, $domain_id) {
        global $app;
        $sogo_db = & $app->sogo_helper->sqlConnect();
        $app->sogo_helper->logDebug("sogo_plugin::sogo_domain_delete(): delete SOGo table [{$app->sogo_helper->get_valid_sogo_table_name($domain_name)}_users] : {$domain_id}#{$domain_name}");
        $sogo_db->query("DROP TABLE {$app->sogo_helper->get_valid_sogo_table_name($domain_name)}_users");
        if ($sogo_db->error) {
            $app->sogo_helper->logWarn("sogo_plugin::sogo_domain_delete(): SQL ERROR:\n{$sogo_db->error}\n{$domain_id}#{$domain_name}");
        }
    }

    /**
     * create mail domain table and sync mail user for use with SOGo
     * @global app $app
     * @global array $conf
     * @param string $domain_name
     * @return boolean
     */
    private function __create_sogo_table($domain_name) {
        global $app, $conf;
        if ($app->sogo_helper->sogo_table_exists($domain_name)) {
            $app->sogo_helper->logDebug("sogo_plugin::__create_sogo_table(): SOGo table exists for domain: {$domain_name}");
            return $this->__sync_mail_users($domain_name);
        }

        $sql = "
CREATE TABLE IF NOT EXISTS `{$app->sogo_helper->get_valid_sogo_table_name($domain_name)}_users` (
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
        $result &= $this->__sync_mail_users($domain_name);
        return $result;
    }

    /**
     * sync all mail users and aliases for a given domain name
     * @global app $app
     * @param string $domain_name
     * @param boolean $imap_enabled if set to false will sync all email addresses, is set to true will only sync email addresses with imap enabled
     * @return boolean
     */
    private function __sync_mail_users($domain_name, $imap_enabled = true) {
        global $app;
        $app->sogo_helper->logDebug("sogo_plugin::__sync_mail_users(): STATED");
        $app->sogo_helper->logDebug("sogo_plugin::__sync_mail_users(): sync users in [{$domain_name}] ");
        $emails = $app->db->queryAllRecords("SELECT * FROM `mail_user` WHERE `email` LIKE '%@{$domain_name}'" . ($imap_enabled ? "AND `disableimap` = 'n'" : ""));
        $sogo_user_sql = "";
        if (!empty($emails)) {
            $domain_config = $this->__get_domain_config($domain_name, true);
            if (!$domain_config || !is_array($domain_config)) {
                $app->sogo_helper->logError("sogo_plugin::__sync_mail_users(): unable to fetch the domain config for domain [{$domain_name}]");
                return false;
            }
            $_tmpSQL = array('users' => array(), 'alias' => array());

            /*
              $_server_query = "SELECT s.`server_name` FROM `server` s, `mail_domain` md  WHERE s.`server_id`=md.`server_id` AND md.`domain`='{$domain_name}'";
              $server = $app->db->queryOneRecord($_server_query);
              if (!isset($server['server_name'])) {
              $app->log("SOGo mail users sync failed." . PHP_EOL . "Unable to get server name from domain {$domain_name}" . PHP_EOL . "SQL: {$_server_query}" . PHP_EOL . "SQL Error: {$app->db->error}", LOGLEVEL_ERROR);
              return FALSE;
              }
             */
            foreach ($emails as $email) {
                $app->sogo_helper->logDebug("sogo_plugin::__sync_mail_users(): Start: {$email['login']}");
                if ($this->__sogo_mail_user_exists($email['login'], "{$app->sogo_helper->get_valid_sogo_table_name($domain_name)}_users")) {
                    $_tmpSQL['users'][] = "UPDATE `{$app->sogo_helper->get_valid_sogo_table_name($domain_name)}_users` SET "
                            . " `c_uid` = '{$app->db->quote($email['login'])}' ,"
                            . " `c_cn` = '{$app->db->quote($email['name'])}' ,"
                            . " `c_name` = '{$app->db->quote($email['login'])}' ,"
                            . " `mail` = '{$app->db->quote($email['email'])}' ,"
                            . " `c_imaplogin` = '{$app->db->quote($email['login'])}' ,"
                            . " `c_sievehostname` = '{$app->db->quote($domain_config['SOGoSieveServer'])}' ,"
                            . " `c_imaphostname` = '{$app->db->quote($domain_config['SOGoIMAPServer'])}' ,"
                            . " `c_domain` = '{$app->db->quote($domain_name)}' ,"
                            . " `c_password` = '{$app->db->quote($email['password'])}' "
                            . " WHERE `c_uid`='{$app->db->quote($email['login'])}';";
                } else {
                    $_tmpSQL['users'][] = "INSERT INTO `{$app->sogo_helper->get_valid_sogo_table_name($domain_name)}_users` "
                            . "(`c_uid`, `c_cn`, `c_name`, `mail`, `c_imaplogin`, `c_sievehostname`, `c_imaphostname`, `c_domain`, `c_password`) "
                            . "VALUES "
                            . "("
                            . "'{$app->db->quote($email['login'])}', "
                            . "'{$app->db->quote($email['name'])}', "
                            . "'{$app->db->quote($email['login'])}', "
                            . "'{$app->db->quote($email['email'])}', "
                            . "'{$app->db->quote($email['login'])}', "
                            . "'{$app->db->quote($domain_config['SOGoSieveServer'])}', "
                            . "'{$app->db->quote($domain_config['SOGoIMAPServer'])}', "
                            . "'{$app->db->quote($domain_name)}', "
                            . "'{$app->db->quote($email['password'])}'"
                            . ");";
                }
                $mail_aliases = $app->db->queryAllRecords("SELECT `source` FROM `mail_forwarding` WHERE `destination` = '{$app->db->quote($email['login'])}' AND `type`='alias' AND `active`='y';");
                if (is_array($mail_aliases) && !empty($mail_aliases)) {
                    $aliasSQL = "UPDATE `{$app->sogo_helper->get_valid_sogo_table_name($domain_name)}_users` SET ";

                    $dtacount = (int) $app->sogo_helper->get_sogo_table_alias_column_count($domain_name); //* get alias columns in table for domain
                    //* only do alias update if a column exists for it
                    if ($dtacount > 0) {
                        $ac = 0;
                        foreach ($mail_aliases as $key => $value) {
                            $aliasSQL .= " `alias_{$ac}` = '{$app->db->quote($value['source'])}' ,";
                            $ac++;
                            //* must be a better way but, need some results here so break on max alias columns in tb
                            if ($dtacount == $ac) break;
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
                                . " `c_uid` = '{$app->db->quote($email['login'])}' AND"
                                . " `c_cn` = '{$app->db->quote($email['name'])}' AND"
                                . " `c_name` = '{$app->db->quote($email['login'])}' AND"
                                . " `mail` = '{$app->db->quote($email['email'])}' AND"
                                . " `c_imaplogin` = '{$app->db->quote($email['login'])}' AND"
                                . " `c_sievehostname` = '{$app->db->quote($domain_config['SOGoSieveServer'])}' AND"
                                . " `c_imaphostname` = '{$app->db->quote($domain_config['SOGoIMAPServer'])}' AND"
                                . " `c_domain` = '{$app->db->quote($domain_name)}' AND"
                                . " `c_password` = '{$app->db->quote($email['password'])}';";
                    }
                }
                /*
                 * server_id
                 * name
                 * disableimap
                 * disablesieve
                 * disablesieve-filter
                 */
                $app->sogo_helper->logDebug("sogo_plugin::__sync_mail_users(): END: {$email['login']}");
            }
            $sqlres = & $app->sogo_helper->sqlConnect();
            foreach ($_tmpSQL['users'] as $value) {
                $_queryHash = md5($value); //* avoid multiple of the same query
                if (in_array($_queryHash, self::$_queryHash)) continue;
                $app->sogo_helper->logDebug("sogo_plugin::__sync_mail_users(): EXEC Query. " . PHP_EOL . $value);
                if (!$sqlres->query($value)) {
                    $app->sogo_helper->logError("sogo_plugin::__sync_mail_users(): sync users failed for domain [{$domain_name}]." . PHP_EOL . "SQL: {$value}" . PHP_EOL . "SQL Error: " . $sqlres->error . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 1));
                }
                self::$_queryHash[] = $_queryHash;
            }
            foreach ($_tmpSQL['alias'] as $value) {
                $_queryHash = md5($value); //* avoid multiple of the same query
                if (in_array($_queryHash, self::$_queryHash)) continue;
                $app->sogo_helper->logDebug("sogo_plugin::__sync_mail_users(): EXEC Query. " . PHP_EOL . $value);
                if (!$sqlres->query($value)) {
                    $app->sogo_helper->logError("sogo_plugin::__sync_mail_users(): sync users aliases failed for domain [{$domain_name}]." . PHP_EOL . "SQL: {$value}" . PHP_EOL . "SQL Error: " . $sqlres->error . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 1));
                }
                self::$_queryHash[] = $_queryHash;
            }
        }
        $app->sogo_helper->logDebug("sogo_plugin::__sync_mail_users(): ENDED");
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
        $usr = $sqlres->query("SELECT `c_imaplogin` FROM {$table} WHERE `c_imaplogin`='{$app->db->quote($email)}'");
        return ($usr !== FALSE && count($usr->fetch_assoc()) > 0 ? TRUE : FALSE);
    }

    /**
     * get config by server id
     * @global app $app
     * @global array $conf
     * @param integer $server_id
     * @return array|boolean
     */
    private function __get_server_config($server_id = NULL) {
        global $app, $conf;
        if ($server_id === NULL || !is_int($server_id)) $server_id = $conf['server_id'];

        $sql = "SELECT * FROM `sogo_config` WHERE `server_id`=" . intval($server_id);

        $server_default = $app->db->queryOneRecord($sql);
        if (!$server_default) {
            $app->sogo_helper->logError("SOGo get server config failed." . PHP_EOL . "Unable to get server config for server id {$server_id}" . PHP_EOL . "SQL: {$sql}" . PHP_EOL . "SQL Error: {$app->db->error}" . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 2));
            return false;
        }
        //* vaules we don't need in sogo config.
        unset($server_default['sogo_id'], $server_default['server_id'], $server_default['server_name'], $server_default['sys_userid'], $server_default['sys_groupid'], $server_default['sys_perm_user'], $server_default['sys_perm_group'], $server_default['sys_perm_other']);
        return $server_default;
    }

    /**
     * get domain config
     * @global app $app
     * @param string $domain_name
     * @param boolean $full_server_conf set to true gets the full config for a domain including server defaults
     * @return array|boolean boolean false on error
     */
    private function __get_domain_config($domain_name, $full_server_conf = false) {
        global $app;
        $app->sogo_helper->logDebug("sogo_plugin::__get_domain_config(): STATED");
        //* get server default config (BASED on domain name)
        $server_default_sql = "SELECT sc.* FROM `server` s, `mail_domain` md, `sogo_config` sc  WHERE s.`server_id`=md.`server_id` AND md.`domain`='{$domain_name}' AND sc.`server_id`=md.`server_id`  AND sc.`server_name`=s.`server_name`";
        $server_default = $app->db->queryOneRecord($server_default_sql);
        if (!$server_default) {
            $app->sogo_helper->logError("SOGo get server config failed." . PHP_EOL . "Unable to get server config from domain {$domain_name}" . PHP_EOL . "SQL: {$server_default_sql}" . PHP_EOL . "SQL Error: {$app->db->error}" . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 2));
            return false; //* if server default is not isset we must stop it from running to prevent SOGo or system failures
        }
        $server_default["SOGoSieveServer"] = parse_url($server_default["SOGoSieveServer"], PHP_URL_HOST);
        $server_default["SOGoIMAPServer"] = parse_url($server_default["SOGoIMAPServer"], PHP_URL_HOST);
        $server_default["SOGoSMTPServer"] = parse_url($server_default["SOGoSMTPServer"], PHP_URL_HOST);

        //* get configuration fields for domains
        $domains_columns = $app->db->queryAllRecords("SHOW COLUMNS FROM `sogo_domains`");

        //* get domain configuration
        $domain_default_sql = "SELECT * FROM `sogo_domains` WHERE `domain_name`='{$domain_name}'";
        $domain_default = $app->db->queryOneRecord($domain_default_sql);
        if (!$domain_default) {
            //* return empty array if domain conf do not exists unless $full_conf == TRUE
            if (!$full_server_conf) {
                $app->sogo_helper->logDebug("sogo_plugin::__get_domain_config(): ENDED (NULL)");
                return array();
            }
            $ret_srv = array();
            foreach ($domains_columns as $value) {
                if (isset($server_default["{$value['Field']}"])) {
                    $ret_srv["{$value['Field']}"] = $server_default["{$value['Field']}"];
                }
            }
            //* return server config if domain config do not exists!
            unset($ret_srv['sogo_id'], $ret_srv['sys_userid'], $ret_srv['sys_groupid'], $ret_srv['sys_perm_user'], $ret_srv['sys_perm_group'], $ret_srv['sys_perm_other']);
            $app->sogo_helper->logDebug("sogo_plugin::__get_domain_config(): ENDED (ret_srv)");
            return $ret_srv;
        }
        //* in domain config we only accept hostname, the server config determines the final url (EG: imaps://HOST-NAME-WILL-PLACED-INTO-HERE:143/?tls=YES)
        $domain_default["SOGoSieveServer"] = parse_url($domain_default["SOGoSieveServer"], PHP_URL_HOST);
        $domain_default["SOGoIMAPServer"] = parse_url($domain_default["SOGoIMAPServer"], PHP_URL_HOST);
        $domain_default["SOGoSMTPServer"] = parse_url($domain_default["SOGoSMTPServer"], PHP_URL_HOST);
        foreach ($domains_columns as $value) {
            //* if domain config field is empty use server default (avoid empty settings!)
            if (isset($domain_default["{$value['Field']}"]) && empty($domain_default["{$value['Field']}"])) {
                $domain_default["{$value['Field']}"] = $server_default["{$value['Field']}"];
            }
            //* if domain defaults == server default remove them from domain config (avoid duplicate entries in config file and makes it a lot smaller)
            if (!$full_server_conf && $domain_default["{$value['Field']}"] == $server_default["{$value['Field']}"]) {
                unset($domain_default["{$value['Field']}"]);
            }
        }
        unset($domain_default['sogo_id'], $domain_default['sys_groupid'], $domain_default['sys_perm_group'], $domain_default['domain_id'], $domain_default['sys_userid'], $domain_default['sys_perm_user'], $domain_default['sys_perm_other']);
        $app->sogo_helper->logDebug("sogo_plugin::__get_domain_config(): ENDED (domain_default)");
        return $domain_default;
    }

    /**
     * method to remove a sogo user from sogo storage
     * @global app $app
     * @global array $conf
     * @param string $email the email address to remove
     */
    private function __delete_mail_user($email) {
        global $app, $conf;
        if (!empty($email) && (strpos($email, '@') !== FALSE)) {
            $cmd = "{$conf['sogo_su_command']} {$conf['sogo_tool_binary']} remove " . escapeshellarg($email);
            $app->sogo_helper->logDebug("sogo_plugin::remove_sogo_mail_user() \n\t - CALL:{$cmd}");
            exec($cmd);
            $usrDom = explode('@', $email);
            $sqlres = & $app->sogo_helper->sqlConnect();
            $sqlres->query("DELETE FROM `{$app->sogo_helper->get_valid_sogo_table_name($usrDom[1])}_users` WHERE `c_uid` = '{$sqlres->escape_string($email)}'");
            if ($sqlres->error) $app->sogo_helper->logDebug("sogo_plugin::remove_sogo_mail_user() \n\t - SQL Error: {$sqlres->error}");
        }
    }

}
