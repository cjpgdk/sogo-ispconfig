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
    var $sogopw = '7JB6g9ue2e4tiutRDF8fWRe1k';
    var $sogouser = 'sogodbuser';
    var $sogodb = 'sogodb';
    var $ispcdb = 'dbispconfig';
    var $sogobinary = '/usr/sbin/sogod';
    var $sogotoolbinary = '/usr/sbin/sogo-tool';
    var $sogohomedir = '/var/lib/sogo';
    var $sogoconffile = '/var/lib/sogo/GNUstep/Defaults/.GNUstepDefaults';
    var $sogoinitscript = '/etc/init.d/sogo';
    var $templ_file = '/usr/local/ispconfig/server/conf/sogo.conf';
    var $templ_domains_dir = '/usr/local/ispconfig/server/conf/sogo_domains';
    var $templ_override_file = '/usr/local/ispconfig/server/conf-custom/sogo/sogo.conf';
    var $templ_override_domains_dir = '/usr/local/ispconfig/server/conf-custom/sogo/domains';
    var $mysql_server_host = '127.0.0.1:3306';

    function onInstall() {
        global $conf;
        if ($conf['services']['mail'] == true) {
            if (!empty($this->ispcdb) &&
                    !empty($this->sogo_su_cmd) &&
                    !empty($this->sogopw) &&
                    !empty($this->sogouser) &&
                    !empty($this->sogodb) &&
                    !empty($this->sogobinary) &&
                    !empty($this->sogotoolbinary) &&
                    !empty($this->sogohomedir) &&
                    !empty($this->sogoconffile) &&
                    !empty($this->sogoinitscript) &&
                    !empty($this->templ_file) &&
                    !empty($this->templ_domains_dir) &&
                    !empty($this->mysql_server_host)) {
                if (!file_exists($this->sogobinary)) {
                    return false;
                }
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
    }

    /**
     * method to remove a sogo user from sogo storage
     * @global app $app
     * @global array $conf
     * @param string $event_name
     * @param array $data array of old and new data
     */
    function remove_sogo_mail_user($event_name, $data) {
        global $app, $conf;
        if ($event_name == 'mail_user_delete') {
            exec($this->sogo_su_cmd . ' ' . $this->sogotoolbinary . ' remove ' . escapeshellarg($data['old']['login']));
        }
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
        $flag = false;
        if ($event_name == 'mail_domain_delete') {
            $flag = $this->remove_sogo_maildomain((isset($data['new']['domain']) ? $data['new']['domain'] : $data['old']['domain']));
        } else if ($event_name == 'mail_domain_insert') {
            $flag = true;
        } else if ($event_name == 'mail_domain_update') {
            $flag = true;
        } else {
            //* we log this as debug since thats not this scripts error
            $app->log("Wrong event_name sendt to sogo_config_plugin::reconfigure(): got [{$event_name}] but only accepts mail_domain_delete|mail_domain_insert|mail_domain_update", LOGLEVEL_DEBUG);
        }
        if ($flag) {
            $active_mail_domains = $app->db->queryAllRecords('SELECT `domain`,`server_id` FROM `mail_domain` WHERE `active`=\'y\'');
            $sogo_conf = "";
            if (file_exists($this->templ_override_file)) {
                $sogo_conf = file_get_contents($this->templ_override_file);
            } else if (file_exists($this->templ_file)) {
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
            if (!file_put_contents($this->sogoconffile, $sogo_conf)) {
                $app->log('ERROR. unable to reconfigure SOGo..', LOGLEVEL_ERROR);
                return;
            } else {
                exec($this->sogoinitscript . ' restart');
                //* make the system wait..
                sleep(1);
            }
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
        $sqlres->query('DROP VIEW `sogo_users_' . $dom_no_point . '`');
        //* remove config files for this domain.
        if (file_exists("{$this->templ_domains_dir}/{$domain_name}.conf")) {
            @unlink("{$this->templ_domains_dir}/{$domain_name}.conf");
        }
        if (file_exists("{$this->templ_override_domains_dir}/{$domain_name}.conf")) {
            @unlink("{$this->templ_override_domains_dir}/{$domain_name}.conf");
        }
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
     * @param string $domain
     * @return boolean
     */
    function _sogo_view_exists($domain) {
        $domain = $this->_get_valid_sogo_table_name($domain);
        $sql1 = "SELECT `TABLE_NAME` FROM `information_schema`.`VIEWS` WHERE `TABLE_SCHEMA`='{$this->sogodb}' AND `TABLE_NAME`='sogo_users_{$domain}'";
        $sqlres = & $this->_sqlConnect();
        $tmp = $sqlres->query($sql1);
        while ($obj = $tmp->fetch_object()) {
            if ($obj->TABLE_NAME == 'sogo_users_' . $domain) {
                return true;
            }
        }
    }

    /**
     * create a new sogo view if not exists
     * @global app $app
     * @global array $conf
     * @param string $domain_name
     * @return boolean
     */
    function create_sogo_view($domain_name) {
        global $app, $conf;
        $sqlres = & $this->_sqlConnect();
        $dom_no_point = $this->_get_valid_sogo_table_name($domain_name);
        if ($this->_sogo_view_exists($dom_no_point)) {
            return true;
        }
        $sqlres->query('
CREATE VIEW sogo_users_' . $dom_no_point . ' AS SELECT
    `login` AS c_uid,
    `login` AS c_name,
    `password` AS c_password,
    `name` AS c_cn,
    `email` AS mail,
    (SELECT `server_name` FROM ' . $this->ispcdb . '.`server`, ' . $this->ispcdb . '.`mail_user` WHERE `mail_user`.`server_id`=`server`.`server_id` AND `server`.`mail_server`=1 AND ispcmu.`login`=`mail_user`.`login` LIMIT 1) AS imap_host 
FROM ' . $this->ispcdb . '.`mail_user` AS ispcmu  WHERE `email` LIKE \'%@' . $dom_no_point . '\' AND disableimap=\'n\'');
        if (!empty($sqlres->error) || !empty($sqlres->error_list))
            $app->log('ERROR. unable to create SOGo view[sogo_users_' . $dom_no_point . '].. ' . $sqlres->error . (!empty($sqlres->error_list) && is_array($sqlres->error_list) ? ' :: ' . implode(PHP_EOL, $sqlres->error_list) : ''), LOGLEVEL_ERROR);
        return true;
    }

    /**
     * build the config for SOGo domain configuration.!
     * @global app $app
     * @global array $conf
     * @param string $domain_name
     * @param int $sid
     * @return string
     */
    function build_conf_sogo_maildomain($domain_name, $sid = 1) {
        global $app, $conf;
        $dom_no_point = $this->_get_valid_sogo_table_name($domain_name);
        /* For mail aliases..
          <key>MailFieldNames</key>
          <array>
          <string>Col1</string>
          <string>Col2</string>
          <string>Col3</string>
          </array>
         */
        $server_name_result = $app->db->queryOneRecord("SELECT `server_name` FROM `server` WHERE `server_id`=" . intval($sid));
        $sogo_conf = $this->_get_config_domain_contents($domain_name, (isset($server_name_result['server_name']) ? $server_name_result['server_name'] : ''));
        $sogo_conf_vars = array(
            '{{DOMAIN}}' => $domain_name,
            '{{DOMAINADMIN}}' => 'postmaster@' . $domain_name,
            '{{SOGOUNIQID}}' => $dom_no_point,
            '{{CONNECTIONVIEWURL}}' => "mysql://{$this->sogouser}:{$this->sogopw}@{$this->mysql_server_host}/{$this->sogodb}/sogo_users_{$dom_no_point}"
        );
        if (!empty($sogo_conf)) {
            foreach ($sogo_conf_vars as $key => $value) {
                $sogo_conf = preg_replace("/{$key}/i", $value, $sogo_conf);
            }
        } else {
            $app->log('unable to loacate a configuration file for domains..!', LOGLEVEL_ERROR);
        }
        return $sogo_conf;
    }

    /**
     * get the domain configuration file template
     * @param string $domain_name
     * @param string $server_name
     * @return string
     */
    function _get_config_domain_contents($domain_name, $server_name = "") {
        if (file_exists("{$this->templ_override_domains_dir}/{$domain_name}.conf")) {
            return file_get_contents("{$this->templ_override_domains_dir}/{$domain_name}.conf");
        } else if (file_exists("{$this->templ_domains_dir}/{$domain_name}.conf")) {
            return file_get_contents("{$this->templ_domains_dir}/{$domain_name}.conf");
        } else if ($server_name != '' && file_exists("{$this->templ_override_domains_dir}/{$server_name}.conf")) {
            return file_get_contents("{$this->templ_override_domains_dir}/{$server_name}.conf");
        } else if ($server_name != '' && file_exists("{$this->templ_domains_dir}/{$server_name}.conf")) {
            return file_get_contents("{$this->templ_domains_dir}/{$server_name}.conf");
        } else if (file_exists("{$this->templ_domains_dir}/domains_default.conf")) {
            return file_get_contents("{$this->templ_domains_dir}/domains_default.conf");
        }
        return '';
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
