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

$form["title"] = "SOGo Domain";
$form["description"] = "";
$form["name"] = "sogo_domains";
$form["action"] = "sogo_mail_domains_edit.php";
$form["db_table"] = "sogo_domains";
$form["db_table_idx"] = "sogo_id";
$form["db_history"] = "yes";
$form["tab_default"] = "domain";
$form["list_default"] = "sogo_mail_domain_list.php";
$form["auth"] = 'yes'; // yes / no
$form["auth_preset"]["userid"] = 0; // 0 = id of the user, > 0 id must match with id of current user
$form["auth_preset"]["groupid"] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form["auth_preset"]["perm_user"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_group"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_other"] = ''; //r = read, i = insert, u = update, d = delete

/*
 * SERVER:
 * SOGoEnableDomainBasedUID
 * 
 * DOMAIN:
 * SOGoFreeBusyDefaultInterval
 * SOGoMailPollingIntervals
 * 
 */

$form["tabs"]['domain'] = array(
    'title' => "SOGo Domain",
    'width' => 70,
    'template' => ($app->auth->is_admin() ? "templates/sogo_domains_edit.htm" : "templates/sogo_domains_user_edit.htm"),
    /* started using permission objects so not needed any more
     'template' => ($app->auth->is_admin() ? "templates/sogo_domains_edit.htm" : ($app->auth->has_clients($app->auth->get_user_id()) ? "templates/sogo_domains_reseller_edit.htm" : "templates/sogo_domains_user_edit.htm")),*/
    'fields' => array(
        'domain_id' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'TEXT',
            'default' => 0,
            'value' => '',
            'maxlength' => '',
            'required' => 1,
            'width' => 100,
        ),
        'domain_name' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '',
            'value' => '',
            'maxlength' => '',
            'required' => 1,
            'width' => 100,
        ),
        'server_id' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'TEXT',
            'default' => 0,
            'value' => '',
            'maxlength' => '',
            'required' => 1,
            'width' => 100,
        ),
        'server_name' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '',
            'value' => '',
            'maxlength' => '',
            'required' => 1,
            'width' => 100,
        ),
        'SOGoSieveScriptsEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => 'No',
                'YES' => 'Yes',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoVacationEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => 'No',
                'YES' => 'Yes',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoSearchMinimumWordLength' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'TEXT',
            'default' => 2,
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoSubscriptionFolderFormat' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '%{FolderName} (%{UserName} <%{Email}>)',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
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
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoACLsSendEMailNotifications' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NO' => 'No',
                'YES' => 'Yes',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoAppointmentSendEMailNotifications' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NO' => 'No',
                'YES' => 'Yes',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoFoldersSendEMailNotifications' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => 'No',
                'YES' => 'Yes',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoNotifyOnPersonalModifications' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => 'No',
                'YES' => 'Yes',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoNotifyOnExternalModifications' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => 'No',
                'YES' => 'Yes',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        /* use system tab for this settings
          'SOGoForceExternalLoginWithEmail' => array(
          'datatype' => 'VARCHAR',
          'formtype' => 'SELECT',
          'default' => 'YES',
          'value' => array(
          'NO' => 'No',
          'YES' => 'Yes',
          ),
          'maxlength' => '',
          'required' => 0,
          'width' => 100,
          ),
         */
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
//                    'errmsg' => 'Calendar Default Roles can\'t be larger than 5',
//                )
//            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
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
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoSuperUsernames' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'CHECKBOXARRAY',
            'default' => 'postmaster@${DOMAIN}',
            'value' => '',
            'datasource' => array(
                'type' => 'SQL',
                'querystring' => 'SELECT `email` FROM `mail_user` WHERE `email` LIKE \'%@{DOMAINNAME}\' AND {AUTHSQL} ORDER BY email',
                'keyfield' => 'email',
                'valuefield' => 'email'
            ),
            'maxlength' => '',
            'separator' => ',',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoCalendarDefaultReminder' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => '-PT5M',
            'value' => array(
                '-PT5M' => '5_MINUTES_BEFORE',
                '-PT10M' => '10_MINUTES_BEFORE',
                '-PT15M' => '15_MINUTES_BEFORE',
                '-PT30M' => '30_MINUTES_BEFORE',
                '-PT45M' => '45_MINUTES_BEFORE',
                '-PT1H' => '1_HOUR_BEFORE',
                '-PT2H' => '2_HOURS_BEFORE',
                '-PT5H' => '5_HOURS_BEFORE',
                '-PT15H' => '15_HOURS_BEFORE',
                '-P1D' => '1_DAY_BEFORE',
                '-P2D' => '2_DAYS_BEFORE',
                '-P1W' => '1_WEEK_BEFORE',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoCalendarEventsDefaultClassification' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'PUBLIC',
            'value' => array(
                'PUBLIC' => 'Public',
                'CONFIDENTIAL' => 'Confidential',
                'PRIVATE' => 'Private',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoCalendarTasksDefaultClassification' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'PUBLIC',
            'value' => array(
                'PUBLIC' => 'Public',
                'CONFIDENTIAL' => 'Confidential',
                'PRIVATE' => 'Private',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoCalendarShouldDisplayWeekend' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NO' => 'No',
                'YES' => 'Yes',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoDayStartTime' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'TEXT',
            'default' => 8,
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoDayEndTime' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'TEXT',
            'default' => 18,
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoFirstDayOfWeek' => array(
            'datatype' => 'INTEGER',
            'formtype' => 'SELECT',
            'default' => 1,
            'value' => array(
                0 => 'Sunday',
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoFirstWeekOfYear' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'FirstFullWeek',
            'value' => array(
                'January1' => 'January 1',
                'First4DayWeek' => 'First 4 Day Week',
                'FirstFullWeek' => 'First Full Week',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        //* www.sogo.nu/development/translations.html
        'SOGoLanguage' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'English',
            'value' => array(
                'English' => 'English',
                'Arabic' => 'Arabic',
                'BrazilianPortuguese' => 'Brazilian (Portuguese)',
                'Catalan' => 'Catalan',
                'Czech' => 'Czech',
                'Danish' => 'Danish',
                'Dutch' => 'Dutch',
                'French' => 'French',
                'German' => 'German',
                'Hungarian' => 'Hungarian',
                'Icelandic' => 'Icelandic',
                'Italian' => 'Italian',
                'Polish' => 'Polish',
                'Russian' => 'Russian',
                'Slovak' => 'Slovak',
                'Swedish' => 'Swedish',
                'Ukrainian' => 'Ukrainian',
                'Welsh' => 'Welsh',
                'Finnish' => 'Finnish',
                'NorwegianBokmal' => 'Norwegian (Bokm&aring;l)',
                'NorwegianNynorsk' => 'Norwegian (Nynorsk)',
                'SpanishSpain' => 'Spanish (Spain)',
                'SpanishArgentina' => 'Spanish (Argentina)',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoLoginModule' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'Mail',
            'value' => array(
                'Calendar' => 'Calendar',
                'Mail' => 'Mail',
                'Contacts' => 'Contacts',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoMailComposeMessageType' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'text',
            'value' => array(
                'text' => 'Text',
                'html' => 'HTML',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        //* field is created in sogo_config_edit.php
        'SOGoMailListViewColumnsOrder' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'CUSTOM',
            'default' => 'Flagged,Attachment,Priority,From,Subject,Unread,Date,Size',
            'value' => '',
            //* the list of columns that are available to sorting, 
            //* i don't know any other cols than the ones here.
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
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoRefreshViewCheck' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'every_minute',
            'value' => array(
                'once_per_hour' => 'once_per_hour',
                'every_30_minutes' => 'every_30_minutes',
                'every_20_minutes' => 'every_20_minutes',
                'every_10_minutes' => 'every_10_minutes',
                'every_5_minutes' => 'every_5_minutes',
                'every_2_minutes' => 'every_2_minutes',
                'every_minute' => 'every_minute',
                'manually' => 'manually',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoMailMessageForwarding' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'inline',
            'value' => array(
                'inline' => 'Inline',
                ' attached' => 'Attached',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoMailReplyPlacement' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'below',
            'value' => array(
                'above' => 'Above',
                'below' => 'Below',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoMailSignaturePlacement' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'below',
            'value' => array(
                'above' => 'Above',
                'below' => 'Below',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoTimeFormat' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '%H:%M',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoMailUseOutlookStyleReplies' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => 'No',
                'YES' => 'Yes',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoCalendarDefaultCategoryColor' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '#aaa',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoDefaultCalendar' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'selected',
            'value' => array(
                'selected' => 'Selected',
                'personal' => 'Personal',
                'first' => 'First',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoForwardEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoMailShowSubscribedFoldersOnly' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
    )
);
//* admin and resellers
//if ($app->auth->is_admin() || $app->auth->has_clients($app->auth->get_user_id())) {

    //* IMAP
    $form["tabs"]['domain']['fields']['SOGoDraftsFolderName'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => 'Drafts',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoSentFolderName'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => 'Sent',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoTrashFolderName'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => 'Trash',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
//}
//* Admins only
//if ($app->auth->is_admin()) {
    $form["tabs"]['domain']['fields']['SOGoSMTPAuthenticationType'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'YES',
        'value' => array(
            'NO' => $app->lng('No'),
            'PLAIN' => $app->lng('Plain'),
        ),
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoCustomXML'] = array(
        'datatype' => 'TEXT',
        'formtype' => 'TEXTAREA',
        'default' => '',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
        'rows' => 20,
        'cols' => 30,
    );
    //* SIEVE
    $form["tabs"]['domain']['fields']['SOGoSieveServer'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => 'sieve://{SERVERNAME}:4190',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoSieveFolderEncoding'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'UTF-7',
        'value' => array(
            'UTF-7' => 'UTF-7',
            'UTF-8' => 'UTF-8',
        ),
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    //* SMTP
    $form["tabs"]['domain']['fields']['SOGoMailCustomFromEnabled'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'NO',
        'value' => array(
            'NO' => 'No',
            'YES' => 'Yes',
        ),
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoMailSpoolPath'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => '/var/spool/sogo',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoMailingMechanism'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'below',
        'value' => array(
            'smtp' => 'SMTP',
            'sendmail' => 'SendMail',
        ),
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoSMTPServer'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => '{SERVERNAME}',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    //* IMAP
    $form["tabs"]['domain']['fields']['SOGoMailAuxiliaryUserAccountsEnabled'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'NO',
        'value' => array(
            'NO' => 'No',
            'YES' => 'Yes',
        ),
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoIMAPServer'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => 'imaps://{SERVERNAME}:143/?tls=YES',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoIMAPAclConformsToIMAPExt'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'YES',
        'value' => array(
            'NO' => 'No',
            'YES' => 'Yes',
        ),
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['domain']['fields']['SOGoIMAPAclStyle'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'rfc4314',
        'value' => array(
            'rfc2086' => 'RFC 2086', 'rfc4314' => 'RFC 4314',
        ),
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
//}