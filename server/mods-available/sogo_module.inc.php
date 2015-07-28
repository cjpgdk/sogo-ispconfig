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
        return false;
    }

    /**
     * 
     * @global app $app
     * @global type $conf
     */
    function onLoad() {
        global $app, $conf;

        $app->plugins->announceEvents($this->module_name, $this->actions_available);

        $app->modules->registerTableHook('sogo_config', $this->module_name, 'process');

        $app->modules->registerTableHook('sogo_domains', $this->module_name, 'process');

        $app->modules->registerTableHook('sogo_module', $this->module_name, 'process');

        $app->services->registerService('sogo', $this->module_name, 'restartSOGo');

        $app->services->registerService('sogoForeceRestart', $this->module_name, 'foreceRestart');

        $app->services->registerService('sogoConfigRebuild', $this->module_name, 'rebuildConfig');
    }

    /**
     * 
     * @global app $app
     * @global type $conf
     * @param type $action
     */
    function rebuildConfig($action = NULL) {
        libxml_use_internal_errors(true); //* handle errors internally from sogo_config
        global $app, $conf;
        $sogodomsconf = "";

        $app->uses('sogo_helper,sogo_config');
        if ($sconf = $app->sogo_helper->get_server_config()) {
            $app->sogo_helper->explode2array($sconf['SOGoMailListViewColumnsOrder'], ',', false, NULL, NULL, $null);
            $app->sogo_helper->explode2array($sconf['SOGoCalendarDefaultRoles'], ',', false, NULL, NULL, $null);
            $app->sogo_config->createConfig(array('sogod' => $sconf));
            if ($mail_domains = $app->sogo_helper->get_mail_domain_names('y'/* Only active domains */)) {
                //* Start: mail domains loop
                foreach ($mail_domains as $value) {
                    if (!$app->sogo_helper->sogo_table_exists($value['domain'])) {
                        if ($app->sogo_helper->has_mail_users($value['domain'])) {
                            $app->sogo_helper->create_sogo_table($value['domain']);
                        } else
                            continue;
                    }
                    $dconf = $app->sogo_helper->get_domain_config($value['domain'], TRUE);
                    $tpl = $app->sogo_helper->getTemplateObject("sogo_domain.master");
                    if ($tpl !== null && $tpl instanceof tpl) {
                        //* Start: loop domain config
                        foreach ($dconf as $key => $value2) {
                            if (($sconf[$key] == $value2 || $key == 'server_name') && ($key != 'SOGoSMTPServer')) {
                                //* skip config settings that is default the server!
                            } else if ($key == 'SOGoSuperUsernames') {
                                $app->sogo_helper->explode2array($dconf['SOGoSuperUsernames'], ',', true, 'SOGoSuperUsername', 'SOGoSuperUsernames', $tpl);
                            } else if ($key == 'SOGoCalendarDefaultRoles') {
                                if (implode(',', $sconf[$key]) == $dconf['SOGoCalendarDefaultRoles'])
                                    continue;
                                $app->sogo_helper->explode2array($dconf['SOGoCalendarDefaultRoles'], ',', true, 'SOGoCalendarDefaultRole', 'SOGoCalendarDefaultRoles', $tpl);
                            } else if ($key == 'SOGoMailListViewColumnsOrder') {
                                if (implode(',', $sconf[$key]) == $dconf['SOGoMailListViewColumnsOrder'])
                                    continue;
                                $app->sogo_helper->explode2array($dconf['SOGoMailListViewColumnsOrder'], ',', true, 'SOGoMailListViewColumn', 'SOGoMailListViewColumnsOrder', $tpl);
                            } else if ($key == 'SOGoMailMessageCheck' || $key == 'SOGoRefreshViewCheck') {
                                //* write both for compatibility with older SOGo versions
                                $tpl->setVar('SOGoMailMessageCheck', $value2);
                                $tpl->setVar('SOGoRefreshViewCheck', $value2);
                            } else
                                $tpl->setVar($key, $value2); //* default isset as normal var
                        }
                        //* END: loop domain config
                        $tpl->setVar('domain', $value['domain']);

                        if (!isset($conf['sogo_unique_id_method']) || (!function_exists($conf['sogo_unique_id_method']) && $conf['sogo_unique_id_method'] != "plain"))
                            $conf['sogo_unique_id_method'] = "md5";
                        $conf['sogo_unique_id_method'] = strtolower($conf['sogo_unique_id_method']);
                        if ($conf['sogo_unique_id_method'] == "plain")
                            $tpl->setVar('SOGOUNIQID', $value['domain']);
                        else
                            $tpl->setVar('SOGOUNIQID', $conf['sogo_unique_id_method']($value['domain']));

                        $tpl->setVar('CONNECTIONVIEWURL', "mysql://{$conf['sogo_database_user']}:{$conf['sogo_database_passwd']}@{$conf['sogo_database_host']}:{$conf['sogo_database_port']}/{$conf['sogo_database_name']}/{$app->sogo_helper->get_valid_sogo_table_name($value['domain'])}");
                        $tpl->setVar($conf['sogo_domain_extra_vars']);
                        $MailFieldNames = array();
                        $dtacount = (int) $app->sogo_helper->get_sogo_table_alias_column_count($value['domain']); //* get alias columns in table for domain
                        for ($i = 0; $i < $dtacount; $i++) {
                            $MailFieldNames[] = array('MailFieldName' => 'alias_' . $i);
                        }
                        $tpl->setLoop('MailFieldNames', $MailFieldNames); //* set alias names loop
                        //* @todo move this so the missing 'idn_mail' column is created when the domain is checked (sogo_helper::sogo_table_exists|create_sogo_table)
                        $sqlObj = & $app->sogo_helper->sqlConnect();
                        $has_idn_column_sql = "SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_NAME`='{$sqlObj->escape_string($app->sogo_helper->get_valid_sogo_table_name($value['domain']))}' AND `TABLE_SCHEMA`='{$conf['sogo_database_name']}' AND `COLUMN_NAME` = 'idn_mail'";
                        $tmp = $sqlObj->query($has_idn_column_sql);
                        $has_idn_column = (bool) (count($tmp->fetch_assoc()) > 0);
                        $tpl->setVar('idn_mail', ($has_idn_column ? 1 : 0));
                        unset($tmp);
                        $sogodomsconf .= str_replace(array('{SERVERNAME}', '{domain}'), array((isset($dconf['server_name_real']) ? $dconf['server_name_real'] : $dconf['server_name']), $value['domain']), $tpl->grab());
                    }
                }
                //* END: mail domains loop
            }
            $this_server = $app->sogo_helper->get_server((int) $conf['server_id']);
            $replace_vars = array('{SOGODOMAINSCONF}', '{SOGOUSERN}', '{SOGOUSERPW}', '{MYSQLHOST}', '{MYSQLPORT}', '{SOGODB}', '{SERVERNAME}',);
            $replace_values = array($sogodomsconf, $conf['sogo_database_user'], $conf['sogo_database_passwd'], $conf['sogo_database_host'], $conf['sogo_database_port'], $conf['sogo_database_name'], $this_server['server_name'],);
            //* replace default vars in default sogo config
            $sogod = $app->sogo_config->getConfigReplace(sogo_config::CONFIG_FULL, $replace_vars, $replace_values);
            //* replace default vars in sogo config (sogod.plist)
            $sogodplist = $app->sogo_config->getConfigReplace(sogo_config::CONFIG_PLIST, $replace_vars, $replace_values);
            //* load it as DOMDocument Object (this validates the XML)
            if ($app->sogo_config->loadSOGoConfigString($sogod) !== FALSE) {
                unset($app->sogo_config); //* unset everything (- ~5kB per. domain)
                $result = file_put_contents($conf['sogo_gnu_step_defaults'], $sogod);
                $app->uses('system');
                //* if sogo system user and group config var is set, set permissions
                if (isset($conf['sogo_system_group']) && isset($conf['sogo_system_user'])) {
                    if (
                            (property_exists($app, 'system') && method_exists($app->system, 'chgrp')) &&
                            (property_exists($app, 'system') && method_exists($app->system, 'chown'))
                    ) {
                        if (
                                $app->system->chown($conf['sogo_gnu_step_defaults'], $conf['sogo_system_user'], true) &&
                                $app->system->chgrp($conf['sogo_gnu_step_defaults'], $conf['sogo_system_group'], true)
                        ) {
                            if (property_exists($app, 'system') && method_exists($app->system, 'chmod'))
                                $app->system->chmod($conf['sogo_gnu_step_defaults'], 0600, true);
                            else {
                                @chmod($conf['sogo_gnu_step_defaults'], 0600);
                            }
                        }
                    } else {
                        if (
                                @chown($conf['sogo_gnu_step_defaults'], $conf['sogo_system_user']) &&
                                @chgrp($conf['sogo_gnu_step_defaults'], $conf['sogo_system_group'])
                        ) {
                            @chmod($conf['sogo_gnu_step_defaults'], 0600);
                        }
                    }
                }
                //* debug the result
                $app->log("Write file [{$conf['sogo_gnu_step_defaults']}] " . ($result ? "Succeeded" : "Failed"), LOGLEVEL_DEBUG);
                //* check if file exists (sogod.plist)
                if (file_exists($conf['sogo_gnu_step_defaults_sogod.plist'])) {
                    $result = file_put_contents($conf['sogo_gnu_step_defaults_sogod.plist'], $sogodplist);
                    //* if sogo system user and group config var is set, set permissions
                    if (isset($conf['sogo_system_group']) && isset($conf['sogo_system_user'])) {
                        if (
                                (property_exists($app, 'system') && method_exists($app->system, 'chgrp')) &&
                                (property_exists($app, 'system') && method_exists($app->system, 'chown'))
                        ) {
                            if (
                                    $app->system->chown($conf['sogo_gnu_step_defaults_sogod.plist'], $conf['sogo_system_user'], true) &&
                                    $app->system->chgrp($conf['sogo_gnu_step_defaults_sogod.plist'], $conf['sogo_system_group'], true)
                            ) {
                                if (property_exists($app, 'system') && method_exists($app->system, 'chmod'))
                                    $app->system->chmod($conf['sogo_gnu_step_defaults_sogod.plist'], 0600, true);
                                else {
                                    @chmod($conf['sogo_gnu_step_defaults_sogod.plist'], 0600);
                                }
                            }
                        } else {
                            if (
                                    @chown($conf['sogo_gnu_step_defaults_sogod.plist'], $conf['sogo_system_user']) &&
                                    @chgrp($conf['sogo_gnu_step_defaults_sogod.plist'], $conf['sogo_system_group'])
                            ) {
                                @chmod($conf['sogo_gnu_step_defaults_sogod.plist'], 0600);
                            }
                        }
                    }
                    $app->log("Write file [{$conf['sogo_gnu_step_defaults_sogod.plist']}] " . ($result ? "Succeeded" : "Failed"), LOGLEVEL_DEBUG);
                }
                //* test the result
                if ($result) {
                    //* log more debug and restart
                    $app->log("rebuilded SOGo config OK", LOGLEVEL_DEBUG);
                    $this->_save_sogo_config_to_system_default();
                    $this->restartSOGo('restart');
                    //$app->services->restartServiceDelayed('sogo', 'restart');
                } else {
                    //* log error somthing when't wrong (check: /var/log/ispconfig/cron.log)
                    $app->log("Unable to save new SOGo config...", LOGLEVEL_ERROR);
                }
            } else {
                //* in case we build invalid SOGo config create error
                $error_string = "SOGo Config is not valid:" . PHP_EOL;
                $libxmlerrors = libxml_get_errors();
                $xml = explode("\n", $sogod);
                foreach ($libxmlerrors as $error) {
                    $error_string .= $xml[$error->line - 1] . PHP_EOL;
                    $error_string .= str_repeat('-', $error->column) . "^" . PHP_EOL;
                    switch ($error->level) {
                        case LIBXML_ERR_WARNING:
                            $error_string .= "Warning $error->code: ";
                            break;
                        case LIBXML_ERR_ERROR:
                            $error_string .= "Error $error->code: ";
                            break;
                        case LIBXML_ERR_FATAL:
                            $error_string .= "Fatal Error $error->code: ";
                            break;
                    }
                    $error_string .= trim($error->message) . PHP_EOL .
                            "\tLine: $error->line" . PHP_EOL .
                            "\tColumn: $error->column" . PHP_EOL;
                    $error_string .= str_repeat('-', $error->column) . PHP_EOL;
                }
                unset($xml);

                if (LOGLEVEL_DEBUG >= $conf['log_priority']) {
                    //* only log FULL xml in debug mode
                    $error_string .= PHP_EOL . str_repeat('=', 25) . PHP_EOL . $sogod . PHP_EOL . str_repeat('=', 25);
                }
                $app->log($error_string, LOGLEVEL_ERROR);
            }
        } else {
            $app->log("SOGo Server config not found", LOGLEVEL_DEBUG);
        }
        libxml_clear_errors();
    }

    function _save_sogo_config_to_system_default() {
        global $app, $conf;
        if (isset($conf['sogo_system_default_conf']) && file_exists($conf['sogo_system_default_conf'])) {
            $cmd_arg = escapeshellarg("{$conf['sogo_tool_binary']}") . " dump-defaults > " . escapeshellarg("{$conf['sogo_system_default_conf']}");
            $cmd = str_replace('{command}', $cmd_arg, $conf['sogo_su_command']);
            $app->log("sogo_module: CALL:{$cmd}", LOGLEVEL_DEBUG);
            exec($cmd);
        } else {
            $app->log("sogo_module: Config variable 'sogo_system_default_conf' not isset, not saving configuration to system default", LOGLEVEL_DEBUG);
        }
    }

    function restartSOGo($action = 'restart') {
        global $app, $conf;
        if (file_exists($conf['init_scripts'] . '/sogo'))
            exec($conf['init_scripts'] . '/sogo ' . $action);
        else if (file_exists($conf['init_scripts'] . '/sogod'))
            exec($conf['init_scripts'] . '/sogod ' . $action);
        else
            $app->log("Unable to locate SOGo init script, not restarting SOGo..", LOGLEVEL_WARN);
    }

    //* in some rare cases we need to stop and start sogo and memcached to make it all work
    function foreceRestart($action = NULL) {

        //* Stop sogo
        if (file_exists($conf['init_scripts'] . '/sogo'))
            exec($conf['init_scripts'] . '/sogo stop');
        else if (file_exists($conf['init_scripts'] . '/sogod'))
            exec($conf['init_scripts'] . '/sogod stop');

        //* Stop memcached
        if (file_exists($conf['init_scripts'] . '/memcached'))
            exec($conf['init_scripts'] . '/memcached stop');

        sleep(5); //* giv it 5 seconds to compleate memcached is a ***** sometimes
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
