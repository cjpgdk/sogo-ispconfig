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
 */
class sogo_helper {

    public function getResellerConfigPermissions($user_id) {
        
    }

    public function getClientConfigPermissions($user_id) {
        $user_id = intval($user_id);
        $records = array();
        $_tmp = $this->getDB()->queryAllRecords("SELECT scp.`scp_name` as name, scp.`scp_allow` as allow FROM `sogo_config_permissions_index` scpii, `sogo_config_permissions` scp WHERE scpii.`scpi_type`='client' AND scpii.`scpi_is_global`=1 AND scp.`scp_index`=scpii.`scpi`");
        //* flatten the array
        foreach ($_tmp as $value) {
            $records['permission_' . $value['name']] = $value['allow'];
        }
        unset($_tmp);
        if ($_records = $this->getDB()->queryAllRecords("SELECT * FROM `sogo_config_permissions_index`")) {
            foreach ($_records as $key => $value) {
                $clients = explode(',', $value['scpi_clients']);
                foreach ($clients as $client) {
                    if ($client == $user_id) {
                        $permissions_index = intval($value['scpi']);
                        $_tmp = $this->getDB()->queryAllRecords("SELECT scp.`scp_allow` as allow, scp.`scp_name` as name FROM `sogo_config_permissions` scp WHERE scp.`scp_index`={$permissions_index}");
                        //* override global
                        foreach ($_tmp as $value) {
                            $records['permission_' . $value['name']] = $value['allow'];
                        }
                        break;
                    }
                }
            }
            unset($_records);
        }
        return $records;
    }

    public function get_client_id() {
        global $app;
        return $app->functions->intval($_SESSION['s']['user']['client_id']);
    }

    //* FROM: auth.inc.php ($app->auth->get_user_id())
    public function get_user_id() {
        global $app;
        return $app->functions->intval($_SESSION['s']['user']['userid']);
    }

    /**
     * get sogo module config field 
     * @param integer $server_id 
     * @param string $field the config field to fetch or "all" (lowercase) to get them all 
     * @return string|array result will be empty if $field does not exists
     */
    public function getSOGoModuleConf($server_id = 1, $field = "all") {
        $result = $this->getDB()->queryOneRecord('SELECT * FROM `sogo_module` WHERE `server_id`=' . intval($server_id));
        if (strtolower($field) == 'all')
            return $result;
        else {
            if (isset($result[$field]))
                return $result[$field];
            else
                return "";
        }
        return array();
    }

