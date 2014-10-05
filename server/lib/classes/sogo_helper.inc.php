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

    /**
     * check if the sogo user table exists in the database
     * @global array $conf
     * @param string $domain
     * @return boolean
     */
    public function sogo_table_exists($domain) {
        global $conf;
        $domain = $this->get_valid_sogo_table_name($domain);
        $sqlres = & $this->sqlConnect();
        $sql1 = "SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$sqlres->escape_string($conf['sogo_database_name'])}' AND `TABLE_NAME`='{$sqlres->escape_string($domain)}_users'";
        $tmp = $sqlres->query($sql1);
        while ($obj = $tmp->fetch_object()) {
            if ($obj->TABLE_NAME == $domain . '_users') {
                $this->logDebug("SOGo table exists [{$domain}_users]");
                return true;
            }
        }
        $this->logDebug("SOGo table do not exists [{$domain}_users]");
        return false;
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
        if (!in_array($active, array('n', 'y'))) $active = 'y';
        $sql = "SELECT DISTINCT (SELECT COUNT(*) FROM `mail_forwarding` WHERE `destination`=mf.`destination` AND `type`='alias' AND `active`='{$active}') AS alias_cnt FROM `mail_forwarding` mf WHERE `destination`='{$destination}' AND `type`='alias' AND `active`='{$active}'";
        $res = $app->db->queryOneRecord($sql);
        return (int) (isset($res['alias_cnt']) ? $res['alias_cnt'] : 0);
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
        if (!in_array($active, array('n', 'y'))) $active = 'y';
        $sql = "SELECT DISTINCT `destination`,  (SELECT COUNT(*) FROM `mail_forwarding` WHERE `destination`=mf.`destination` AND `type`='alias' AND `active`='{$active}') AS alias_cnt FROM `mail_forwarding` mf WHERE `destination` LIKE '%@{$domain_name}' AND `type`='alias' AND `active`='{$active}'";
        return $app->db->queryAllRecords($sql);
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
