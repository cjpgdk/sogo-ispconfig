<?php

/*
 * Wee don't realy have a table to lookup but if we have this file 
 * we can can allways add a table with all the colums we need
 * 
 * the list of columns in this file is the defaults from my default SOGo.conf file,
 * we can add more columns from the form it self and any extra settings in the SOGo.conf 
 * not listed here are automatic added to the UI form.
 */


$form["title"] = "SOGo Config";
$form["description"] = "";
$form["name"] = "sogo_config";
$form["action"] = "sogo_config_edit.php";
/*
 * To avoid the ispconfig classes to create a table 
 * we set this to a table that exists.!
 */
$form["db_table"] = "server";
//$form["db_table"] = "sogo_config";
$form["db_table_idx"] = "sogo_id";
$form["db_history"] = "yes";
$form["tab_default"] = "sogo";
$form["list_default"] = "sogo_config.php";
$form["auth"] = 'yes'; // yes / no
$form["auth_preset"]["userid"] = 0; // 0 = id of the user, > 0 id must match with id of current user
$form["auth_preset"]["groupid"] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form["auth_preset"]["perm_user"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_group"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_other"] = ''; //r = read, i = insert, u = update, d = delete


$form["tabs"]['sogo'] = array(
    'title' => "SOGo Defaults",
    'width' => 70,
    'template' => "templates/sogo_config_edit.htm",
    'fields' => array(
        'SOGoPageTitle' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'ISPConfig 3 w/SOGo',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoMemcachedHost' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '127.0.0.1',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoLoginModule' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'Mail',
            'value' => array(
                'Calendar' => $app->lng('Calendar'),
                'Mail' => $app->lng('Mail'),
                'Contacts' => $app->lng('Contacts'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoZipPath' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '/usr/bin/zip',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoSoftQuotaRatio' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '0.9',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoMailUseOutlookStyleReplies' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoMailAuxiliaryUserAccountsEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoMailCustomFromEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoDefaultCalendar' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'selected',
            'value' => array(
                'selected' => $app->lng('Selected'),
                'personal' => $app->lng('Personal'),
                'first' => $app->lng('First'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        //* field is created in sogo_config_edit.php
        'SOGoMailListViewColumnsOrder' => array(
            'datatype' => 'CUSTOM',
            'formtype' => 'CUSTOM',
            'default' => 'Flagged,Attachment,Priority,From,Subject,Unread,Date,Size',
            'value' => '',
            //* the list of columns that are available to sorting, i don't know any other cols than the ones here.
            'cols_available' => array(
                'Flagged',
                'Attachment',
                'Priority',
                'From',
                'Subject',
                'Unread',
                'Date',
                'Size'
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'NGImap4ConnectionStringSeparator' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '.',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoEnableEMailAlarms' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'OCSEMailAlarmsFolderURL' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'mysql://${SOGOUSERN}:${SOGOUSERPW}@${MYSQLHOST}:${MYSQLPORT}/${SOGODB}/sogo_mailalarms_folder',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'OCSFolderInfoURL' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'mysql://${SOGOUSERN}:${SOGOUSERPW}@${MYSQLHOST}:${MYSQLPORT}/${SOGODB}/sogo_folder_info',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'OCSSessionsFolderURL' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'mysql://${SOGOUSERN}:${SOGOUSERPW}@${MYSQLHOST}:${MYSQLPORT}/${SOGODB}/sogo_sessions_folder',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoProfileURL' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'mysql://${SOGOUSERN}:${SOGOUSERPW}@${MYSQLHOST}:${MYSQLPORT}/${SOGODB}/sogo_user_profile',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoACLsSendEMailNotifcations' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoAppointmentSendEMailNotifcations' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoAppointmentSendEMailReceipts' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoFoldersSendEMailNotifcations' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'No',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        //* this NEEDs to set to LDAP on old version of SOGo!!!
        'SOGoAuthenticationMethod' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'SQL',
            'value' => array(
                'LDAP' => $app->lng('LDAP'),
                'SQL' => $app->lng('MySQL/PostgreSQL'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoCalendarDefaultRoles' => array(
            'value' => array(
                'PublicViewer' => 'PublicViewer',
                'PublicDAndTViewer' => 'PublicDAndTViewer',
                'PublicModifer' => 'PublicModifer',
                'PublicResponder' => 'PublicResponder',
                'ConfidentialViewer' => 'ConfidentialViewer',
                'ConfidentialDAndTViewer' => 'ConfidentialDAndTViewer',
                'ConfidentialModifer' => 'ConfidentialModifer',
                'ConfidentialResponder' => 'ConfidentialResponder',
                'PrivateViewer' => 'PrivateViewer',
                'PrivateDAndTViewer' => 'PrivateDAndTViewer',
                'PrivateModifer' => 'PrivateModifer',
                'PrivateResponder' => 'PrivateResponder',
                'ObjectCreator' => 'ObjectCreator',
                'ObjectEraser' => 'ObjectEraser'
            ),
            'datatype' => 'VARCHAR',
            'formtype' => 'CHECKBOXARRAY',
            'default' => 'PublicViewer,ConfidentialDAndTViewer',
            'separator' => ',',
//            'validators' => array(
//                array(
//                    'type' => 'CUSTOM',
//                    'class' => 'validate_sogo',
//                    'function' => 'isValidCalendarDefaultRolesField',
//                    'errmsg' => $app->lng('Calendar Default Roles can\'t be larger than 5'),
//                )
//            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoContactsDefaultRoles' => array(
            'value' => array(
                'ObjectViewer' => 'ObjectViewer',
                'ObjectEditor' => 'ObjectEditor',
                'ObjectCreator' => 'ObjectCreator',
                'ObjectEraser' => 'ObjectEraser',
            ),
            'datatype' => 'VARCHAR',
            'formtype' => 'CHECKBOXARRAY',
            'default' => 'ObjectEditor',
            'separator' => ',',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoFirstDayOfWeek' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'SELECT',
            'default' => 1,
            'value' => array(
                0 => $app->lng('Sunday'),
                1 => $app->lng('Monday'),
                2 => $app->lng('Tuesday'),
                3 => $app->lng('Wednesday'),
                4 => $app->lng('Thursday'),
                5 => $app->lng('Friday'),
                6 => $app->lng('Saturday'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoFirstWeekOfYear' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'FirstFullWeek',
            'value' => array(
                'January1' => $app->lng('January 1'),
                'First4DayWeek' => $app->lng('First 4 Day Week'),
                'FirstFullWeek' => $app->lng('First Full Week'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoForceIMAPLoginWithEmail' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoForwardEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoIMAPAclConformsToIMAPExt' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoIMAPServer' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'imaps://127.0.0.1:143/?tls=YES',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        //* www.sogo.nu/development/translations.html
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
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoMailMessageCheck' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'every_minute',
            'value' => array(
                'once_per_hour' => $app->lng('once_per_hour'),
                'every_30_minutes' => $app->lng('every_30_minutes'),
                'every_20_minutes' => $app->lng('every_20_minutes'),
                'every_10_minutes' => $app->lng('every_10_minutes'),
                'every_5_minutes' => $app->lng('every_5_minutes'),
                'every_2_minutes' => $app->lng('every_2_minutes'),
                'every_minute' => $app->lng('every_minute'),
                'manually' => $app->lng('manually'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoMailReplyPlacement' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'below',
            'value' => array(
                'above' => $app->lng('Above'),
                'below' => $app->lng('Below'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoSMTPServer' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '127.0.0.1',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoMailingMechanism' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'below',
            'value' => array(
                'smtp' => $app->lng('SMTP'),
                'sendmail' => $app->lng('SendMail'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoPasswordChangeEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoSieveScriptsEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoSieveServer' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'sieve://localhost:4190',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        /*
         * ::: http://www.sogo.nu/files/docs/SOGo%20Installation%20Guide.pdf
         * Parameter used to set a default time zone for users. 
         * The default timezone is set to UTC. The Olson database is a standard 
         * database that takes all the time zones around the world into account 
         * and represents them along with their history. On GNU/Linux systems, 
         * time zone definition files are available under /usr/share/zoneinfo. 
         * Listing the available files will give you the name of the available 
         * time zones. This could be America/New_York, Europe/Berlin, 
         * Asia/Tokyo or Africa/Lubumbashi. 
         * In our example, we set the time zone to America/Montreal
         */
        'SOGoTimeZone' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'Europe/Berlin',
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SOGoVacationEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'SxVMemLimit' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'TEXT',
            'default' => 384,
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'WOWorkersCount' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'TEXT',
            'default' => 3,
            'value' => '',
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        'WOUseRelativeURLs' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength'=>'',
            'required'=>0,
            'width'=>100,
        ),
        /* We are on multi domains config so this is required and set in sogo_config_edit.php on save/update
         * <key>domains</key>
            <dict>{{SOGODOMAINSCONF}}
            </dict>
         */
    )
);
//
//$form["tabs"]['domain'] = array (
//	'title' 	=> "SOGo Domain Defaults",
//	'width' 	=> 70,
//	'template' 	=> "templates/sogo_config_domain_edit.htm",
//	'fields' 	=> array (
//            
//	)
//);
//
//$form["tabs"]['user'] = array (
//	'title' 	=> "SOGo User Defaults",
//	'width' 	=> 70,
//	'template' 	=> "templates/sogo_config_user_edit.htm",
//	'fields' 	=> array (
//            
//	)
//);