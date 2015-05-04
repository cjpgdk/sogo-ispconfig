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
class sogo_helper {

    /** @var mysqli */
    static private $_sqlObject = NULL;

    /** @var array */
    static private $daCache = array();

    /** @var array */
    static private $dnCache = array();

    /** @var array */
    static private $sCache = array();

    /** @var sogo_module_settings */
    public $module_settings;
    static private $sogo_server = array();

    /**
     * query all mail domains, based on module settings
     * @global app $app
     * @global array $conf
     * @param string $active
     * @return boolean|array boolean false on failure or empty query
     */
    public function getMailDomainNames($active = 'y') {
        global $app, $conf;
        if (!in_array($active, array('n', 'y')))
            $active = 'y';
        $mail_domains_sql = FALSE;
        if ($this->module_settings->all_domains && $this->module_settings->allow_same_instance) {
            //* allow all domains + same instance
            $mail_domains_sql = "SELECT `domain` FROM `mail_domain` WHERE `active`='y'";
        } else if (!$this->module_settings->all_domains && $this->module_settings->allow_same_instance) {
            //* allow only domains with sogo domain config + same instance
            $mail_domains_sql = "SELECT md.`domain` FROM `mail_domain` md, `sogo_domains` sd WHERE md.`active`='y' AND md.`domain_id`=sd.`domain_id` AND md.`server_id`=sd.`server_id`";
        } else if ($this->module_settings->all_domains && !$this->module_settings->allow_same_instance) {
            //* allow all domains but only for this server
            $mail_domains_sql = "SELECT `domain` FROM `mail_domain` WHERE `active`='y' AND `server_id`=" . intval($conf['server_id']);
        } else if (!$this->module_settings->all_domains && !$this->module_settings->allow_same_instance) {
            //* allow only domains with sogo domain config and located on this server
            $mail_domains_sql = "SELECT md.`domain` FROM `mail_domain` md, `sogo_domains` sd WHERE md.`active`='y' AND md.`domain_id`=sd.`domain_id` AND md.`server_id`=" . intval($conf['server_id']);
        }
        if ($mail_domains_sql !== FALSE) {
            return $this->getDB()->queryAllRecords($mail_domains_sql);
        } else
            return FALSE;
    }

    public function load_module_settings($server_id = 1) {
        global $app;
        //* $server_id are for the future
        $query = "SELECT * FROM `sogo_module` WHERE `server_id`=" . intval($server_id);
        $settings = $this->getDB(false)->queryOneRecord($query);

        $this->module_settings = new sogo_module_settings();
        if ($settings !== FALSE) {
            foreach ($settings as $key => $value) {
                if (in_array($key, array('smid', 'sys_userid', 'sys_groupid', 'sys_perm_user', 'sys_perm_group', 'sys_perm_other')))
                    continue;
                if ($key == "server_id")
                    $this->module_settings->{$key} = (int) $value;
                else
                    $this->module_settings->{$key} = ($value == 'y' ? TRUE : FALSE);
            }
        } else {
            $this->module_settings->server_id = intval($server_id);
            $app->log("Unable to fetch SOGo module settings using defaults", LOGLEVEL_WARN);
        }
    }

