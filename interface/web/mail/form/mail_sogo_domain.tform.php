<?php

$form["title"] = "SOGo Mail Domain";
$form["description"] = "";
$form["name"] = "mail_sogo_domain";
$form["action"] = "mail_sogo_domain_edit.php";
$form["db_table"] = "mail_domain";
$form["db_table_idx"] = "domain_id";
$form["db_history"] = "yes";
$form["tab_default"] = "sogo_domain";
$form["list_default"] = "mail_sogo_domain_list.php";
$form["auth"] = 'yes'; // yes / no

$form["auth_preset"]["userid"] = 0; // 0 = id of the user, > 0 id must match with id of current user
$form["auth_preset"]["groupid"] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form["auth_preset"]["perm_user"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_group"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_other"] = ''; //r = read, i = insert, u = update, d = delete

$form["tabs"]['sogo_domain'] = array(
    'title' => "SOGo Domain",
    'width' => 100,
    'template' => ($app->auth->is_admin() ? "templates/mail_sogo_domain_admin_edit.htm" : ($app->auth->has_clients($app->auth->get_user_id()) ? "templates/mail_sogo_domain_reseller_edit.htm" : "templates/mail_sogo_domain_edit.htm")),
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
        ),
        'SOGoSentFolderName' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '',
            'value' => '',
            'width' => '30',
            'maxlength' => '255',
        ),
        'SOGoTrashFolderName' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '',
            'value' => '',
            'width' => '30',
            'maxlength' => '255',
        ),
        'SOGoMailShowSubscribedFoldersOnly' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => 'NO', 'YES' => 'YES'
            ),
        ),
        'SOGoLanguage' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'English',
            'value' => array(
                'English' => 'English',
                'Arabic' => 'Arabic',
                'Brazilian' => 'Brazilian',
                'Catalan' => 'Catalan',
                'Czech' => 'Czech',
                'Danish' => 'Danish',
                'Dutch' => 'Dutch',
                'French' => 'French',
                'German' => 'German',
                'Hungarian' => 'Hungarian',
                'Icelandic' => 'Icelandic',
                'Italian' => 'Italian',
                'Norwegian Bokmål' => 'Norwegian Bokmål',
                'Polish' => 'Polish',
                'Russian' => 'Russian',
                'Slovak' => 'Slovak',
                'Swedish' => 'Swedish',
                'Ukrainian' => 'Ukrainian',
                'Welsh' => 'Welsh',
            ),
        ),
        'SOGoSuperUsernames' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'CHECKBOXARRAY',
            'default' => '',
            'datasource' => array(
                'type' => 'SQL',
                'querystring' => 'SELECT `mail_user`.`email` FROM `mail_user`, `mail_domain` WHERE ' . $this->getAuthSQL('r', 'mail_user') . ' AND `mail_user`.`email` LIKE CONCAT(\'%@\',`mail_domain`.`domain`) AND `mail_domain`.`domain_id`=\'{RECORDID}\' ORDER BY `mail_user`.email',
                'keyfield' => 'email',
                'valuefield' => 'email'
            ),
            'separator' => '|',
            'value' => '',
        ),
    )
);
//** wee it works. :):)
//echo $form["tabs"]['sogo_domain']['fields']['SOGoSuperUsernames']['datasource']['querystring'];