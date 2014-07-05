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

class sogo_config_plugin {

    var $plugin_name = 'sogo_config_plugin';
    var $class_name = 'sogo_config_plugin';
    var $sogo_su_cmd = "sudo -u sogo";
    var $sogo_su = "sogo";
    var $sogopw = 'yPqziEiF83KH8A87ox0edVfdv';
    var $sogouser = 'sogodbuser';
    var $sogodb = 'sogodb';
    var $ispcdb = 'dbispconfig'; //* kept here to allow for difrent database with less data.!
    var $sogobinary = '/usr/sbin/sogod'; //* hmm not used, maybe remove it
    var $sogotoolbinary = '/usr/sbin/sogo-tool';
    var $sogohomedir = '/var/lib/sogo'; //* hmm not used, maybe remove it
    var $sogoconffile = '/var/lib/sogo/GNUstep/Defaults/.GNUstepDefaults';
    var $templ_file = '/usr/local/ispconfig/server/conf/sogo.conf';
    var $templ_domains_dir = '/usr/local/ispconfig/server/conf/sogo_domains';
    var $templ_override_file = '/usr/local/ispconfig/server/conf-custom/sogo/sogo.conf';
    var $templ_override_domains_dir = '/usr/local/ispconfig/server/conf-custom/sogo/domains';
    var $mysql_server_host = '127.0.0.1:3306';

    /**
     * set to fals to disable mail alias
     * recommened on systems with limited cpu 
     * @var boolean
     */
    var $allow_mail_alias = true;

    /**
     * set this to true to allways write the sogod.plist
     * in onLoad() we check if this file exists, if it do we write to it
     * @var boolean 
     */
    var $write_sogodplist = false;
    
    /**
     * delete old backups after x days
     * default: 7
     * @var string 
     */
    var $delete_old_backups_after = "7";

