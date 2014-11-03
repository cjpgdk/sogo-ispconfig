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

$form["title"] = "SOGo Domains";
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
 * 
 */

$form["tabs"]['domain'] = array(
    'title' => "SOGo Domain",
    'width' => 70,
    'template' => ($app->auth->is_admin() ? "templates/sogo_domains_edit.htm" : ($app->auth->has_clients($app->auth->get_user_id()) ? "templates/sogo_domains_reseller_edit.htm" : "templates/sogo_domains_user_edit.htm")),
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
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoSieveServer' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'sieve://localhost:4190',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoVacationEnabled' => array(
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
        'SOGoDraftsFolderName' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'Drafts',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoSentFolderName' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'Sent',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoTrashFolderName' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'Trash',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoIMAPServer' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => 'imaps://127.0.0.1:143/?tls=YES',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoSMTPServer' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '127.0.0.1',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoMailingMechanism' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'below',
            'value' => array(
                'smtp' => $app->lng('SMTP'),
                'sendmail' => $app->lng('SendMail'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoMailSpoolPath' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '/var/spool/sogo',
            'value' => '',
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
        'SOGoSieveFolderEncoding' => array(
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
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
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
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
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
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
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
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
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
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoForceExternalLoginWithEmail' => array(
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
        'SOGoMailAuxiliaryUserAccountsEnabled' => array(
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
        'SOGoMailCustomFromEnabled' => array(
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
        'SOGoIMAPAclConformsToIMAPExt' => array(
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
        'SOGoCalendarDefaultReminder' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => '-PT5M',
            'value' => array(
                '-PT5M' => $app->lng('5_MINUTES_BEFORE'),
                '-PT10M' => $app->lng('10_MINUTES_BEFORE'),
                '-PT15M' => $app->lng('15_MINUTES_BEFORE'),
                '-PT30M' => $app->lng('30_MINUTES_BEFORE'),
                '-PT45M' => $app->lng('45_MINUTES_BEFORE'),
                '-PT1H' => $app->lng('1_HOUR_BEFORE'),
                '-PT2H' => $app->lng('2_HOURS_BEFORE'),
                '-PT5H' => $app->lng('5_HOURS_BEFORE'),
                '-PT15H' => $app->lng('15_HOURS_BEFORE'),
                '-P1D' => $app->lng('1_DAY_BEFORE'),
                '-P2D' => $app->lng('2_DAYS_BEFORE'),
                '-P1W' => $app->lng('1_WEEK_BEFORE'),
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
                'PUBLIC' => $app->lng('Public'),
                'CONFIDENTIAL' => $app->lng('Confidential'),
                'PRIVATE' => $app->lng('Private'),
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
                'PUBLIC' => $app->lng('Public'),
                'CONFIDENTIAL' => $app->lng('Confidential'),
                'PRIVATE' => $app->lng('Private'),
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
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
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
                0 => $app->lng('Sunday'),
                1 => $app->lng('Monday'),
                2 => $app->lng('Tuesday'),
                3 => $app->lng('Wednesday'),
                4 => $app->lng('Thursday'),
                5 => $app->lng('Friday'),
                6 => $app->lng('Saturday'),
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
                'January1' => $app->lng('January 1'),
                'First4DayWeek' => $app->lng('First 4 Day Week'),
                'FirstFullWeek' => $app->lng('First Full Week'),
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
                'Calendar' => $app->lng('Calendar'),
                'Mail' => $app->lng('Mail'),
                'Contacts' => $app->lng('Contacts'),
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
                'text' => $app->lng('Text'),
                'html' => $app->lng('HTML'),
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
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoMailMessageForwarding' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'inline',
            'value' => array(
                'inline' => $app->lng('Inline'),
                ' attached' => $app->lng('Attached'),
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
                'above' => $app->lng('Above'),
                'below' => $app->lng('Below'),
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
                'above' => $app->lng('Above'),
                'below' => $app->lng('Below'),
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
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
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
                'selected' => $app->lng('Selected'),
                'personal' => $app->lng('Personal'),
                'first' => $app->lng('First'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoCustomXML' => array(
            'datatype' => 'TEXT',
            'formtype' => 'TEXTAREA',
            'default' => '',
            'value' => '',
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
            'rows' => 20,
            'cols' => 30,
        ),
    )
);