    /**
     * check the number of alias columns for a domain name, and create more if to low
     * @param string $domain
     * @return boolean
     */
    public function check_alias_columns($domain) {
        //* get total alias count for domain
        $acount_n = (int) $this->get_max_alias_count($domain, 'n'); //* none active
        $acount_y = (int) $this->get_max_alias_count($domain, 'y'); //* active
        $acount = (int) ($acount_n + $acount_y);
        //* get alias columns in table for domain
        $dtacount = (int) $this->getSOGoTableAliasColumnCount($domain);
        $has_error = FALSE;
        //* if alias columns count in table for domain are to low
        if ($dtacount < $acount) {
            //* update domain table
            $sql = array();
            for ($index = 0; $index < intval(($acount - $dtacount)); $index++) {
                $_i = (int) ($dtacount + $index);
                $sql[] = "ALTER TABLE `{$this->getValidSOGoTableName($domain)}` ADD `alias_{$_i}` VARCHAR( 500 ) NOT NULL ";
            }
            $sqlres = & $this->sqlConnect();
            foreach ($sql as $value) {
                if (!$sqlres->query($value)) {
                    $this->logError("sogo_helper::check_alias_columns(): update domain table for [{$domain}], FAILD" . PHP_EOL . "SQL: {$value}" . PHP_EOL . "SQL Error: " . $sqlres->error . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 1));
                    $has_error = TRUE;
                }
            }
        }
        return ($has_error === FALSE);
    }

    /**
     * check if a domain has any email addresses
     * @global array $conf
     * @param string $domain_name
     * @param boolean $imap_enabled if set to false will count all email addresses, is set to true will only count email addresses with imap enabled
     * @return boolean
     */
    public function has_mail_users($domain_name, $imap_enabled = true) {
        $emails = $this->getDB()->queryOneRecord("SELECT count(*) as cnt FROM `mail_user` WHERE `email` LIKE '%@{$domain_name}'" . ($imap_enabled ? "AND `disableimap` = 'n'" : ""));
        if ($emails !== FALSE && ((int) $emails['cnt'] > 0)) {
            return true;
        }
        return false;
    }

    public function getServer($sid) {
        if (!isset(self::$sogo_server[$sid])) {
            global $app, $conf;
            if ($sid === NULL || !is_int($sid))
                $sid = $conf['server_id'];
            $sql = "SELECT * FROM `server` WHERE `server_id`=" . intval($sid);
            self::$sogo_server[$sid] = $this->getDB()->queryOneRecord($sql);
        }
        return self::$sogo_server[$sid];
    }

    /**
     * get config by server id
     * @global app $app
     * @global array $conf
     * @param integer $server_id
     * @return array|boolean
     */
    public function getServerConfig($server_id = NULL) {
        if (!isset(self::$sCache[$server_id])) {
            global $app, $conf;
            if ($server_id === NULL || !is_int($server_id))
                $server_id = $conf['server_id'];

            $sql = "SELECT * FROM `sogo_config` WHERE `server_id`=" . intval($server_id);
            $server_default = $this->getDB(false)->queryOneRecord($sql);

            if (!$server_default) {
                $this->logError("SOGo get server config failed.");
                self::$sCache[$server_id] = false;
                return self::$sCache[$server_id];
            }
            $this->removeUselessValues($server_default);
            self::$sCache[$server_id] = $server_default;
            return self::$sCache[$server_id];
        }
        return self::$sCache[$server_id];
    }

    public function is_domain_active($domain_name) {
        global $app;
        if (!isset(self::$dnCache['active' . $domain_name])) {
            $sql = "SELECT `active` FROM `mail_domain` WHERE `domain`='{$this->dbEscapeString($domain_name)}'";
            $res = $this->getDB()->queryOneRecord($sql);
            if ($res !== FALSE && isset($res['active']))
                self::$dnCache['active' . $domain_name] = (strtolower($res['active']) == 'y' ? TRUE : FALSE);
            else
                self::$dnCache['active' . $domain_name] = FALSE;
        }
        return (bool) self::$dnCache['active' . $domain_name];
    }

    /**
     * get domain config
     * @global app $app
     * @param string $domain_name
     * @param boolean $full_server_conf set to true gets the full config for a domain including server defaults
     * @return array|boolean boolean false on error
     */
    public function getDomainConfig($domain_name, $full_server_conf = false) {
        if (!isset(self::$dnCache[$domain_name]) && $this->is_domain_active($domain_name)) {
            global $app;
            //* get server default config (BASED on domain name)
            //$server_default_sql = "SELECT sc.* FROM `server` s, `mail_domain` md, `sogo_config` sc  WHERE s.`server_id`=md.`server_id` AND md.`domain`='{$domain_name}' AND sc.`server_id`=md.`server_id`  AND sc.`server_name`=s.`server_name`";
            //* better for multi server but not sure if multi SOGo server? (@todo propper testing)
            $server_default_sql = "SELECT sc.*, 
(SELECT `server_name` FROM `mail_domain` md, `server` s WHERE md.`domain`='{$domain_name}' AND md.`server_id`=s.`server_id`) as server_name_real 
FROM `server` s, `mail_domain` md, `sogo_config` sc 
WHERE md.`domain` = '{$domain_name}' 
AND sc.`server_name` = s.`server_name";
            $server_default = $this->getDB()->queryOneRecord($server_default_sql);

            if (!$server_default) {
                $app->log("sogo_helper::getDomainConfig(): failed. Unable to get server config from domain {$domain_name}", LOGLEVEL_WARN);
                $app->log("sogo_helper::getDomainConfig(): {$server_default_sql}", LOGLEVEL_WARN);
                self::$dnCache[$domain_name] = false; //* if server default is not isset we must stop it from running to prevent SOGo or system failures
                return self::$dnCache[$domain_name];
            }
            //* @todo if same instace is allowed force server url / ip on the mail server (NO localhosts)
            $parse_url = parse_url($server_default["SOGoSieveServer"]);
            $server_default["SOGoSieveServer"] = (isset($parse_url['host']) ? $parse_url['host'] : (isset($parse_url['path']) && $parse_url['path'] == $server_default["SOGoSieveServer"] ? $server_default["SOGoSieveServer"] : ""));
            $parse_url = parse_url($server_default["SOGoIMAPServer"]);
            $server_default["SOGoIMAPServer"] = (isset($parse_url['host']) ? $parse_url['host'] : (isset($parse_url['path']) && $parse_url['path'] == $server_default["SOGoIMAPServer"] ? $server_default["SOGoIMAPServer"] : ""));
            $parse_url = parse_url($server_default["SOGoSMTPServer"]);
            $server_default["SOGoSMTPServer"] = (isset($parse_url['host']) ? $parse_url['host'] : (isset($parse_url['path']) && $parse_url['path'] == $server_default["SOGoSMTPServer"] ? $server_default["SOGoSMTPServer"] : ""));
            unset($parse_url);
            //* get configuration fields for domains
            $domains_columns = $this->getDB()->queryAllRecords("SHOW COLUMNS FROM `sogo_domains`");

            //* get domain configuration
            $domain_default_sql = "SELECT * FROM `sogo_domains` WHERE `domain_name`='{$domain_name}'";
            $domain_default = $this->getDB()->queryOneRecord($domain_default_sql);
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
                $ret_srv['server_name_real'] = $server_default['server_name_real'];
                $this->removeUselessValues($ret_srv, array('server_name', 'server_name_real'));
                self::$dnCache[$domain_name] = $ret_srv;
                return self::$dnCache[$domain_name];
            }

            $parse_url = parse_url($domain_default["SOGoSieveServer"]);
            $domain_default["SOGoSieveServer"] = (isset($parse_url['host']) ? $parse_url['host'] : (isset($parse_url['path']) && $parse_url['path'] == $domain_default["SOGoSieveServer"] ? $domain_default["SOGoSieveServer"] : ""));
            $parse_url = parse_url($domain_default["SOGoIMAPServer"]);
            $domain_default["SOGoIMAPServer"] = (isset($parse_url['host']) ? $parse_url['host'] : (isset($parse_url['path']) && $parse_url['path'] == $domain_default["SOGoIMAPServer"] ? $domain_default["SOGoIMAPServer"] : ""));
            $parse_url = parse_url($domain_default["SOGoSMTPServer"]);
            $domain_default["SOGoSMTPServer"] = (isset($parse_url['host']) ? $parse_url['host'] : (isset($parse_url['path']) && $parse_url['path'] == $domain_default["SOGoSMTPServer"] ? $domain_default["SOGoSMTPServer"] : ""));
            unset($parse_url);
            //* get configuration fields for domains
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
            //* for SOGo on difrent host than mail server of domain, (server_name_real == mail server of domain)
            $domain_default["server_name_real"] = $server_default['server_name_real'];
            $this->removeUselessValues($domain_default, array('server_name', 'server_name_real'));
            self::$dnCache[$domain_name] = $domain_default;
            return self::$dnCache[$domain_name];
        } else {
            if (!isset(self::$dnCache[$domain_name]))
                self::$dnCache[$domain_name] = $this->is_domain_active($domain_name);
        }
        return self::$dnCache[$domain_name];
    }

    /**
     * get alias columns count in sogo table by domain name
     * @global array $conf
     * @param string $domain_name
     * @return int
     */
    public function getSOGoTableAliasColumnCount($domain_name) {
        global $conf;
        $sqlres = & $this->sqlConnect();
        $sql = "SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_NAME`='{$sqlres->escape_string($this->getValidSOGoTableName($domain_name))}' AND `TABLE_SCHEMA`='{$conf['sogo_database_name']}' AND `COLUMN_NAME` LIKE 'alias_%'";
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
     * @param string $domain_name
     * @param integer $domain_id
     */
    public function dropSOGoUsersTable($domain_name, $domain_id) {
        $this->logDebug("sogo_helper::dropSOGoUsersTable(): {$domain_id}#{$domain_name}");
        $sogo_db = & $this->sqlConnect();
        $sogo_db->query("DROP TABLE {$this->getValidSOGoTableName($domain_name)}");
        if ($sogo_db->error) {
            $this->logWarn("sogo_plugin::sogo_domain_delete(): SQL ERROR:\n{$sogo_db->error}\n{$domain_id}#{$domain_name}");
        }
        unset(self::$sutCache[$domain_name]);
    }

    /**
     * check if the sogo user table exists in the database
     * @global array $conf
     * @param string $domain
     * @return boolean
     */
    public function sogoTableExists($domain) {
        if (!isset(self::$sutCache[$domain])) {
            global $conf;
            $domain = $this->getValidSOGoTableName($domain);
            $sqlres = & $this->sqlConnect();
            $sql1 = "SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$sqlres->escape_string($conf['sogo_database_name'])}' AND `TABLE_NAME`='{$sqlres->escape_string($domain)}'";
            $tmp = $sqlres->query($sql1);
            while ($obj = $tmp->fetch_object()) {
                if ($obj->TABLE_NAME == $domain) {
                    self::$sutCache[$domain] = true;
                    return self::$sutCache[$domain];
                }
            }
            $this->logDebug("SOGo table do not exists [{$domain}]");
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
    public function getValidSOGoTableName($domain_name) {
        global $conf;
        $domain_name = str_replace(array('-', '.'), '_', $domain_name);
        return str_replace('{domain}', $domain_name, $conf['sogo_domain_table_tpl']);
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
            $res = $this->getDB()->queryOneRecord($sql);
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
            $res = $this->getDB()->queryAllRecords($sql);
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
     * get ISPConfig database connection
     * @global app $app
     * @param boolean $dbmaster
     * @return NULL|db
     */
    public function getDB($dbmaster = true) {
        global $app;
        if ($dbmaster && property_exists($app, 'dbmaster')) {
            return $app->dbmaster;
        } else if (property_exists($app, 'db')) {
            return $app->db;
        } else
            $app->log('sogo_helper::getDB() : No database connection object found', LOGLEVEL_WARN);
        return NULL;
    }

    /**
     * get template object (tpl)
     * @global app $app
     * @param string $file
     * @return \tpl
     */
    public function getTemplateObject($file = "sogo_domain.master") {
        global $app;
        if (!property_exists($app, 'tpl') || !is_object($app->tpl))
            $app->uses('tpl');
        $tpl = NULL;
        if (file_exists(ISPC_ROOT_PATH . "/conf-custom/{$file}"))
            $tpl = new tpl(ISPC_ROOT_PATH . "/conf-custom/{$file}");
        else if (file_exists(ISPC_ROOT_PATH . "/conf/{$file}"))
            $tpl = new tpl(ISPC_ROOT_PATH . "/conf/{$file}");
        return $tpl;
    }

    /**
     * unset all possible wars in an array that is of no use to SOGo
     */
    private function removeUselessValues(& $param, $keep = array()) {
        $useless_values = array(
            'sogo_id', 'sys_groupid', 'sys_perm_group', 'domain_id', 'sys_userid',
            'sys_perm_user', 'sys_perm_other', 'server_id', 'server_name'
        );
        if (is_array($param))
            foreach ($param as $key => & $value)
                if (in_array($key, $useless_values) && !in_array($key, $keep))
                    unset($param[$key]);
    }

    /**
     * Escape string for mysql query use
     * @param mixed $str
     * @return mixed
     */
    public function dbEscapeString($str) {
        $db = $this->getDB();
        if ($db === null || $db === FALSE)
            return $str;
        if (method_exists($db, 'escape_string')) {
            return $db->escape_string($str);
        } else if (method_exists($db, 'quote')) {
            return $db->quote($str);
        }
        return $str;
    }

    /*     * *******************
     * are to be removed
     * never meant as a permanent thing
     * ******************* */

    /**
     * log errors
     * @global app $app
     * @param string $str
     * @deprecated use $app->log
     */
    public function logError($str) {
        global $app;
        $app->log($str, LOGLEVEL_ERROR);
    }

    /**
     * log warnings
     * @global app $app
     * @param string $str
     * @deprecated use $app->log
     */
    public function logWarn($str) {
        global $app;
        $app->log($str, LOGLEVEL_WARN);
    }

    /**
     * log debug
     * @global app $app
     * @param string $str
     * @deprecated use $app->log
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
     * @deprecated use if ($arg_0 == $arg_1)
     */
    public function isEqual($arg_0, $arg_1) {
        if ($arg_0 == $arg_1) {
            return TRUE;
        }
        return FALSE;
    }

}

class sogo_module_settings {

    /**
     * this server id
     * @var integer
     */
    public $server_id = -1;

    /**
     * configure domains with and without config
     * @var boolean
     */
    public $all_domains = TRUE;

    /**
     * configure all domains for use same place
     * @var boolean
     */
    public $allow_same_instance = TRUE;

    /**
     * always rebuild SOGo configuration when a mail user is inserted
     * @var boolean
     */
    public $config_rebuild_on_mail_user_insert = TRUE;

}
