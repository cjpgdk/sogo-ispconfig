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
    private $_sqlObject = NULL;

    /** @var array */
    static private $daCache = array();
    static private $dnCache = array();
    static private $sCache = array();
    static private $sogo_server = array();

    /** @var sogo_module_settings */
    public $module_settings;

    /**
     * used to avoid duplicate queries on multi data updates in one session
     * @var array 
     */
    static private $_queryHash = array();

    /**
     * define if we use the old way for domain tables.
     * @var boolean 
     */
    public $sogo_tb_compatibility = false;

    public function __construct() {
        global $conf;
        $this->sogo_tb_compatibility = false;
        if (isset($conf['sogo_tb_compatibility'])) {
            $this->sogo_tb_compatibility = (bool) $conf['sogo_tb_compatibility'];
        }
    }

    /**
     * sync all mail users and aliases for a given domain name
     * @global app $app
     * @global array $conf
     * @param string $domain_name
     * @param boolean $imap_enabled if set to false will sync all email addresses, is set to true will only sync email addresses with imap enabled
     * @return boolean
     */
    public function sync_mail_users($domain_name, $imap_enabled = true) {
        global $app, $conf;
        if (!$this->check_domain_state_drop($domain_name))
            return false;

        $new_table = false;
        //* create domain table if it do not exists
        if (!$this->sogo_table_exists($domain_name)) {
            $this->create_sogo_table($domain_name, false);
            $new_table = true; //* createing new table, must rebuild config..!
        }
        $emails = $this->getDB(true)->queryAllRecords("SELECT * FROM `mail_user` WHERE `email` LIKE '%@{$domain_name}'" . ($imap_enabled ? " AND `disableimap` = 'n'" : ""));
        $sqlres = & $this->sqlConnect();
        if ($sqlres->set_charset("{$conf['db_charset']}")) {
            $sqlres->query("SET NAMES {$conf['db_charset']}");
            $sqlres->query("SET character_set_results='{$conf['db_charset']}'");
        }
        if (!empty($emails)) {
            $domain_config = $this->get_domain_config($domain_name, true);
            if (!$domain_config || !is_array($domain_config)) {
                $app->log("SOGo Sync Mail Users - Unable to fetch the domain config for domain [{$domain_name}]", LOGLEVEL_ERROR);
                return false;
            }
            $_REPLACE_SERVERNAME = (isset($domain_config['server_name_real']) ? $domain_config['server_name_real'] : $domain_config['server_name']);
            $domain_config['SOGoSieveServer'] = str_replace('{SERVERNAME}', $_REPLACE_SERVERNAME, $domain_config['SOGoSieveServer']);
            $domain_config['SOGoIMAPServer'] = str_replace('{SERVERNAME}', $_REPLACE_SERVERNAME, $domain_config['SOGoIMAPServer']);
            $_tmpSQL = array('users' => array(), 'alias' => array());
            $good_mails = array();

            $sogo_table_name = $this->get_valid_sogo_table_name($domain_name);

            //* @todo move this so the missing 'idn_mail' column is created when the domain is checked (sogo_helper::sogo_table_exists|create_sogo_table)
            $has_idn_column_sql = "SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_NAME`='{$sqlres->escape_string($sogo_table_name)}' AND `TABLE_SCHEMA`='{$conf['sogo_database_name']}' AND `COLUMN_NAME` = 'idn_mail'";
            $tmp = $sqlres->query($has_idn_column_sql);
            $has_idn_column = (bool) (count($tmp->fetch_assoc()) > 0);

            foreach ($emails as $email) {
                $good_mails[] = $email['login'];
                if ($this->sogo_mail_user_exists($email['login'], "{$sogo_table_name}")) {
                    $append_sql = "";
                    if ($has_idn_column)
                        $append_sql = "   `idn_mail` = '{$sqlres->escape_string($this->idn_decode($email['email']))}' ,   ";

                    $_tmpSQL['users'][] = "UPDATE `{$sogo_table_name}` SET "
                            . " `c_uid` = '{$sqlres->escape_string($email['login'])}' ,"
                            . " `c_cn` = '{$sqlres->escape_string($email['name'])}' ,"
                            . " `c_name` = '{$sqlres->escape_string($email['login'])}' ,"
                            . " `mail` = '{$sqlres->escape_string($email['email'])}' ,"
                            . $append_sql
                            . " `c_imaplogin` = '{$sqlres->escape_string($email['login'])}' ,"
                            . " `c_sievehostname` = '{$sqlres->escape_string($domain_config['SOGoSieveServer'])}' ,"
                            . " `c_imaphostname` = '{$sqlres->escape_string($domain_config['SOGoIMAPServer'])}' ,"
                            . " `c_domain` = '{$sqlres->escape_string($domain_name)}' ,"
                            . " `c_password` = '{$sqlres->escape_string($email['password'])}' "
                            . " WHERE `c_uid`='{$sqlres->escape_string($email['login'])}';";
                } else {

                    $append_sql = "";
                    $append_sql2 = "";
                    if ($has_idn_column) {
                        $append_sql = " `idn_mail`,";
                        $append_sql2 = "'{$sqlres->escape_string($this->idn_decode($email['email']))}', ";
                    }

                    $_tmpSQL['users'][] = "INSERT INTO `{$sogo_table_name}` "
                            . "(`c_uid`, `c_cn`, `c_name`, `mail`,{$append_sql} `c_imaplogin`, `c_sievehostname`, `c_imaphostname`, `c_domain`, `c_password`) "
                            . "VALUES "
                            . "("
                            . "'{$sqlres->escape_string($email['login'])}', "
                            . "'{$sqlres->escape_string($email['name'])}', "
                            . "'{$sqlres->escape_string($email['login'])}', "
                            . "'{$sqlres->escape_string($email['email'])}', "
                            . $append_sql2
                            . "'{$sqlres->escape_string($email['login'])}', "
                            . "'{$sqlres->escape_string($domain_config['SOGoSieveServer'])}', "
                            . "'{$sqlres->escape_string($domain_config['SOGoIMAPServer'])}', "
                            . "'{$sqlres->escape_string($domain_name)}', "
                            . "'{$sqlres->escape_string($email['password'])}'"
                            . ");";
                }
                $mail_aliases = $this->getDB()->queryAllRecords("SELECT `source` FROM `mail_forwarding` WHERE `destination` = '{$this->dbEscapeString($email['login'])}' AND `type`='alias' AND `active`='y';");
                //* get alias columns in table for domain
                $dtacount = (int) $this->get_sogo_table_alias_column_count($domain_name);

                $aliasSQL = "UPDATE `{$sogo_table_name}` SET ";
                //* only do alias update if a column exists for it 
                if ($dtacount > 0) {
                    $ac = 0;
                    foreach ($mail_aliases as $key => $value) {
                        $aliasSQL .= " `alias_{$ac}` = '{$sqlres->escape_string($this->idn_encode($value['source']))}' ,";
                        $ac++;
                        //* must be a better way but, need some results here so break on max alias columns in tb
                        if ($dtacount == $ac)
                            break;
                    }
                    $acount_n = (int) $this->get_max_alias_count($domain_name, 'n'); //* none active
                    $acount_y = (int) $this->get_max_alias_count($domain_name, 'y'); //* active
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
                            . " `c_uid` = '{$sqlres->escape_string($email['login'])}' AND"
                            . " `c_cn` = '{$sqlres->escape_string($email['name'])}' AND"
                            . " `c_name` = '{$sqlres->escape_string($email['login'])}' AND"
                            . " `mail` = '{$sqlres->escape_string($email['email'])}' AND"
                            . " `c_imaplogin` = '{$sqlres->escape_string($email['login'])}' AND"
                            . " `c_sievehostname` = '{$sqlres->escape_string($domain_config['SOGoSieveServer'])}' AND"
                            . " `c_imaphostname` = '{$sqlres->escape_string($domain_config['SOGoIMAPServer'])}' AND"
                            . " `c_domain` = '{$sqlres->escape_string($domain_name)}' AND"
                            . " `c_password` = '{$sqlres->escape_string($email['password'])}';";
                }
                /*
                 * server_id
                 * name
                 * disableimap
                 * disablesieve
                 * disablesieve-filter
                 */
            }
            foreach ($_tmpSQL['users'] as $value) {
                $_queryHash = md5($value); //* avoid multiple of the same query
                if (in_array($_queryHash, self::$_queryHash))
                    continue;
                if (!$sqlres->query($value)) {
                    $app->log("sogo_plugin::sync_mail_users(): sync users failed for domain [{$domain_name}]." . PHP_EOL . "SQL: {$value}" . PHP_EOL . "SQL Error: " . $sqlres->error . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 1), LOGLEVEL_ERROR);
                }
                self::$_queryHash[] = $_queryHash;
            }
            foreach ($_tmpSQL['alias'] as $value) {
                $_queryHash = md5($value); //* avoid multiple of the same query
                if (in_array($_queryHash, self::$_queryHash))
                    continue;
                if (!$sqlres->query($value)) {
                    $app->log("sogo_plugin::sync_mail_users(): sync users aliases failed for domain [{$domain_name}]." . PHP_EOL . "SQL: {$value}" . PHP_EOL . "SQL Error: " . $sqlres->error . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 1), LOGLEVEL_ERROR);
                }
                self::$_queryHash[] = $_queryHash;
            }

            //* for SOGo on other server than mail server, make sure delete users gets removed
            $sql = "SELECT c_uid FROM `{$sogo_table_name}` WHERE NOT `c_uid` IN ('" . implode("','", $good_mails) . "')";
            if ($tmp = $sqlres->query($sql)) {
                while ($obj = $tmp->fetch_object())
                    if (isset($obj->c_uid) && !in_array($obj->c_uid, $good_mails))
                        $this->delete_mail_user($obj->c_uid);
            }
            $app->log("Sync Mail Users in {$domain_name}", LOGLEVEL_DEBUG);

            if ($new_table) {
                $app->log("Mail domain '{$domain_name}', is newly created rebuilding SOGo config", LOGLEVEL_DEBUG);
                $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
            }
        } else {
            //* no mail users drop sogo table
            if ($this->sogo_table_exists($domain_name)) {
                //* check if users exists in table, delete them with SOGo if they do
                $sqlres = & $this->sqlConnect();
                if ($tmp = $sqlres->query("SELECT `c_uid` FROM `{$sqlres->escape_string($sogo_table_name)}`;")) {
                    while ($obj = $tmp->fetch_object()) {
                        if (isset($obj->c_uid)) {
                            //* only deletes from SOGo db
                            $this->delete_mail_user($obj->c_uid);
                        }
                    }
                }
                $this->drop_sogo_users_table($domain_name, -1);
            }
            $app->log("No users, dropping domain {$domain_name}", LOGLEVEL_DEBUG);
            $app->services->restartServiceDelayed('sogoConfigRebuild', 'bob the "SOGo Config" builder');
        }
        return TRUE;
    }

    public function sogo_mail_user_exists($email, $table) {
        $sqlres = & $this->sqlConnect();
        $usr = $sqlres->query("SELECT `c_imaplogin` FROM {$table} WHERE `c_imaplogin`='{$sqlres->escape_string($email)}' OR `c_uid`='{$sqlres->escape_string($email)}'");
        return ($usr !== FALSE && count($usr->fetch_assoc()) > 0 ? TRUE : FALSE);
    }

    /**
     * method to remove a sogo user from sogo storage
     * @global app $app
     * @global array $conf
     * @param string $email the email address to remove
     */
    public function delete_mail_user($email) {
        global $app, $conf;
        if (!empty($email) && (strpos($email, '@') !== FALSE)) {
            $cmd_arg = escapeshellarg("{$conf['sogo_tool_binary']}") . " remove " . escapeshellarg("{$email}");
            $cmd = str_replace('{command}', $cmd_arg, $conf['sogo_su_command']);
            $app->log("sogo_plugin::remove_sogo_mail_user() \n\t - CALL:{$cmd}", LOGLEVEL_DEBUG);
            exec($cmd);
            $usrDom = explode('@', $email);
            $sqlres = & $this->sqlConnect();
            $sqlres->query("DELETE FROM `{$this->get_valid_sogo_table_name($usrDom[1])}` WHERE `c_uid` = '{$sqlres->escape_string($email)}'");
            if ($sqlres->error)
                $app->log("sogo_plugin::remove_sogo_mail_user() \n\t - SQL Error: {$sqlres->error}", LOGLEVEL_DEBUG);
        }
    }

    public function check_domain_state_drop($domain, $domain_id = -1) {
        if (!$this->is_domain_active($domain)) {
            //* not active
            if ($this->sogo_table_exists($domain)) {
                //* check if users exists in table, delete them with SOGo if they do
                $domain_table = $this->get_valid_sogo_table_name($domain);
                $sqlres = & $this->sqlConnect();
                if ($tmp = $sqlres->query("SELECT `c_uid` FROM `{$sqlres->escape_string($domain_table)}`;")) {
                    while ($obj = $tmp->fetch_object()) {
                        if (isset($obj->c_uid)) {
                            //* only deletes from SOGo db
                            $this->delete_mail_user($obj->c_uid);
                        }
                    }
                }
                $this->drop_sogo_users_table($domain, $domain_id);
            }
            return false;
        }
        return true;
    }

    /**
     * create mail domain table and sync mail user for use with SOGo
     * @global app $app
     * @global array $conf
     * @param string $domain_name
     * @return boolean
     */
    public function create_sogo_table($domain_name, $call_sync = true) {
        global $app, $conf;
        if (!$this->has_mail_users($domain_name, true)) {
            //* dont create no users
            $app->log("sogo_helper::create_sogo_table(): Refusing to create table for domain: {$domain_name}, NO USERS", LOGLEVEL_DEBUG);
            return false;
        }
        if ($this->sogo_table_exists($domain_name)) {
            $app->log("sogo_helper::create_sogo_table(): SOGo table exists for domain: {$domain_name}", LOGLEVEL_DEBUG);
            if ($call_sync)
                return $this->sync_mail_users($domain_name);
            else
                return true;
        }

        // ALTER TABLE `xyz_users` ADD `idn_mail` VARCHAR( 500 ) NOT NULL AFTER `mail`
        //* @todo optimize table to reduce the space requirements (varchar(500) too much in most cases)
        //* @todo use mysql charset from config file.!
        $sql = "
CREATE TABLE IF NOT EXISTS `{$this->get_valid_sogo_table_name($domain_name)}` (
  `c_uid` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_cn` text CHARACTER SET utf8 NOT NULL,
  `c_name` varchar(500) CHARACTER SET utf8 NOT NULL,
  `mail` varchar(500) CHARACTER SET utf8 NOT NULL,
  `idn_mail` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_imaplogin` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_sievehostname` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_imaphostname` varchar(500) CHARACTER SET utf8 NOT NULL,
  `c_domain` varchar(255) CHARACTER SET utf8 NOT NULL,
  `c_password` varchar(255) CHARACTER SET utf8 NOT NULL";

        //* build up the mail aliases
        $acount_n = (int) $this->get_max_alias_count($domain_name, 'n'); //* none active
        $acount_y = (int) $this->get_max_alias_count($domain_name, 'y'); //* active
        $a_cnt = (int) ($acount_n + $acount_y);
        if ($a_cnt > 0) {
            //* append alias sql
            for ($index = 0; $index < $a_cnt; $index++) {
                $sql .= ",
    `alias_{$index}` varchar(500) CHARACTER SET utf8 NOT NULL";
            }
        }
        //* end sql mail aliases statement
        $sql .= ",
  UNIQUE KEY `c_uid` ( `c_uid` ( 333 ) )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $sqlres = & $this->sqlConnect();
        $result = $sqlres->query($sql) ? TRUE : FALSE;
        $app->log("sogo_helper::create_sogo_table(): add SOGo table for domain: {$domain_name}" . (!$result ? "\n\tERROR\t\n{$sql}" : ""), ($result ? LOGLEVEL_DEBUG : LOGLEVEL_ERROR));
        if ($call_sync)
            $result &= $this->sync_mail_users($domain_name);
        return $result;
    }

    /**
     * query all mail domains, based on module settings
     * @global app $app
     * @global array $conf
     * @param string $active
     * @return boolean|array boolean false on failure or empty query
     */
    public function get_mail_domain_names($active = 'y') {
        global $app, $conf;
        if (!in_array($active, array('n', 'y')))
            $active = 'y';
        $mail_domains_sql = FALSE;
        if ($this->module_settings->all_domains && $this->module_settings->allow_same_instance) {
            //* allow all domains + same instance
            $mail_domains_sql = "SELECT `domain` FROM `mail_domain` WHERE `active`='{$active}'";
        } else if (!$this->module_settings->all_domains && $this->module_settings->allow_same_instance) {
            //* allow only domains with sogo domain config + same instance
            $mail_domains_sql = "SELECT md.`domain` FROM `mail_domain` md, `sogo_domains` sd WHERE md.`active`='{$active}' AND md.`domain_id`=sd.`domain_id`";
        } else if ($this->module_settings->all_domains && !$this->module_settings->allow_same_instance) {
            //* allow all domains but only for this server
            $mail_domains_sql = "SELECT md.`domain` FROM `mail_domain` md, `sogo_domains` sd WHERE `active`='{$active}' AND sd.`server_id`=" . intval($conf['server_id']);
        } else if (!$this->module_settings->all_domains && !$this->module_settings->allow_same_instance) {
            //* allow only domains with sogo domain config and located on this server
            $mail_domains_sql = "SELECT md.`domain` FROM `mail_domain` md, `sogo_domains` sd WHERE md.`active`='{$active}' AND md.`domain_id`=sd.`domain_id` AND sd.`server_id`=" . intval($conf['server_id']);
        }
        if ($mail_domains_sql !== FALSE) {
            return $this->getDB()->queryAllRecords($mail_domains_sql);
        } else
            return FALSE;
    }

    public function load_module_settings($server_id = 1) {
        global $app;
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

    public function explode2array(& $value, $separator, $set_tpl_loop = false, $tpl_loop_item_name = NULL, $tpl_loop_name = NULL, & $tpl) {
        if ($set_tpl_loop === FALSE) {
            $value = explode($separator, $value);
        } else if (
                ($tpl_loop_item_name !== null && is_string($tpl_loop_item_name) && strlen($tpl_loop_item_name) > 0) &&
                ($tpl_loop_name !== null && is_string($tpl_loop_name) && strlen($tpl_loop_name) > 0) &&
                ($tpl !== null && $tpl instanceof tpl)
        ) {
            $_arr = explode($separator, $value);
            $arr = array();
            foreach ($_arr as $item)
                $arr[] = array($tpl_loop_item_name => $item);
            $tpl->setLoop($tpl_loop_name, $arr);
        }
    }

    /**
     * check the number of alias columns for a domain name, and create more if to low
     * @param string $domain
     * @return boolean
     */
    public function check_alias_columns($domain) {
        global $app;
        //* get total alias count for domain
        $acount_n = (int) $this->get_max_alias_count($domain, 'n'); //* none active
        $acount_y = (int) $this->get_max_alias_count($domain, 'y'); //* active
        $acount = (int) ($acount_n + $acount_y);
        //* get alias columns in table for domain
        $dtacount = (int) $this->get_sogo_table_alias_column_count($domain);
        $has_error = FALSE;
        //* if alias columns count in table for domain are to low
        if ($dtacount < $acount) {
            //* update domain table
            $sql = array();
            for ($index = 0; $index < intval(($acount - $dtacount)); $index++) {
                $_i = (int) ($dtacount + $index);
                $sql[] = "ALTER TABLE `{$this->get_valid_sogo_table_name($domain)}` ADD `alias_{$_i}` VARCHAR( 500 ) NOT NULL ";
            }
            $sqlres = & $this->sqlConnect();
            foreach ($sql as $value) {
                if (!$sqlres->query($value)) {
                    $app->log("sogo_helper::check_alias_columns(): update domain table for [{$domain}], FAILD" . PHP_EOL . "SQL: {$value}" . PHP_EOL . "SQL Error: " . $sqlres->error . PHP_EOL . "FILE:" . __FILE__ . ":" . (__LINE__ - 1), LOGLEVEL_ERROR);
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
        $emails = $this->getDB()->queryOneRecord("SELECT count(`email`) as cnt FROM `mail_user` WHERE `email` LIKE '%@{$domain_name}'" . ($imap_enabled ? " AND `disableimap` = 'n'" : ""));
        if ($emails !== FALSE && ((int) $emails['cnt'] > 0)) {
            return true;
        }
        return false;
    }

    public function get_server($sid) {
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
    public function get_server_config($server_id = NULL) {
        if (!isset(self::$sCache[$server_id])) {
            global $app, $conf;
            if ($server_id === NULL || !is_int($server_id))
                $server_id = $conf['server_id'];

            $sql = "SELECT * FROM `sogo_config` WHERE `server_id`=" . intval($server_id);
            $server_default = $this->getDB(false)->queryOneRecord($sql);

            if (!$server_default) {
                $app->log("SOGo get server config failed.", LOGLEVEL_ERROR);
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
    public function get_domain_config($domain_name, $full_server_conf = false) {
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
                $app->log("sogo_helper::get_domain_config(): failed. Unable to get server config from domain {$domain_name}", LOGLEVEL_WARN);
                $app->log("sogo_helper::get_domain_config(): {$server_default_sql}", LOGLEVEL_WARN);
                self::$dnCache[$domain_name] = false; //* if server default is not isset we must stop it from running to prevent SOGo or system failures
                return self::$dnCache[$domain_name];
            }
            //* @todo if same instace is allowed force server host / ip on the mail server (NO localhosts)
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
    public function get_sogo_table_alias_column_count($domain_name) {
        global $conf;
        $sqlres = & $this->sqlConnect();
        $sql = "SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_NAME`='{$sqlres->escape_string($this->get_valid_sogo_table_name($domain_name))}' AND `TABLE_SCHEMA`='{$conf['sogo_database_name']}' AND `COLUMN_NAME` LIKE 'alias_%'";
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
    public function drop_sogo_users_table($domain_name, $domain_id) {
        global $app;
        if (!empty($domain_name) && $domain_name !== false) {
            $app->log("sogo_helper::drop_sogo_users_table(): {$domain_id}#{$domain_name}", LOGLEVEL_DEBUG);
            $sogo_db = & $this->sqlConnect();
            $sogo_db->query("DROP TABLE {$this->get_valid_sogo_table_name($domain_name)}");
            if ($sogo_db->error) {
                $app->log("sogo_helper::sogo_domain_delete(): SQL ERROR:\n{$sogo_db->error}\n{$domain_id}#{$domain_name}", LOGLEVEL_WARN);
            }
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
            global $conf, $app;
            $domain = $this->get_valid_sogo_table_name($domain);
            $sqlres = & $this->sqlConnect();
            $sql1 = "SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='{$sqlres->escape_string($conf['sogo_database_name'])}' AND `TABLE_NAME`='{$sqlres->escape_string($domain)}'";
            $tmp = $sqlres->query($sql1);
            while ($obj = $tmp->fetch_object()) {
                if ($obj->TABLE_NAME == $domain) {
                    self::$sutCache[$domain] = true;
                    return self::$sutCache[$domain];
                }
            }
            $app->log("SOGo table do not exists [{$domain}]", LOGLEVEL_DEBUG);
            self::$sutCache[$domain] = false;
            return (bool) self::$sutCache[$domain];
        }
        return (bool) self::$sutCache[$domain];
    }

    /**
     * SOGo wont lookup a table with - or . in them so replace with _
     * @param string $domain_name
     * @return string 
     */
    public function get_valid_sogo_table_name($domain_name) {
        global $conf;
        $domain_name = str_replace(array('-', '.'), '_', $domain_name);
        if (!$this->sogo_tb_compatibility)
            $domain_name = str_replace('__', '_', $domain_name); //* replace double '_'
        return str_replace('{domain}', $domain_name, $conf['sogo_domain_table_tpl']);
    }

    /**
     * get the highest alias mail count for a mail domain
     * @param string $domain_name
     * @param string $active y|n
     * @return integer
     */
    public function get_max_alias_count($domain_name, $active = 'y') {
        $_md5 = md5($domain_name . $active);
        if (!isset(self::$daCache[$_md5])) {
            $a_cnt = 0;
            $aliases = $this->get_alias_counters($domain_name, $active);
            if (is_array($aliases)) {
                foreach ($aliases as $value)
                    $a_cnt = (int) ((int) $a_cnt < (int) $value['alias_cnt'] ? $value['alias_cnt'] : $a_cnt);
                self::$daCache[$_md5] = (int) $a_cnt;
            } else
                self::$daCache[$_md5] = 0;
        }
        return (int) self::$daCache[$_md5];
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
        $sql_md5 = md5($sql);
        if (!isset(self::$daCache[$sql_md5])) {
            $res = $this->getDB()->queryOneRecord($sql);
            self::$daCache[$sql_md5] = (int) (isset($res['alias_cnt']) ? $res['alias_cnt'] : 0);
        }
        return (int) self::$daCache[$sql_md5];
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
        $sql_md5 = md5($sql);
        if (!isset(self::$daCache[$sql_md5])) {
            $res = $this->getDB()->queryAllRecords($sql);
            self::$daCache[$sql_md5] = $res;
        }
        return self::$daCache[$sql_md5];
    }

    /**
     * Get a mysqli connection object to the mysql database server
     * @global array $conf
     * @return mysqli
     * 
     * @todo create fail safe in case sql connect fails, 
     *       currently it will kill the server.php script 
     *       causing ispconfig cron to stop working
     */
    public function & sqlConnect() {
        global $conf, $app;

        if ($this->_sqlObject == NULL) {
            $this->_sqlObject = new mysqli($conf['sogo_database_host'], $conf['sogo_database_user'], $conf['sogo_database_passwd'], $conf['sogo_database_name'], $conf['sogo_database_port']);
            if (mysqli_connect_errno()) {
                $app->log(sprintf("SOGo DB, Connect failed: %s\n", mysqli_connect_error()), LOGLEVEL_ERROR);
                return new mysqli(); //* return empty object 
            }
        }
        //* check if the connetion is still good.!
        if ($this->_sqlObject->ping()) {
            return $this->_sqlObject;
        } else {
            //* not good create a new one.
            $this->_sqlObject = new mysqli($conf['sogo_database_host'], $conf['sogo_database_user'], $conf['sogo_database_passwd'], $conf['sogo_database_name'], $conf['sogo_database_port']);
            if (mysqli_connect_errno()) {
                $app->log(sprintf("SOGo DB, Connect failed: %s\n", mysqli_connect_error()), LOGLEVEL_ERROR);
                return new mysqli(); //* return empty object 
            }
        }
        return $this->_sqlObject;
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
        } else {
            $log_msg = 'sogo_helper::getDB() : No database connection object found';
            // $app->log($log_msg, LOGLEVEL_WARN); //* no db object even 'app->log' will fail
            $this->_write_ispc_log($log_msg);
        }
        return NULL;
    }

    private function _write_ispc_log($log_msg) {
        global $conf;
        if (file_exists($conf['log_file'])) {
            if (!$fp = fopen($conf['log_file'], 'a')) {
                echo 'Unable to open logfile.';
            }
            if (!fwrite($fp, $log_msg . "\r\n")) {
                echo 'Unable to write to logfile.';
            }
            echo $log_msg . "\n";
            fclose($fp);
        }
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

    public function __destruct() {
        if ($this->_sqlObject != null)
            try {
                $this->_sqlObject->close();
            } catch (Exception $ex) {
                
            }
    }

    //* #START:# Methods from interface/lib/class/function.php:~354
    /**
     * IDN converter wrapper.
     * all converter classes should be placed in ISPC_CLASS_PATH.'/idn/'
     */
    private function _idn_encode_decode($domain, $encode = true) {
        if ($domain == '')
            return '';
        if (preg_match('/^[0-9\.]+$/', $domain))
            return $domain; // may be an ip address - anyway does not need to bee encoded









            
// get domain and user part if it is an email
        $user_part = false;
        if (strpos($domain, '@') !== false) {
            $user_part = substr($domain, 0, strrpos($domain, '@'));
            $domain = substr($domain, strrpos($domain, '@') + 1);
        }

        if ($encode == true) {
            if (function_exists('idn_to_ascii')) {
                $domain = idn_to_ascii($domain);
            } elseif (file_exists(ISPC_CLASS_PATH . '/idn/idna_convert.class.php')) {
                /* use idna class:
                 * @author  Matthias Sommerfeld <mso@phlylabs.de>
                 * @copyright 2004-2011 phlyLabs Berlin, http://phlylabs.de
                 * @version 0.8.0 2011-03-11
                 */
                if (!is_object($this->idn_converter) || $this->idn_converter_name != 'idna_convert.class') {
                    include_once ISPC_CLASS_PATH . '/idn/idna_convert.class.php';
                    $this->idn_converter = new idna_convert(array('idn_version' => 2008));
                    $this->idn_converter_name = 'idna_convert.class';
                }
                $domain = $this->idn_converter->encode($domain);
            }
        } else {
            if (function_exists('idn_to_utf8')) {
                $domain = idn_to_utf8($domain);
            } elseif (file_exists(ISPC_CLASS_PATH . '/idn/idna_convert.class.php')) {
                /* use idna class:
                 * @author  Matthias Sommerfeld <mso@phlylabs.de>
                 * @copyright 2004-2011 phlyLabs Berlin, http://phlylabs.de
                 * @version 0.8.0 2011-03-11
                 */

                if (!is_object($this->idn_converter) || $this->idn_converter_name != 'idna_convert.class') {
                    include_once ISPC_CLASS_PATH . '/idn/idna_convert.class.php';
                    $this->idn_converter = new idna_convert(array('idn_version' => 2008));
                    $this->idn_converter_name = 'idna_convert.class';
                }
                $domain = $this->idn_converter->decode($domain);
            }
        }

        if ($user_part !== false)
            return $user_part . '@' . $domain;
        else
            return $domain;
    }

    //* from interface/lib/class/function.php:~411
    public function idn_encode($domain) {
        $domains = explode("\n", $domain);
        for ($d = 0; $d < count($domains); $d++) {
            $domains[$d] = $this->_idn_encode_decode($domains[$d], true);
        }
        return implode("\n", $domains);
    }

    //* from interface/lib/class/function.php:~419
    public function idn_decode($domain) {
        $domains = explode("\n", $domain);
        for ($d = 0; $d < count($domains); $d++) {
            $domains[$d] = $this->_idn_encode_decode($domains[$d], false);
        }
        return implode("\n", $domains);
    }

    //* #END:# Methods from interface/lib/class/function.php
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
