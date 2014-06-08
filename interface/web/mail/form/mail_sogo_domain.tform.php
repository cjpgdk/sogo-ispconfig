<?php

$form["title"] = "SOGo Mail Domain";
$form["description"] = "";
$form["name"] = "mail_sogo_domain";
$form["action"] = "mail_sogo_domain_edit.php";
$form["db_table"] = "mail_domain";
$form["db_table_idx"] = "domain_id";
$form["tab_default"] = "sogo_domain";
$form["list_default"] = "mail_sogo_domain_list.php";
$form["auth"] = 'yes'; // yes / no

$form["auth_preset"]["userid"] = 0; // 0 = id of the user, > 0 id must match with id of current user
$form["auth_preset"]["groupid"] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form["auth_preset"]["perm_user"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_group"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_other"] = ''; //r = read, i = insert, u = update, d = delete

$domain = $app->db->queryOneRecord('SELECT `domain` FROM `mail_domain` WHERE `domain_id`=' . @$app->functions->intval($_REQUEST["id"]));

$form["tabs"]['sogo_domain'] = array(
    'title' => "SOGo Domain",
    'template' => (($_SESSION['s']['user']['typ'] == 'admin') ? "templates/mail_sogo_domain_admin_edit.htm" :"templates/mail_sogo_domain_edit.htm" ),
    'fields' => array(
        /*
         * Wee lookup the domain on save..
        'SOGoMailDomain' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' =>  @$domain['domain'],
            'value' => @$domain['domain'],
            'width' => '30',
            'maxlength' => '255',
            'searchable' => 1
        ),
        */
        'SOGoDraftsFolderName' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '',
            'value' => '',
            'width' => '30',
            'maxlength' => '255',
            'searchable' => 1
        ),
        'SOGoSentFolderName' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '',
            'value' => '',
            'width' => '30',
            'maxlength' => '255',
            'searchable' => 1
        ),
        'SOGoTrashFolderName' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '',
            'value' => '',
            'width' => '30',
            'maxlength' => '255',
            'searchable' => 1
        ),
        'SOGoMailShowSubscribedFoldersOnly' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO'=>'NO', 'YES'=>'YES'
            ),
            'searchable' => 1
        ),
        'SOGoLanguage' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'English',
            'value' => array(
                'English'=>'English',
                'Arabic'=>'Arabic',
                'Brazilian'=>'Brazilian',
                'Catalan'=>'Catalan',
                'Czech'=>'Czech',
                'Danish'=>'Danish',
                'Dutch'=>'Dutch',
                'French'=>'French',
                'German'=>'German',
                'Hungarian'=>'Hungarian',
                'Icelandic'=>'Icelandic',
                'Italian'=>'Italian',
                'Norwegian Bokmål'=>'Norwegian Bokmål',
                'Polish'=>'Polish',
                'Russian'=>'Russian',
                'Slovak'=>'Slovak',
                'Swedish'=>'Swedish',
                'Ukrainian'=>'Ukrainian',
                'Welsh'=>'Welsh',
            ),
            'searchable' => 1
        ),
        //* do select
        'SOGoSuperUsernames' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'CHECKBOXARRAY',
            'default' => '',
            'datasource' => array('type' => 'SQL',
                'querystring' => 'SELECT `email` FROM `mail_user` WHERE {AUTHSQL} AND `email` LIKE \'%@' . $domain['domain'] . '\' ORDER BY email',
                'keyfield' => 'email',
                'valuefield' => 'email'
            ),
            'separator'=>'|',
            'value' => '',
            'searchable' => 2
        ),
    )
);
?>
