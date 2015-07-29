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

$form["title"] = "Set SOGo configuration";
$form["description"] = "update SOGo configuration on the selected domains";
$form["name"] = "sogo_domains";
$form["action"] = "sogo_module_bulk_update_config.php";
$form["db_table"] = "sogo_domains";
$form["db_table_idx"] = "sogo_id";
$form["db_history"] = "yes";
$form["tab_default"] = "domain";
$form["list_default"] = "sogo_module_settings_list.php";
$form["auth"] = 'yes'; // yes / no
$form["auth_preset"]["userid"] = 0; // 0 = id of the user, > 0 id must match with id of current user
$form["auth_preset"]["groupid"] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form["auth_preset"]["perm_user"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_group"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_other"] = ''; //r = read, i = insert, u = update, d = delete

$form["tabs"]['domain'] = array(
    'title' => "SOGo Domain Defaults",
    'width' => 70,
    'template' => "templates/sogo_domains_bulk_edit.htm",
    'fields' => array(
        'SOGoSieveScriptsEnabled' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'NO',
            'value' => array(
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoCalendarDefaultRoles' => array(
            'value' => array(
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
                'NOCHANGE' => $app->lng('No change'),
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
        'SOGoIMAPAclConformsToIMAPExt' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NOCHANGE' => $app->lng('No change'),
                'NO' => $app->lng('No'),
                'YES' => $app->lng('Yes'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoIMAPAclStyle' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'rfc4314',
            'value' => array(
                'NOCHANGE' => $app->lng('No change'),
                'rfc2086' => 'RFC 2086', 'rfc4314' => 'RFC 4314',
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
        'SOGoSMTPAuthenticationType' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'SELECT',
            'default' => 'YES',
            'value' => array(
                'NOCHANGE' => $app->lng('No change'),
                'NO' => $app->lng('No'),
                'PLAIN' => $app->lng('Plain'),
            ),
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        ),
    )
);