    function onInstall() {
        global $conf;
        if ($conf['services']['mail'] == true) {
            if (!empty($this->ispcdb) &&
                    !empty($this->sogo_su_cmd) &&
                    !empty($this->sogopw) &&
                    !empty($this->sogouser) &&
                    !empty($this->sogodb) &&
                    !empty($this->sogotoolbinary) &&
                    !empty($this->sogoconffile) &&
                    !empty($this->templ_file) &&
                    !empty($this->templ_domains_dir) &&
                    !empty($this->mysql_server_host)) {
                if (!file_exists($this->sogotoolbinary)) {
                    return false;
                }
                if (!is_dir($this->templ_domains_dir)) {
                    return false;
                }
                if (!file_exists($this->templ_file)) {
                    return false;
                }
                if (!file_exists($this->sogoconffile) || !is_writable($this->sogoconffile)) {
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    function onLoad() {
        global $app;
        $app->plugins->registerEvent('mail_domain_delete', $this->plugin_name, 'reconfigure');
        $app->plugins->registerEvent('mail_domain_insert', $this->plugin_name, 'reconfigure');
        $app->plugins->registerEvent('mail_domain_update', $this->plugin_name, 'reconfigure');

        $app->plugins->registerEvent('mail_user_delete', $this->plugin_name, 'remove_sogo_mail_user');

        $app->plugins->registerEvent('fake_tb_sogo_update', $this->plugin_name, 'reconfigure');
        $app->plugins->registerEvent('fake_tb_sogo_delete', $this->plugin_name, 'reconfigure');
        $app->plugins->registerEvent('fake_tb_sogo_insert', $this->plugin_name, 'reconfigure');

        //* check if sogod.plist exists and tell plugin to write it.!
        $this->sogoconffile2 = str_replace('.GNUstepDefaults', 'sogod.plist', $this->sogoconffile);
        $this->sogod_templ_override_file = str_replace('sogo.conf', 'sogo-sogod.plist.conf', $this->templ_override_file);
        $this->sogod_templ_file = str_replace('sogo.conf', 'sogo-sogod.plist.conf', $this->templ_file);
        if (file_exists($this->sogoconffile2)) {
            $this->write_sogodplist = true;
        }
    }


    /**
     * Backup configuration files.!
     * @global app $app
     */
    function backupConf($file_postfix = "") {
        global $app, $conf;
        $app->uses('getconf, system');
        $app->log("Started sogo_config_plugin::backupConf($file_postfix)", LOGLEVEL_DEBUG);

        $server_config = $app->getconf->get_server_config($conf['server_id'], 'server');

        $backup_dir_is_ready = true;
        //* check if is_mounted exists
        if (method_exists($app->system, 'is_mounted')) {
            //* -- BORROWED FROM BACKUP PLUGIN (plugins-available\backup_plugin.inc.php)
            //* mount backup directory, if necessary
            $server_config['backup_dir_mount_cmd'] = trim($server_config['backup_dir_mount_cmd']);
            if ($server_config['backup_dir_is_mount'] == 'y' && $server_config['backup_dir_mount_cmd'] != '') {
                if (!$app->system->is_mounted($server_config['backup_dir'])) {
                    exec(escapeshellcmd($server_config['backup_dir_mount_cmd']));
                    sleep(1);
                    if (!$app->system->is_mounted($server_config['backup_dir']))
                        $backup_dir_is_ready = false;
                }
            }
            //* -- /BORROWED FROM BACKUP PLUGIN (plugins-available\backup_plugin.inc.php)
        }
        if (!$backup_dir_is_ready)
            return;
        if (!is_dir($server_config['backup_dir'])) {
            return;
        }

        $backup_dir = $server_config['backup_dir'] . '/sogo';
        @mkdir($backup_dir);
        if (!is_dir($backup_dir)) {
            return;
        }

        //* delete old backups
        exec('find ' . $backup_dir . ' -mtime +' . $this->delete_old_backups_after . '/* | xargs rm -rf');

        $backup_dir = $backup_dir . '/' . date('Y-m-d');
        @mkdir($backup_dir);
        @mkdir($backup_dir . '/conf');
        //* backup all default config files not just SOGo
        exec('tar -zcvf ' . $backup_dir . '/conf/sogo-conf-' . date('H.i.s') . $file_postfix . '.tar.gz ' . str_replace('sogo.conf', '', $this->templ_file), $output);
        $app->log("\t\t - OUTPUT[conf]: " . print_r($output, true), LOGLEVEL_DEBUG);
        unset($output);

        @mkdir($backup_dir . '/conf-custom');
        @mkdir($backup_dir . '/conf-custom/sogo');
        exec('tar -zcvf ' . $backup_dir . '/conf-custom/sogo/sogo-conf-' . date('H.i.s') . $file_postfix . '.tar.gz ' . str_replace('sogo.conf', '', $this->templ_override_file), $output);
        $app->log("\t\t - OUTPUT[conf-custom]: " . print_r($output, true), LOGLEVEL_DEBUG);

        $app->log("Ended sogo_config_plugin::backupConf($file_postfix)", LOGLEVEL_DEBUG);
    }

    /**
     * create a back of the sogo database
     * @global app $app
     * @param string $file
     * @param boolean $compress
     */
    function backupDb($file, $compress = TRUE) {
        global $app, $conf;
        $app->uses('getconf, system');
        $app->log("Started sogo_config_plugin::backupDb({$file}, " . ($compress ? 'TRUE' : 'FALSE') . ")", LOGLEVEL_DEBUG);

        $server_config = $app->getconf->get_server_config($conf['server_id'], 'server');

        $backup_dir_is_ready = true;
        if (method_exists($app->system, 'is_mounted')) {
            //* -- BORROWED FROM BACKUP PLUGIN (plugins-available\backup_plugin.inc.php)
            //* mount backup directory, if necessary
            $server_config['backup_dir_mount_cmd'] = trim($server_config['backup_dir_mount_cmd']);
            if ($server_config['backup_dir_is_mount'] == 'y' && $server_config['backup_dir_mount_cmd'] != '') {
                if (!$app->system->is_mounted($server_config['backup_dir'])) {
                    exec(escapeshellcmd($server_config['backup_dir_mount_cmd']));
                    sleep(1);
                    if (!$app->system->is_mounted($server_config['backup_dir']))
                        $backup_dir_is_ready = false;
                }
            }
            //* -- /BORROWED FROM BACKUP PLUGIN (plugins-available\backup_plugin.inc.php)
        }
        if (!$backup_dir_is_ready)
            return;
        if (!is_dir($server_config['backup_dir'])) {
            return;
        }
        $backup_dir = $server_config['backup_dir'] . '/sogo';
        @mkdir($backup_dir);
        if (!is_dir($backup_dir)) {
            return;
        }
        $file = $backup_dir . '/' . date('Y.m.d.H.i.s') . '-' . $file;
        $h = explode(':', $this->mysql_server_host);
        if ($compress)
            exec("mysqldump -h {$h[0]} -u {$this->sogouser} -p{$this->sogopw} {$this->sogodb} | gzip -9 > {$file}.gz");
        else
            exec("mysqldump -h {$h[0]} -u {$this->sogouser} -p{$this->sogopw} {$this->sogodb} > {$file}");
        $app->log("Ended sogo_config_plugin::backupDb({$file}, " . ($compress ? 'TRUE' : 'FALSE') . ")", LOGLEVEL_DEBUG);
    }

//    function restoreDb($file,$compressed=TRUE) {
//        $h = explode(':', $this->mysql_server_host);
//        if ($compressed) {
//            exec("gunzip < {$file} | mysql -h {$h[0]} -u {$this->sogouser} -p{$this->sogopw} {$this->sogodb}");
//        }
//    }

    /**
     * method to remove a sogo user from sogo storage
     * @global app $app
     * @global array $conf
     * @param string $event_name
     * @param array $data array of old and new data
     */
    function remove_sogo_mail_user($event_name, $data) {
        global $app, $conf;
        $app->log('Started sogo_config_plugin::remove_sogo_mail_user(' . $data['old']['login'] . ')', LOGLEVEL_DEBUG);
        $this->backupDb('before_remove' . str_replace('@', '.', $data['old']['login']) . '.sql', TRUE);
        if ($event_name == 'mail_user_delete') {
            $cmd = $this->sogo_su_cmd . ' ' . $this->sogotoolbinary . ' remove ' . escapeshellarg($data['old']['login']);
            $app->log('sogo_config_plugin::remove_sogo_mail_user()' . PHP_EOL . "\t - CALL:{$cmd}", LOGLEVEL_DEBUG);
            exec($cmd);
        }
        $this->backupDb('after_remove' . str_replace('@', '.', $data['old']['login']) . '.sql', TRUE);
        $app->log('Ended sogo_config_plugin::remove_sogo_mail_user(' . $data['old']['login'] . ')', LOGLEVEL_DEBUG);
    }

    /**
     * method to reconfigure the sogo server with newly created/delete domains
     * @global app $app
     * @global array $conf see lib/config.inc.php
     * @param string $event_name the event to exec
     * @param array $data array of old and new data
     * @todo add validation on that sogo config..!
     */
    function reconfigure($event_name, $data) {
        global $app, $conf;
        $app->log('Started sogo_config_plugin::reconfigure(' . $event_name . ',Array(...))', LOGLEVEL_DEBUG);
        $flag = false;
        if ($event_name == 'mail_domain_delete') {
            $flag = $this->remove_sogo_maildomain((isset($data['new']['domain']) ? $data['new']['domain'] : $data['old']['domain']));
        } else if (($event_name == 'mail_domain_insert') || ($event_name == 'mail_domain_update')) {
            $flag = true;
        } else if (($event_name == 'fake_tb_sogo_update') || ($event_name == 'fake_tb_sogo_delete') || ($event_name == 'fake_tb_sogo_insert')) {
            //* lets create the sogod.plist file if data is set
            if (
                    isset($data['new']['server_id']) &&
                    (intval($data['new']['server_id']) == intval($conf['server_id'])) &&
                    (
                    isset($data['new']['config']['sogod']) &&
                    is_array($data['new']['config']['sogod']) &&
                    !empty($data['new']['config']['sogod'])
                    )
            ) {
                if ($this->write_sogodplist) {
                    //* please preseve the tabs "\t" so the file is easy to read if we need to do some debugging
                    $sogodplist_conf = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . PHP_EOL;
                    $sogodplist_conf .="<!DOCTYPE plist PUBLIC \"-//GNUstep//DTD plist 0.9//EN\" \"http://www.gnustep.org/plist-0_9.xml\">" . PHP_EOL;
                    $sogodplist_conf .="<plist version=\"0.9\">" . PHP_EOL;
                    $sogodplist_conf .="\t<key>sogod</key>" . PHP_EOL;
                    $sogodplist_conf .="\t<dict>" . PHP_EOL;
                    foreach ($data['new']['config']['sogod'] as $key => $value) {
                        //* we do not write empty values!
                        if (!empty($value) && is_string($value)) {
                            $sogodplist_conf .= "\t\t<key>{$key}</key>" . PHP_EOL
                                    . "\t\t<string>{$value}</string>" . PHP_EOL;
                        } else if (!empty($value) && is_array($value)) {
                            $sogodplist_conf .= "\t\t<key>{$key}</key>" . PHP_EOL
                                    . "\t\t<array>" . PHP_EOL;
                            foreach ($value as $k => $v) {
                                $sogodplist_conf .= "\t\t\t<string>{$v}</string>" . PHP_EOL;
                            }
                            $sogodplist_conf .= "\t\t</array>" . PHP_EOL;
                        }
                    }
                    $sogodplist_conf .="\t\t<key>domains</key>" . PHP_EOL;
                    $sogodplist_conf .="\t\t<dict>{{SOGODOMAINSCONF}}</dict>" . PHP_EOL;
                    $sogodplist_conf .="\t</dict>" . PHP_EOL;
                    $sogodplist_conf .="</plist>" . PHP_EOL;

                    if (!file_put_contents($this->sogoconffile2, $sogodplist_conf)) {
                        $app->log('ERROR. unable to reconfigure SOGo..', LOGLEVEL_ERROR);
                        return;
                    }
                    exec("chown {$this->sogo_su}:{$this->sogo_su} {$this->sogoconffile2}");
                }
            }
            $flag = true;
        } else {
            //* we log this as debug since thats not this scripts error
            $app->log("Wrong event_name sendt to sogo_config_plugin::reconfigure(): got [{$event_name}] but only accepts mail_domain_delete|mail_domain_insert|mail_domain_update", LOGLEVEL_DEBUG);
        }
        
        if ($flag) {
            $this->backupDb('before_reconfigure.sql', TRUE);
            $this->backupConf('_before');
            $active_mail_domains = $app->db->queryAllRecords('SELECT `domain`,`server_id` FROM `mail_domain` WHERE `active`=\'y\' AND CONCAT(\'@\',mail_domain.domain) NOT IN (SELECT mail_forwarding.source FROM mail_forwarding WHERE mail_forwarding.active=\'y\' AND mail_forwarding.type=\'aliasdomain\')');
            $app->log("\t - WE GOT: " . count($active_mail_domains) . " Mail domains", LOGLEVEL_DEBUG);
            $sogo_conf = "";
            if (file_exists($this->templ_override_file)) {
                $app->log("\t - Loaded config file: {$this->templ_override_file}", LOGLEVEL_DEBUG);
                $sogo_conf = file_get_contents($this->templ_override_file);
            } else if (file_exists($this->templ_file)) {
                $app->log("\t - Loaded config file: {$this->templ_file}", LOGLEVEL_DEBUG);
                $sogo_conf = file_get_contents($this->templ_file);
            } else {
                $app->log("Unable to loacte a SOGo configuration file!", LOGLEVEL_ERROR);
                return;
            }
            $tmp_conf = "";
            foreach ($active_mail_domains as $vd) {
                $tmp_conf .= $this->build_conf_sogo_maildomain($vd['domain'], $vd['server_id']);
                //* create if not exist
                $this->create_sogo_view($vd['domain']);
            }
            $sogo_conf = str_replace('{{SOGODOMAINSCONF}}', $tmp_conf, $sogo_conf);

            //* get sogod.plist template, if not isset
            if (!isset($sogodplist_conf)) {
                if (file_exists($this->sogod_templ_override_file)) {
                    $app->log("\t - Loaded config file: {$this->sogod_templ_override_file}", LOGLEVEL_DEBUG);
                    $sogodplist_conf = file_get_contents($this->sogod_templ_override_file);
                } else if (file_exists($this->sogod_templ_file)) {
                    $app->log("\t - Loaded config file: {$this->sogod_templ_file}", LOGLEVEL_DEBUG);
                    $sogodplist_conf = file_get_contents($this->sogod_templ_file);
                } else {
                    $app->log("Unable to loacte a SOGo (sogod.plist) configuration file!\n{$this->sogod_templ_override_file}\n{$this->sogod_templ_file}", LOGLEVEL_ERROR);
                }
            }
            //* write sogod.plist
            if ($this->write_sogodplist && isset($sogodplist_conf)) {
                $sogodplist_conf = str_replace('{{SOGODOMAINSCONF}}', $tmp_conf, $sogodplist_conf);
                if (!file_put_contents($this->sogoconffile2, $sogodplist_conf)) {
                    $app->log('ERROR. unable to reconfigure SOGo (sogod.plist)..', LOGLEVEL_ERROR);
                }
                //* chown to sogo user
                exec("chown {$this->sogo_su}:{$this->sogo_su} {$this->sogoconffile2}");
            }
            unset($tmp_conf);

            //* write .GNUstepDefaults
            if (!file_put_contents($this->sogoconffile, $sogo_conf)) {
                $app->log('ERROR. unable to reconfigure SOGo..', LOGLEVEL_ERROR);
                return;
            } else {
                //* chown to sogo user
                exec("chown {$this->sogo_su}:{$this->sogo_su} {$this->sogoconffile}");
                $app->log("\t - SOGo config file: {$this->sogoconffile}, has bean saved", LOGLEVEL_DEBUG);
                $app->log("\t - Restarting SOGo", LOGLEVEL_DEBUG);
            }
            //* trigger a delayed restart of sogo
            $app->services->restartServiceDelayed('sogo', 'restart');
            $this->backupDb('after_reconfigure.sql', TRUE);
            $this->backupConf('_after');
            $app->log('Ended sogo_config_plugin::reconfigure(' . $event_name . ',Array(...))', LOGLEVEL_DEBUG);
        }
    }

    /**
     * check domain is valid
     * @param string $domain_name
     * @return boolean
     * @see http://stackoverflow.com/a/4694816
     */
    function is_valid_domain_name($domain_name) {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
                && preg_match("/^.{1,253}$/", $domain_name) //overall length check
                && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name) ); //length of each label
    }

    /**
     * remove a mail domain from the sogo database and config
     * @global app $app
     * @global array $conf
     * @param string $domain_name
     * @return boolean
     */
    function remove_sogo_maildomain($domain_name) {
        global $app, $conf;
        $app->log('Started sogo_config_plugin::remove_sogo_maildomain(' . $domain_name . ')', LOGLEVEL_DEBUG);
        $this->backupDb('before_remove' . $domain_name . '.sql', TRUE);
        if (empty($domain_name) || !$this->is_valid_domain_name($domain_name)) {
            if (!empty($domain_name)) {
                $app->log('ERROR. removeing sogo mail domain.. domain invalid [' . $domain_name . ']', LOGLEVEL_ERROR);
            } else {
                //* i can't decide if this is an error so log as debug
                $app->log('ERROR. sogo_config_plugin::remove_sogo_maildomain(): removeing sogo mail domain.. domain is empty!!', LOGLEVEL_DEBUG);
            }
            return false;
        }
        $dom_no_point = $this->_get_valid_sogo_table_name($domain_name);
        $sqlres = & $this->_sqlConnect();
        $app->log("\t DROP VIEW: sogo_users_{$dom_no_point}", LOGLEVEL_DEBUG);
        $sqlres->query("DROP VIEW `sogo_users_{$dom_no_point}`");
        //* remove config files for this domain.
        if (file_exists("{$this->templ_domains_dir}/{$domain_name}.conf")) {
            $app->log("\t UNLINK FIle: {$this->templ_domains_dir}/{$domain_name}.conf", LOGLEVEL_DEBUG);
            @unlink("{$this->templ_domains_dir}/{$domain_name}.conf");
        }
        if (file_exists("{$this->templ_override_domains_dir}/{$domain_name}.conf")) {
            $app->log("\t UNLINK FIle: {$this->templ_override_domains_dir}/{$domain_name}.conf", LOGLEVEL_DEBUG);
            @unlink("{$this->templ_override_domains_dir}/{$domain_name}.conf");
        }
        $this->backupDb('after_remove' . $domain_name . '.sql', TRUE);
        $app->log('Ended sogo_config_plugin::remove_sogo_maildomain(' . $domain_name . ')', LOGLEVEL_DEBUG);
        return true;
    }

    /**
     * SOGo wont lookup a table with - or . in them so replace with _
     * @param string $domain_name
     * @return string 
     */
    function _get_valid_sogo_table_name($domain_name) {
        return str_replace('-', '_', str_replace('.', '_', $domain_name));
    }

    /**
     * check if the sogo view exists in the database
     * @global app $app
     * @param string $domain
     * @return boolean
     */
    function _sogo_view_exists($domain) {
        global $app;
        $app->log("Started sogo_config_plugin::_sogo_view_exists({$domain})", LOGLEVEL_DEBUG);
        $domain = $this->_get_valid_sogo_table_name($domain);
        $sql1 = "SELECT `TABLE_NAME` FROM `information_schema`.`VIEWS` WHERE `TABLE_SCHEMA`='{$this->sogodb}' AND `TABLE_NAME`='sogo_users_{$domain}'";
        $sqlres = & $this->_sqlConnect();
        $tmp = $sqlres->query($sql1);
        while ($obj = $tmp->fetch_object()) {
            if ($obj->TABLE_NAME == 'sogo_users_' . $domain) {
                $app->log("Ended sogo_config_plugin::_sogo_view_exists({$domain}), exists", LOGLEVEL_DEBUG);
                return true;
            }
        }
        $app->log("Ended sogo_config_plugin::_sogo_view_exists({$domain}), NO VIEW", LOGLEVEL_DEBUG);
        return false;
    }

    /**
     * create a new sogo view if not exists
     * @global app $app
     * @global array $conf
     * @param string $domain_name
     * @return boolean
     */
    function create_sogo_view($domain_name) {
        global $app;
        $app->log("Started sogo_config_plugin::create_sogo_view({$domain_name})", LOGLEVEL_DEBUG);
        $sqlres = & $this->_sqlConnect();
        $dom_no_point = $this->_get_valid_sogo_table_name($domain_name);
        if ($this->_sogo_view_exists($dom_no_point)) {
            return true;
        }
        $app->log("\t Creating VIEW for domain: {$domain_name}", LOGLEVEL_DEBUG);
        if ($this->allow_mail_alias) {
            $sqlres->query("CREATE VIEW sogo_users_{$dom_no_point} AS SELECT DISTINCT
    `login` AS c_uid,
    `login` AS c_name,
    `password` AS c_password,
    `name` AS c_cn,
    `email` AS mail,
    (SELECT `server_name` FROM {$this->ispcdb}.`server`, {$this->ispcdb}.`mail_user` WHERE `mail_user`.`server_id`=`server`.`server_id` AND `server`.`mail_server`=1 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1) AS imap_host,
    (SELECT `mail_forwarding`.`source` FROM {$this->ispcdb}.`mail_forwarding`, {$this->ispcdb}.`mail_user` WHERE `mail_user`.`login`=`mail_forwarding`.`destination` AND `mail_forwarding`.`type`='alias' AND `mail_forwarding`.`active`='y' AND ispcmu.`login`=`mail_user`.`login` LIMIT 1)  AS mail_1,
    (SELECT `mail_forwarding`.`source` FROM {$this->ispcdb}.`mail_forwarding`, {$this->ispcdb}.`mail_user` WHERE `mail_user`.`login`=`mail_forwarding`.`destination` AND `mail_forwarding`.`type`='alias' AND `mail_forwarding`.`active`='y' AND `mail_forwarding`.`source` NOT LIKE mail_1 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1)  AS mail_2,
    (SELECT `mail_forwarding`.`source` FROM {$this->ispcdb}.`mail_forwarding`, {$this->ispcdb}.`mail_user` WHERE `mail_user`.`login`=`mail_forwarding`.`destination` AND `mail_forwarding`.`type`='alias' AND `mail_forwarding`.`active`='y' AND `mail_forwarding`.`source` NOT LIKE mail_1 AND `mail_forwarding`.`source` NOT LIKE mail_2 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1)  AS mail_3,
    (SELECT `mail_forwarding`.`source` FROM {$this->ispcdb}.`mail_forwarding`, {$this->ispcdb}.`mail_user`  WHERE `mail_user`.`login`=`mail_forwarding`.`destination` AND `mail_forwarding`.`type`='alias' AND `mail_forwarding`.`active`='y' AND `mail_forwarding`.`source` NOT LIKE mail_1 AND `mail_forwarding`.`source` NOT LIKE mail_2 AND `mail_forwarding`.`source` NOT LIKE mail_3 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1)  AS mail_4
FROM {$this->ispcdb}.`mail_forwarding`, {$this->ispcdb}.`mail_user` AS ispcmu 
 WHERE `email` LIKE '%@{$domain_name}' AND disableimap='n'");
        } else {
            $sqlres->query('
CREATE VIEW sogo_users_' . $dom_no_point . ' AS SELECT
    `login` AS c_uid,
    `login` AS c_name,
    `password` AS c_password,
    `name` AS c_cn,
    `email` AS mail,
    (SELECT `server_name` FROM ' . $this->ispcdb . '.`server`, ' . $this->ispcdb . '.`mail_user` WHERE `mail_user`.`server_id`=`server`.`server_id` AND `server`.`mail_server`=1 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1) AS imap_host 
FROM ' . $this->ispcdb . '.`mail_user` AS ispcmu  WHERE `email` LIKE \'%@' . $domain_name . '\' AND disableimap=\'n\'');
        }
        if (!empty($sqlres->error) || !empty($sqlres->error_list))
            $app->log('ERROR. unable to create SOGo view[sogo_users_' . $dom_no_point . '].. ' . $sqlres->error . (!empty($sqlres->error_list) && is_array($sqlres->error_list) ? ' :: ' . implode(PHP_EOL, $sqlres->error_list) : ''), LOGLEVEL_ERROR);
        $app->log("Ended sogo_config_plugin::create_sogo_view({$domain_name})", LOGLEVEL_DEBUG);
        return true;
    }

    /**
     * build the config for SOGo domain configuration.!
     * @global app $app
     * @param string $domain_name
     * @param int $sid
     * @return string
     */
    function build_conf_sogo_maildomain($domain_name, $sid = 1) {
        global $app;
        $app->log('Started sogo_config_plugin::build_conf_sogo_maildomain(' . $domain_name . ',' . $sid . ')', LOGLEVEL_DEBUG);
        $dom_no_point = $this->_get_valid_sogo_table_name($domain_name);
        /* For mail aliases..

         */
        $server_name_result = $app->db->queryOneRecord("SELECT `server_name` FROM `server` WHERE `server_id`=" . intval($sid));
        $sogo_conf = $this->_get_config_domain_contents($domain_name, (isset($server_name_result['server_name']) ? $server_name_result['server_name'] : ''));
        $sogo_conf_vars = array(
            '{{DOMAIN}}' => $domain_name,
            '{{DOMAINADMIN}}' => 'postmaster@' . $domain_name,
            '{{SOGOUNIQID}}' => $dom_no_point,
            '{{MAILALIAS}}' => ($this->allow_mail_alias ? "
                            <key>MailFieldNames</key>
                            <array>
                                <string>mail_1</string>
                                <string>mail_2</string>
                                <string>mail_3</string>
                                <string>mail_4</string>
                            </array>" : ''),
            '{{CONNECTIONVIEWURL}}' => "mysql://{$this->sogouser}:{$this->sogopw}@{$this->mysql_server_host}/{$this->sogodb}/sogo_users_{$dom_no_point}"
        );
        if (!empty($sogo_conf)) {
            foreach ($sogo_conf_vars as $key => $value) {
                $sogo_conf = preg_replace("/{$key}/i", $value, $sogo_conf);
            }
        } else {
            $app->log('unable to loacate a configuration file for domains..!', LOGLEVEL_ERROR);
        }
        $app->log('Ended sogo_config_plugin::build_conf_sogo_maildomain(' . $domain_name . ',' . $sid . ')', LOGLEVEL_DEBUG);
        return $sogo_conf;
    }

    /**
     * get the domain configuration file template
     * @global app $app
     * @param string $domain_name
     * @param string $server_name
     * @return string
     */
    function _get_config_domain_contents($domain_name, $server_name = "") {
        global $app;
        $return = "";
        $app->log('Started sogo_config_plugin::_get_config_domain_contents(' . $domain_name . ',' . $server_name . ')', LOGLEVEL_DEBUG);
        if (file_exists("{$this->templ_override_domains_dir}/{$domain_name}.conf")) {
            $app->log("\t Loaded Config: {$this->templ_override_domains_dir}/{$domain_name}.conf", LOGLEVEL_DEBUG);
            $return = file_get_contents("{$this->templ_override_domains_dir}/{$domain_name}.conf");
        } else if (file_exists("{$this->templ_domains_dir}/{$domain_name}.conf")) {
            $app->log("\t Loaded Config: {$this->templ_domains_dir}/{$domain_name}.conf", LOGLEVEL_DEBUG);
            $return = file_get_contents("{$this->templ_domains_dir}/{$domain_name}.conf");
        } else if ($server_name != '' && file_exists("{$this->templ_override_domains_dir}/{$server_name}.conf")) {
            $app->log("\t Loaded Config: {$this->templ_override_domains_dir}/{$server_name}.conf", LOGLEVEL_DEBUG);
            $return = file_get_contents("{$this->templ_override_domains_dir}/{$server_name}.conf");
        } else if ($server_name != '' && file_exists("{$this->templ_domains_dir}/{$server_name}.conf")) {
            $app->log("\t Loaded Config: {$this->templ_domains_dir}/{$server_name}.conf", LOGLEVEL_DEBUG);
            $return = file_get_contents("{$this->templ_domains_dir}/{$server_name}.conf");
        } else if (file_exists("{$this->templ_domains_dir}/domains_default.conf")) {
            $app->log("\t Loaded Config: {$this->templ_domains_dir}/domains_default.conf", LOGLEVEL_DEBUG);
            $return = file_get_contents("{$this->templ_domains_dir}/domains_default.conf");
        }
        $app->log('Ended sogo_config_plugin::_get_config_domain_contents(' . $domain_name . ',' . $server_name . ')', LOGLEVEL_DEBUG);
        return $return;
    }

    /** @var mysqli */
    static private $_sqlObject = NULL;

    /**
     * Get a mysqli connection object to the mysql database server
     * @return mysqli
     */
    function & _sqlConnect() {
        if (self::$_sqlObject == NULL) {
            $_sqlserver = explode(':', $this->mysql_server_host);
            self::$_sqlObject = new mysqli($_sqlserver[0], $this->sogouser, $this->sogopw, $this->sogodb, $_sqlserver[1]);
            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit;
            }
        }
        //* check if the connetion is still good.!
        if (self::$_sqlObject->ping()) {
            return self::$_sqlObject;
        } else {
            //* not good create a new one.
            $_sqlserver = explode(':', $this->mysql_server_host);
            self::$_sqlObject = new mysqli($_sqlserver[0], $this->sogouser, $this->sogopw, $this->sogodb, $_sqlserver[1]);
            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit;
            }
        }
        return self::$_sqlObject;
    }

}

?>