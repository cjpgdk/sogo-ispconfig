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
);

/*
 * Form fields are all customized by class sogo_config
 * they are listed here to define what fields are editable to what type of user 
 * 
 * NOTE* 
 * all settings listed in the officiel SOGo documentaion for Domain and users are listed here
 * Server settings CAN NOT be set per. domain only server
 * 
 * The following settings are not added
 * i don't see a reason for them, and most of them can be set by each user in SOGo interface
 *  
 * SOGoTimeZone
 * SOGoiPhoneForceAllDayTransparency
 * SOGoDayStartTime
 * SOGoDayEndTime
 * SOGoFirstDayOfWeek
 * SOGoFirstWeekOfYear
 * SOGoTimeFormat
 * SOGoCalendarCategories
 * SOGoCalendarDefaultCategoryColor
 * SOGoCalendarEventsDefaultClassification
 * SOGoCalendarTasksDefaultClassification
 * SOGoCalendarDefaultReminder
 * SOGoFreeBusyDefaultInterval
 * SOGoBusyOffHours
 * SOGoMailMessageForwarding
 * SOGoMailCustomFullName
 * SOGoMailCustomEmail
 * SOGoMailReplyPlacement
 * SOGoMailReplyTo
 * SOGoMailSignaturePlacement
 * SOGoMailComposeMessageType
 * SOGoContactsCategories
 * SOGoUIAdditionalJSFiles
 * SOGoSubscriptionFolderFormat
 * SOGoUIxAdditionalPreferences
 * 
 * 
 * 
 * SOGoLDAPQueryTimeout
 * SOGoLDAPContactInfoAttribute
 * SOGoLDAPContactInfoAttribute
 * SOGoLDAPQueryLimit
 * 
 * SOGoHideSystemEMail
 * 
 * ======== IF you like to use difrent database on some domains
 * SOGoProfileURL
 * OCSFolderInfoURL
 * OCSSessionsFolderURL
 * OCSEMailAlarmsFolderURL
 * ============================
 * 
 * SOGoSMTPAuthenticationType ---- Activate SMTP authentication and specifies which type is in use. Current, only ?PLAIN? is supported and other values will be ignored.
 * 
 * SOGoIMAPCASServiceName
 * NGImap4ConnectionGroupIdPrefix
 * SOGoMailPollingIntervals
 */

//* GLOBAL SETTINGS Allow edit by all
$form["tabs"]['sogo_domain']['fields'] = array(
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
    'SOGoSoftQuotaRatio' => array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => '0.9',
        'value' => '',
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
    'SOGoSearchMinimumWordLength' => array(
        'datatype' => 'INTEGER',
        'formtype' => 'TEXT',
        'default' => 3,
        'value' => 3,
    ),
    'SOGoNotifyOnPersonalModifications' => array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'NO',
        'value' => array(
            'NO' => 'NO', 'YES' => 'YES'
        ),
    ),
    'SOGoNotifyOnExternalModifications' => array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'NO',
        'value' => array(
            'NO' => 'NO', 'YES' => 'YES'
        ),
    ),
    'SOGoACLsSendEMailNotifications' => array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'NO',
        'value' => array(
            'NO' => 'NO', 'YES' => 'YES'
        ),
    ),
    'SOGoFoldersSendEMailNotifications' => array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'NO',
        'value' => array(
            'NO' => 'NO', 'YES' => 'YES'
        ),
    ),
    'SOGoAppointmentSendEMailNotifications' => array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'NO',
        'value' => array(
            'NO' => 'NO', 'YES' => 'YES'
        ),
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
            'English' => 'English'
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
);
//* SETTINGS Allow edit by admins and resellers
if ($app->auth->is_admin() || $app->auth->has_clients($app->auth->get_user_id())) {

    //* @todo limit this option, is reseller allowed Sieve Scripts
    $form["tabs"]['sogo_domain']['fields']['SOGoSieveScriptsEnabled'] = array(
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
    );
    //* @todo limit this option, is reseller allowed Mail Forward
    $form["tabs"]['sogo_domain']['fields']['SOGoForwardEnabled'] = array(
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
    );
    //* @todo limit this option, is reseller allowed Sieve Scripts
    $form["tabs"]['sogo_domain']['fields']['SOGoVacationEnabled'] = array(
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
    );
}
//* SETTINGS Allow edit by admins only
if ($app->auth->is_admin()) {

    $form["tabs"]['sogo_domain']['fields']['SOGoMailCustomFromEnabled'] = array(
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
    );
    /*
     * Allow user to add imap mail accounts just like a normal mail client, 
     * THE MAIL ACCOUNTS WILL USE YOUR SMTP SERVER.!
     */
    $form["tabs"]['sogo_domain']['fields']['SOGoMailAuxiliaryUserAccountsEnabled'] = array(
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
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoMailSpoolPath'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => '/var/spool/sogo',
        'value' => '/var/spool/sogo',
        'maxlength' => '255',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoIMAPAclConformsToIMAPExt'] = array(
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
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoIMAPAclStyle'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'rfc4314',
        'value' => array(
            'rfc2086' => 'rfc4314', 'rfc4314' => 'rfc4314',
        ),
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoSieveFolderEncoding'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'UTF-7',
        'value' => array(
            'UTF-7' => 'UTF-7', 'UTF-8' => 'UTF-8'
        ),
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoSieveServer'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => 'sieve://localhost:4190',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoIMAPServer'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => 'imaps://127.0.0.1:143/?tls=YES',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoForceExternalLoginWithEmail'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'SELECT',
        'default' => 'YES',
        'value' => array(
            'NO' => 'NO', 'YES' => 'YES'
        ),
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoSMTPServer'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => '127.0.0.1',
        'value' => '',
        'maxlength' => '',
        'required' => 0,
        'width' => 100,
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoMailDomain'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => '{{DOMAIN}}',
        'value' => '{{DOMAIN}}',
        'width' => '30',
        'maxlength' => '255',
        'searchable' => 1
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoDraftsFolderName'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => '',
        'value' => '',
        'width' => '30',
        'maxlength' => '255',
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoSentFolderName'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => '',
        'value' => '',
        'width' => '30',
        'maxlength' => '255',
    );
    $form["tabs"]['sogo_domain']['fields']['SOGoTrashFolderName'] = array(
        'datatype' => 'VARCHAR',
        'formtype' => 'TEXT',
        'default' => '',
        'value' => '',
        'width' => '30',
        'maxlength' => '255',
    );

    $form["tabs"]['sogo_domain']['fields']['SOGoMailingMechanism'] = array(
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
    );
}
