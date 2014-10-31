<?php

/*
 * Copyright (C) 2014  Christian M. Jensen
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
 *  @author Christian M. Jensen <christian@cmjscripter.net>
 *  @copyright 2014 Christian M. Jensen
 *  @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

class sogo_helper {

    public function __construct() {
        global $app;
        if (!is_object($app->functions))
            $app->uses('functions');
    }

    /**
     * get a list of domains name,id and server of domains with sogo configurations
     * @return sogo_domains_list[]
     */
    public function listDomains() {
        global $app;
        $list = array();
        $result = $app->db->queryAllRecords('SELECT `sogo_id`, `domain_id`, `server_id`,`domain_name` FROM `sogo_domains`');
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
    public function configDomainExists($domain_id) {
        global $app;
        $result = $app->db->queryOneRecord('SELECT `domain_id` FROM `sogo_domains` WHERE `domain_id`=' . intval($domain_id));
        return (boolean) ($result['domain_id'] == $domain_id);
    }

    /**
     * get domain SOGo configuration id
     * @param integer $server_id
     * @return boolean
     */
    public function getDomainConfigIndex($domain_id) {
        global $app;
        $result = $app->db->queryOneRecord('SELECT `sogo_id` FROM `sogo_domains` WHERE `domain_id`=' . intval($domain_id));
        return isset($result['sogo_id']) ? $result['sogo_id'] : 0;
    }
//    
//    /**
//     * get a list of server name+id of server with sogo configurations
//     * @param app $app
//     * @return sogo_servers_list[]
//     */
//    public static function list_servers(&$app) {
//        $list = array();
//        $result = $app->db->queryAllRecords('SELECT `server_id`,`server_name` FROM `sogo_config`');
//        if ($result === FALSE)
//            return $list;
//        foreach ($result as $value) {
//            $list[] = new sogo_servers_list($value['server_id'], $value['server_name']);
//        }
//        return $list;
//    }

    /**
     * fetch all system servers with or without sogo config
     * @param boolean $all set to true to fetch all servers in system
     * @return sogo_servers_list[]
     */
    public function listSystemServers($all = false) {
        global $app;
        if (!$all)
            $result = $app->db->queryAllRecords('SELECT s.`server_id`, s.`server_name` FROM `server` s WHERE s.`server_id` NOT IN (SELECT `server_id` FROM `sogo_config`);');
        else
            $result = $app->db->queryAllRecords('SELECT `server_id`,`server_name` FROM `sogo_config`');

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
        global $app;
        $result = $app->db->queryOneRecord('SELECT `server_id` FROM `sogo_config` WHERE `server_id`=' . intval($server_id));
        return (boolean) ($result['server_id'] == $server_id);
    }

    /**
     * get servers SOGo configuration id
     * @param integer $server_id
     * @return boolean
     */
    public function getConfigIndex($server_id) {
        global $app;
        $result = $app->db->queryOneRecord('SELECT `sogo_id` FROM `sogo_config` WHERE `server_id`=' . intval($server_id));
        return isset($result['sogo_id']) ? $result['sogo_id'] : 0;
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
