<?php

/*
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

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$list_def_file = "list/sogo_domains.list.php";

$app->auth->check_module_permissions('admin');
$app->uses('listform_actions, sogo_helper');

//* remove old saved domain id
unset($_SESSION['s']['module']["sogo_conifg_domain_id"]);

class listform_action extends listform_actions {
    public $SQLExtJoin = "";
    /** @global app $app */
    public function onLoad() {
        global $app;
        $app->uses('tpl,listform,tform');

        $_sogo_domains = $app->sogo_helper->listDomains();
        $sogo_domains = array();
        foreach ($_sogo_domains as $value) {
            $sogo_domains[] = array('sogo_domain_id' => $value->id, 'domain_id' => $value->domain_id, 'sogo_server_id' => $value->server_id);
        }
        $app->tpl->setLoop('sogo_domains', $sogo_domains);
        unset($sogo_domains, $_sogo_domains);
        parent::onLoad();
    }

    /**
     * FIX wired and wrong usage of join.!
     * $lists['join_sql'] ** WRONG PLACE FOR JOIN
     * added 
     * $this->SQLExtJoin
     */
    public function getQueryString($no_limit = false) {
        global $app;
        $sql_where = '';

        //* Generate the search sql
        if ($app->listform->listDef['auth'] != 'no') {
            if ($_SESSION['s']['user']['typ'] == "admin") {
                $sql_where = '';
            } else {
                $sql_where = $app->tform->getAuthSQL('r', $app->listform->listDef['table']) . ' and';
                //$sql_where = $app->tform->getAuthSQL('r').' and';
            }
        }
        if ($this->SQLExtWhere != '') {
            $sql_where .= ' ' . $this->SQLExtWhere . ' and';
        }
        $sql_where = $app->listform->getSearchSQL($sql_where);
        if ($app->listform->listDef['join_sql'])
            $sql_where .= ' AND ' . $app->listform->listDef['join_sql'];
        $app->tpl->setVar($app->listform->searchValues);
        $order_by_sql = $this->SQLOrderBy;
        //* Generate SQL for paging
        $limit_sql = $app->listform->getPagingSQL($sql_where);
        $app->tpl->setVar('paging', $app->listform->pagingHTML);
        $extselect = '';
        $join = $this->SQLExtJoin;
        if ($this->SQLExtSelect != '') {
            if (substr($this->SQLExtSelect, 0, 1) != ',')
                $this->SQLExtSelect = ',' . $this->SQLExtSelect;
            $extselect .= $this->SQLExtSelect;
        }
        $table_selects = array();
        $table_selects[] = trim($app->listform->listDef['table']) . '.*';
        $app->listform->listDef['additional_tables'] = trim($app->listform->listDef['additional_tables']);
        if ($app->listform->listDef['additional_tables'] != '') {
            $additional_tables = explode(',', $app->listform->listDef['additional_tables']);
            foreach ($additional_tables as $additional_table) {
                $table_selects[] = trim($additional_table) . '.*';
            }
        }
        $select = implode(', ', $table_selects);
        $sql = 'SELECT ' . $select . $extselect . ' FROM ' . $app->listform->listDef['table'] . ($app->listform->listDef['additional_tables'] != '' ? ',' . $app->listform->listDef['additional_tables'] : '') . "$join WHERE $sql_where $order_by_sql";
        if ($no_limit == false)
            $sql .= " $limit_sql";
        return $sql;
    }

}

/*
  SELECT
  `mail_domain`.*,
  `sogo_domains`.server_id as sogo_server_id
  FROM
  `mail_domain`
  LEFT JOIN `sogo_domains`
  ON `mail_domain`.`domain_id`=`sogo_domains`.`domain_id`;
 */
$app->listform_action = new listform_action();
$app->listform_action->SQLExtSelect = " `sogo_domains`.server_id as sogo_server_id ";
$app->listform_action->SQLExtJoin = " LEFT JOIN `sogo_domains` ON `mail_domain`.`domain_id`=`sogo_domains`.`domain_id` ";
$app->listform_action->onLoad();