    /**
     * check if extended debug is enabled
     * @return boolean
     */
    public static function isExtendedDebug() {
        return defined('SOGO_EXT_DEBUG_INFO') ? SOGO_EXT_DEBUG_INFO : false;
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
     * get a list of domains name,id and server of domains with sogo configurations
     * @return sogo_domains_list[]
     */
    public function listDomains($authsql = "") {
        $list = array();
        $result = $this->getDB()->queryAllRecords('SELECT `sogo_id`, `domain_id`, `server_id`,`domain_name` FROM `sogo_domains`' . (!empty($authsql) ? ' WHERE (' . $authsql . ')' : ''));
        if ($result === FALSE)
            return $list;
        foreach ($result as $value) {
            $list[] = new sogo_domains_list($value['sogo_id'], $value['domain_id'], $value['server_name'], $value['server_id']);
        }
        return $list;
    }

    /**
     * check if domain has a SOGo configuration
     * @param integer $domain_id
     * @return boolean
     */
    public function domainSOGoConfigExists($domain_id) {
        $result = $this->getDB()->queryOneRecord('SELECT `domain_id` FROM `sogo_domains` WHERE `domain_id`=' . intval($domain_id));
        return (boolean) ($result['domain_id'] == $domain_id);
    }

//    /**
//     * alias of domainSOGoConfigExists
//     * @see sogo_helper::domainSOGoConfigExists($domain_id)
//     * @deprecated since version pre.u10
//     */
//    public function configDomainExists($domain_id) {
//        return $this->domainSOGoConfigExists($domain_id);
//    }

    /**
     * get domain SOGo configuration id
     * @param integer $domain_id
     * @return boolean
     */
    public function getDomainConfigIndex($domain_id) {
        $result = $this->getDB()->queryOneRecord('SELECT `sogo_id` FROM `sogo_domains` WHERE `domain_id`=' . intval($domain_id));
        return isset($result['sogo_id']) ? $result['sogo_id'] : 0;
    }

    /**
     * fetch all system servers with or without sogo config
     * @param boolean $all set to true to fetch all servers in system
     * @return sogo_servers_list[]
     */
    public function listSystemServers($all = false) {
        if (!$all)
            $result = $this->getDB()->queryAllRecords('SELECT s.`server_id`, s.`server_name` FROM `server` s WHERE s.`server_id` NOT IN (SELECT `server_id` FROM `sogo_config`);');
        else
            $result = $this->getDB()->queryAllRecords('SELECT `server_id`,`server_name` FROM `sogo_config`');

        $list = array();
        if ($result === FALSE)
            return $list;
        foreach ($result as $value) {
            $list[] = new sogo_servers_list($value['server_id'], $value['server_name']);
        }
        return $list;
    }

    /**
     * check if server has a SOGo configuration
     * @param integer $server_id
     * @return boolean
     */
    public function configExists($server_id) {
        $result = $this->getDB()->queryOneRecord('SELECT `server_id` FROM `sogo_config` WHERE `server_id`=' . intval($server_id));
        return (boolean) ($result['server_id'] == $server_id) && (isset($result['server_id']) && $result['server_id'] > 0);
    }

    /**
     * check if domain has a SOGo configuration
     * @param integer $domain_id
     * @return boolean
     */
    public function configExistsByDomain($domain_id) {
        $result = $this->getDB()->queryOneRecord('SELECT sc.`server_id`, sd.`domain_id` FROM `sogo_config` sc, `sogo_domains` sd WHERE sc.`server_id`=sd.`server_id` AND sd.`domain_id`=' . intval($domain_id));
        return (boolean) ($result['domain_id'] == $domain_id) && (isset($result['server_id']) && $result['server_id'] > 0);
    }

    /**
     * get servers SOGo configuration id
     * @param integer $server_id
     * @return boolean
     */
    public function getConfigIndex($server_id) {
        $result = $this->getDB()->queryOneRecord('SELECT `sogo_id` FROM `sogo_config` WHERE `server_id`=' . intval($server_id));
        return isset($result['sogo_id']) ? $result['sogo_id'] : 0;
    }

    /**
     * gets the domain config fields from the database<br>
     * <pre><code>
     * $conf = sogo_helper->getDomainConfigFields();
     * print_r($conf);
     * --------------------
     * array(
     *     COLUMN-NAME => array(
     *         name => COLUMN-NAME,
     *         default => [Default value, set in db],
     *     ),
     *     ...
     * );
     * --------------------
     * </code></pre>
     * @return array
     */
    public function getDomainConfigFields() {
        $domains_columns = $this->getDB()->queryAllRecords("SHOW COLUMNS FROM `sogo_domains`");
        $ret = array();
        foreach ($domains_columns as $value) {
            if ($value['Field'] == "domain_id" || $value['Field'] == "domain_name" ||
                    $value['Field'] == "server_id" || $value['Field'] == "server_name" ||
                    $value['Field'] == "sogo_id" || $value['Field'] == "sys_userid" ||
                    $value['Field'] == "sys_groupid" || $value['Field'] == "sys_perm_user" ||
                    $value['Field'] == "sys_perm_group" || $value['Field'] == "sys_perm_other") {
                continue;
            }
            $ret[$value['Field']] = array('name' => $value['Field'], 'default' => $value['Default']);
        }
        return $ret;
    }

}

class sogo_domains_list {

    /**
     * @param integer $id
     * @param string $name
     * @param integer $server_id
     */
    public function __construct($id, $domain_id, $name, $server_id) {
        $this->name = $name;
        $this->id = (int) $id;
        $this->domain_id = (int) $domain_id;
        $this->server_id = (int) $server_id;
    }

    /** @var integer */
    public $id;

    /** @var string */
    public $name;

    /** @var integer */
    public $domain_id;

    /** @var integer */
    public $server_id;

}

class sogo_servers_list {

    /**
     * @param integer $id
     * @param string $name
     */
    public function __construct($id, $name) {
        $this->name = $name;
        $this->id = (int) $id;
    }

    /** @var integer */
    public $id;

    /** @var string */
    public $name;

}
