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

/**
 * SOGo helper for ISPConfig server plugin (sogo_plugin.inc.php)
 */
class sogo_helper {

    /** @var mysqli */
    static private $_sqlObject = NULL;

    /** @var array */
    static private $daCache = array();

    /** @var array */
    static private $dnCache = array();

    /** @var array */
    static private $sCache = array();

    /**
     * get config by server id
     * @global app $app
     * @global array $conf
     * @param integer $server_id
     * @return array|boolean
     */
    public function get_server_config($server_id = NULL) {
        if (!isset(self::$sCache[$server_id])) {
            global $app, $conf;
            if ($server_id === NULL || !is_int($server_id))
                $server_id = $conf['server_id'];

            $sql = "SELECT * FROM `sogo_config` WHERE `server_id`=" . intval($server_id);

            $server_default = $app->db->queryOneRecord($sql);
            if (!$server_default) {
                $app->sogo_helper->logError("SOGo get server config failed." . PHP_EOL . "Unable to get server config for server id {$server_id}" . PHP_EOL . "SQL: {$sql}" . PHP_EOL . "SQL Error: {$app->db->error}" . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 2));
                self::$sCache[$server_id] = false;
                return self::$sCache[$server_id];
            }
            //* vaules we don't need in sogo config.
            unset($server_default['sogo_id'], $server_default['server_id'], $server_default['server_name'], $server_default['sys_userid'], $server_default['sys_groupid'], $server_default['sys_perm_user'], $server_default['sys_perm_group'], $server_default['sys_perm_other']);
            self::$sCache[$server_id] = $server_default;
            return self::$sCache[$server_id];
        }
        return self::$sCache[$server_id];
    }

    /**
     * get domain config
     * @global app $app
     * @param string $domain_name
     * @param boolean $full_server_conf set to true gets the full config for a domain including server defaults
     * @return array|boolean boolean false on error
     */
    public function get_domain_config($domain_name, $full_server_conf = false) {
        if (!isset(self::$dnCache[$domain_name])) {
            global $app;
            //* get server default config (BASED on domain name)
            $server_default_sql = "SELECT sc.* FROM `server` s, `mail_domain` md, `sogo_config` sc  WHERE s.`server_id`=md.`server_id` AND md.`domain`='{$domain_name}' AND sc.`server_id`=md.`server_id`  AND sc.`server_name`=s.`server_name`";
            $server_default = $app->db->queryOneRecord($server_default_sql);
            if (!$server_default) {
                $this->logError("sogo_helper::get_domain_config(): failed." . PHP_EOL . "Unable to get server config from domain {$domain_name}" . PHP_EOL . "SQL: {$server_default_sql}" . PHP_EOL . "SQL Error: {$app->db->error}" . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 2));
                self::$dnCache[$domain_name] = false; //* if server default is not isset we must stop it from running to prevent SOGo or system failures
                return self::$dnCache[$domain_name];
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
                    self::$dnCache[$domain_name] = array();
                    return self::$dnCache[$domain_name];
                }
                $ret_srv = array();
                foreach ($domains_columns as $value) {
                    if (isset($server_default["{$value['Field']}"])) {
                        $ret_srv["{$value['Field']}"] = $server_default["{$value['Field']}"];
                    }
                }
                //* return server config if domain config do not exists!
                unset($ret_srv['sogo_id'], $ret_srv['sys_userid'], $ret_srv['sys_groupid'], $ret_srv['sys_perm_user'], $ret_srv['sys_perm_group'], $ret_srv['sys_perm_other']);
                self::$dnCache[$domain_name] = $ret_srv;
                return self::$dnCache[$domain_name];
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
            self::$dnCache[$domain_name] = $domain_default;
            return self::$dnCache[$domain_name];
        }
        return self::$dnCache[$domain_name];
    }

    /**
     * get alias columns count in sogo table by domain name
     * @global array $conf
     * @param string $domain_name
     * @return int
     */
    public function get_sogo_table_alias_column_count($domain_name) {
        global $conf;
        $sqlres = & $this->sqlConnect();
        $sql = "SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_NAME`='{$sqlres->escape_string($this->get_valid_sogo_table_name($domain_name))}_users' AND `TABLE_SCHEMA`='{$conf['sogo_database_name']}' AND `COLUMN_NAME` LIKE 'alias_%'";
        $tmp = $sqlres->query($sql);
        $c = 0;
        while ($obj = $tmp->fetch_object())
            $c++;

        return $c;
    }

    /** @var array */
    static private $sutCache = array();

    /**
     * delete a SOGo mail domain table from SOGo db
     * @global app $app
     * @param string $domain_name
     * @param integer $domain_id
     */
    public function drop_sogo_users_table($domain_name, $domain_id) {
        global $app;
        $sogo_db = & $app->sogo_helper->sqlConnect();
        $sogo_db->query("DROP TABLE {$app->sogo_helper->get_valid_sogo_table_name($domain_name)}_users");
        if ($sogo_db->error) {
            $app->sogo_helper->logWarn("sogo_plugin::sogo_domain_delete(): SQL ERROR:\n{$sogo_db->error}\n{$domain_id}#{$domain_name}");
        }
        unset(self::$sutCache[$domain_name]);
    }

    /**
     * check if the sogo user table exists in the database
     * @global array $conf
     * @param string $domain
     * @return boolean
     */
    public function sogo_table_exists($domain) {
        if (!isset(self::$sutCache[$domain])) {
            global $conf;
            $domain = $this->get_valid_sogo_table_name($domain);
            $sqlres = & $this->sqlConnect();
            $sql1 = "SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$sqlres->escape_string($conf['sogo_database_name'])}' AND `TABLE_NAME`='{$sqlres->escape_string($domain)}_users'";
            $tmp = $sqlres->query($sql1);
            while ($obj = $tmp->fetch_object()) {
                if ($obj->TABLE_NAME == $domain . '_users') {
                    self::$sutCache[$domain] = true;
                    return self::$sutCache[$domain];
                }
            }
            $this->logDebug("SOGo table do not exists [{$domain}_users]");
            self::$sutCache[$domain] = false;
            return self::$sutCache[$domain];
        }
        return self::$sutCache[$domain];
    }

    /**
     * SOGo wont lookup a table with - or . in them so replace with _
     * @param string $domain_name
     * @return string 
     */
    public function get_valid_sogo_table_name($domain_name) {
        return str_replace(array('-', '.'), '_', $domain_name);
    }

    /**
     * get the highest alias mail count for a mail domain
     * @param string $domain_name
     * @param string $active y|n
     * @return integer
     */
    public function get_max_alias_count($domain_name, $active = 'y') {
        if (!isset(self::$daCache[md5($domain_name . $active)])) {
            $a_cnt = 0;
            $aliases = $this->get_alias_counters($domain_name, $active);
            foreach ($aliases as $value) {
                //* get highest mail alias counter 
                $a_cnt = (int) ((int) $a_cnt < (int) $value['alias_cnt'] ? $value['alias_cnt'] : $a_cnt);
            }
            self::$daCache[md5($domain_name . $active)] = (int) $a_cnt;
        }
        return (int) self::$daCache[md5($domain_name . $active)];
    }

    /**
     * get alias mail count destination email
     * @global app $app
     * @param string $destination
     * @param string $active
     * @return inteter
     */
    public function get_mail_alias_counters($destination, $active = 'y') {
        global $app;
        $destination = trim($destination);
        if (!in_array($active, array('n', 'y')))
            $active = 'y';
        $sql = "SELECT DISTINCT (SELECT COUNT(*) FROM `mail_forwarding` WHERE `destination`=mf.`destination` AND `type`='alias' AND `active`='{$active}') AS alias_cnt FROM `mail_forwarding` mf WHERE `destination`='{$destination}' AND `type`='alias' AND `active`='{$active}'";
        if (!isset(self::$daCache[md5($sql)])) {
            $res = $app->db->queryOneRecord($sql);
            self::$daCache[md5($sql)] = (int) (isset($res['alias_cnt']) ? $res['alias_cnt'] : 0);
        }
        return (int) self::$daCache[md5($sql)];
    }

    /**
     * get alias mail count and destinations by domainn name
     * @global app $app
     * @param type $domain_name
     * @param string $active
     * @return inteter|boolean
     */
    public function get_alias_counters($domain_name, $active = 'y') {
        global $app;
        $domain_name = trim($domain_name);
        if (!in_array($active, array('n', 'y')))
            $active = 'y';
        $sql = "SELECT DISTINCT `destination`,  (SELECT COUNT(*) FROM `mail_forwarding` WHERE `destination`=mf.`destination` AND `type`='alias' AND `active`='{$active}') AS alias_cnt FROM `mail_forwarding` mf WHERE `destination` LIKE '%@{$domain_name}' AND `type`='alias' AND `active`='{$active}'";
        if (!isset(self::$daCache[md5($sql)])) {
            $res = $app->db->queryAllRecords($sql);
            self::$daCache[md5($sql)] = $res;
        }
        return self::$daCache[md5($sql)];
    }

    /**
     * Get a mysqli connection object to the mysql database server
     * @global array $conf
     * @return mysqli
     */
    public function & sqlConnect() {
        global $conf;

        if (self::$_sqlObject == NULL) {
            self::$_sqlObject = new mysqli($conf['sogo_database_host'], $conf['sogo_database_user'], $conf['sogo_database_passwd'], $conf['sogo_database_name'], $conf['sogo_database_port']);
            if (mysqli_connect_errno()) {
                $this->logError(sprintf("SOGo DB, Connect failed: %s\n", mysqli_connect_error()));
                return;
            }
        }
        //* check if the connetion is still good.!
        if (self::$_sqlObject->ping()) {
            return self::$_sqlObject;
        } else {
            //* not good create a new one.
            self::$_sqlObject = new mysqli($conf['sogo_database_host'], $conf['sogo_database_user'], $conf['sogo_database_passwd'], $conf['sogo_database_name'], $conf['sogo_database_port']);
            if (mysqli_connect_errno()) {
                $this->logError(sprintf("SOGo DB, Connect failed: %s\n", mysqli_connect_error()));
                return;
            }
        }
        return self::$_sqlObject;
    }

    /**
     * log errors
     * @global app $app
     * @param string $str
     */
    public function logError($str) {
        global $app;
        $app->log($str, LOGLEVEL_ERROR);
    }

    /**
     * log warnings
     * @global app $app
     * @param string $str
     */
    public function logWarn($str) {
        global $app;
        $app->log($str, LOGLEVEL_WARN);
    }

    /**
     * log debug
     * @global app $app
     * @param string $str
     */
    public function logDebug($str) {
        global $app;
        $app->log($str, LOGLEVEL_DEBUG);
    }

    /**
     * compere to strings
     * @param string $arg_0
     * @param string $arg_1
     * @return boolean
     */
    public function isEqual($arg_0, $arg_1) {
        if ($arg_0 == $arg_1) {
            return TRUE;
        }
        return FALSE;
    }

}
