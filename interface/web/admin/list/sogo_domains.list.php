<?php

$liste["name"] = "sogo_domains";
$liste["table"] = "mail_domain";
$liste["table_idx"] = "domain_id";
$liste["search_prefix"] = "search_";
$liste["records_per_page"] = "15";
$liste["file"] = "sogo_domains_list.php";
$liste["edit_file"] = "sogo_domains_edit.php";
$liste["delete_file"] = "sogo_domains_del.php";
$liste["paging_tpl"] = "templates/paging.tpl.htm";
$liste["auth"] = "yes";

$liste["item"][] = array(
    'field' => "active",
    'datatype' => "VARCHAR",
    'formtype' => "SELECT",
    'op' => "=",
    'prefix' => "",
    'suffix' => "",
    'width' => "",
    'value' => array(
        'y' => "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>",
        'n' => "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>"
    )
);


$liste["item"][] = array(
    'field' => "sys_groupid",
    'datatype' => "INTEGER",
    'formtype' => "SELECT",
    'op' => "=",
    'prefix' => "",
    'suffix' => "",
    'datasource' => array(
        'type' => 'SQL',
        'querystring' => 'SELECT groupid, name FROM sys_group WHERE groupid != 1 ORDER BY name',
        'keyfield' => 'groupid',
        'valuefield' => 'name'
    ),
    'width' => "",
    'value' => ""
);

$liste["item"][] = array(
    'field' => "server_id",
    'datatype' => "INTEGER",
    'formtype' => "SELECT",
    'op' => "like",
    'prefix' => "",
    'suffix' => "",
    'datasource' => array(
        'type' => 'SQL',
        'querystring' => 'SELECT a.server_id, a.server_name FROM server a, mail_domain b WHERE (a.server_id = b.server_id) AND ({AUTHSQL-B}) ORDER BY a.server_name',
        'keyfield' => 'server_id',
        'valuefield' => 'server_name'
    ),
    'width' => "",
    'value' => ""
);

$liste["item"][] = array(
    'field' => "domain",
    'datatype' => "VARCHAR",
    'filters' => array(
        0 => array('event' => 'SHOW',
            'type' => 'IDNTOUTF8'
        )
    ),
    'formtype' => "TEXT",
    'op' => "like",
    'prefix' => "%",
    'suffix' => "%",
    'width' => "",
    'value' => ""
);
?>
