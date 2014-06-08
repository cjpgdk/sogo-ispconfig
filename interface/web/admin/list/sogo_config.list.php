<?php
/**
 * this file is allmost a plain copy of "server.list.php"
 * we just need the server name of mailsers only.
 */
$liste['name'] = 'sogo_server';
$liste['table'] = 'server';
$liste['table_idx'] = 'server_id';
$liste['search_prefix'] = 'search_';
$liste['records_per_page'] = "15";
$liste['file'] = 'sogo_config.php';
$liste['edit_file'] = 'sogo_config_edit.php';
$liste['paging_tpl'] = 'templates/paging.tpl.htm';
$liste['auth'] = 'yes';


$liste['item'][] = array(
    'field' => 'server_id',
    'datatype' => 'INTEGER',
    'formtype' => 'TEXT',
    'op' => '=',
    'prefix' => '',
    'suffix' => '',
    'width' => '',
    'value' => ''
);

$liste['item'][] = array(
    'field' => 'server_name',
    'datatype' => 'VARCHAR',
    'formtype' => 'TEXT',
    'op' => 'like',
    'prefix' => '%',
    'suffix' => '%',
    'width' => '',
    'value' => ''
);

//$liste['item'][] = array('field' => 'mail_server',
//    'datatype' => 'VARCHAR',
//    'formtype' => 'TEXT',
//    'op' => 'like',
//    'prefix' => '%',
//    'suffix' => '%',
//    'width' => '',
//    'value' => array('1' => "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>", '0' => "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>"));
//
//$liste['item'][] = array('field' => 'web_server',
//    'datatype' => 'VARCHAR',
//    'formtype' => 'TEXT',
//    'op' => 'like',
//    'prefix' => '%',
//    'suffix' => '%',
//    'width' => '',
//    'value' => array('1' => "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>", '0' => "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>"));
//
//$liste['item'][] = array('field' => 'dns_server',
//    'datatype' => 'VARCHAR',
//    'formtype' => 'TEXT',
//    'op' => 'like',
//    'prefix' => '%',
//    'suffix' => '%',
//    'width' => '',
//    'value' => array('1' => "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>", '0' => "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>"));
//
//$liste['item'][] = array('field' => 'file_server',
//    'datatype' => 'VARCHAR',
//    'formtype' => 'TEXT',
//    'op' => 'like',
//    'prefix' => '%',
//    'suffix' => '%',
//    'width' => '',
//    'value' => array('1' => "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>", '0' => "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>"));
//
//$liste['item'][] = array('field' => 'db_server',
//    'datatype' => 'VARCHAR',
//    'formtype' => 'TEXT',
//    'op' => 'like',
//    'prefix' => '%',
//    'suffix' => '%',
//    'width' => '',
//    'value' => array('1' => "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>", '0' => "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>"));
//
//$liste['item'][] = array('field' => 'vserver_server',
//    'datatype' => 'VARCHAR',
//    'formtype' => 'TEXT',
//    'op' => 'like',
//    'prefix' => '%',
//    'suffix' => '%',
//    'width' => '',
//    'value' => array('1' => "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>", '0' => "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>"));
?>