SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
--
-- Table structure for table `sogo_config`
--
CREATE TABLE IF NOT EXISTS `sogo_config` (
  `sogo_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sys_userid` int(11) NOT NULL DEFAULT '0',
  `sys_groupid` int(11) NOT NULL DEFAULT '0',
  `sys_perm_user` varchar(5) DEFAULT NULL,
  `sys_perm_group` varchar(5) DEFAULT NULL,
  `sys_perm_other` varchar(5) DEFAULT NULL,
  `server_id` varchar(255) DEFAULT NULL,
  `server_name` varchar(255) DEFAULT NULL,
  `SOGoPageTitle` varchar(255) NOT NULL DEFAULT 'ISPConfig 3 w/SOGo',
  `SOGoMemcachedHost` varchar(255) NOT NULL DEFAULT '127.0.0.1',
  `SOGoZipPath` varchar(255) NOT NULL DEFAULT '/usr/bin/zip',
  `SOGoSoftQuotaRatio` varchar(255) NOT NULL DEFAULT '0.9',
  `SOGoAddressBookDAVAccessEnabled` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoCalendarDAVAccessEnabled` varchar(255) NOT NULL DEFAULT 'YES',
  `NGImap4ConnectionStringSeparator` varchar(255) NOT NULL DEFAULT '.',
  `SOGoEnableEMailAlarms` varchar(255) NOT NULL DEFAULT 'NO',
  `OCSEMailAlarmsFolderURL` varchar(255) NOT NULL DEFAULT 'mysql://{SOGOUSERN}:{SOGOUSERPW}@{MYSQLHOST}:{MYSQLPORT}/{SOGODB}/sogo_mailalarms_folder',
  `OCSFolderInfoURL` varchar(255) NOT NULL DEFAULT 'mysql://{SOGOUSERN}:{SOGOUSERPW}@{MYSQLHOST}:{MYSQLPORT}/{SOGODB}/sogo_folder_info',
  `OCSSessionsFolderURL` varchar(255) NOT NULL DEFAULT 'mysql://{SOGOUSERN}:{SOGOUSERPW}@{MYSQLHOST}:{MYSQLPORT}/{SOGODB}/sogo_sessions_folder',
  `SOGoProfileURL` varchar(255) NOT NULL DEFAULT 'mysql://{SOGOUSERN}:{SOGOUSERPW}@{MYSQLHOST}:{MYSQLPORT}/{SOGODB}/sogo_user_profile',
  `SOGoAppointmentSendEMailReceipts` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoAuthenticationMethod` varchar(255) NOT NULL DEFAULT 'SQL',
  `SOGoPasswordChangeEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SxVMemLimit` int(11) NOT NULL DEFAULT '384',
  `WOWorkersCount` int(11) NOT NULL DEFAULT '1',
  `WOListenQueueSize` int(11) NOT NULL DEFAULT '5',
  `WOWatchDogRequestTimeout` int(11) NOT NULL DEFAULT '10',
  `WOWorkerThreadCount` int(11) NOT NULL DEFAULT '0',
  `WOUseRelativeURLs` varchar(255) NOT NULL DEFAULT 'YES',
  `WOLogFile` varchar(255) NOT NULL DEFAULT '/var/log/sogo/sogo.log',
  `WOPidFile` varchar(255) NOT NULL DEFAULT '/var/run/sogo/sogo.pid',
  `WOPort` varchar(255) NOT NULL DEFAULT '127.0.0.1:20000',
  `WOSendMail` varchar(255) NOT NULL DEFAULT '/usr/lib/sendmail',
  `SOGoSieveScriptsEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoSieveServer` varchar(255) NOT NULL DEFAULT 'sieve://{SERVERNAME}:4190',
  `SOGoVacationEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoDraftsFolderName` varchar(255) NOT NULL DEFAULT 'Drafts',
  `SOGoSentFolderName` varchar(255) NOT NULL DEFAULT 'Sent',
  `SOGoTrashFolderName` varchar(255) NOT NULL DEFAULT 'Trash',
  `SOGoIMAPServer` varchar(255) NOT NULL DEFAULT 'imaps://{SERVERNAME}:143/?tls=YES',
  `SOGoSMTPServer` varchar(255) NOT NULL DEFAULT '{SERVERNAME}',
  `SOGoMailingMechanism` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoMailSpoolPath` varchar(255) NOT NULL DEFAULT '/var/spool/sogo',
  `SOGoSearchMinimumWordLength` int(11) NOT NULL DEFAULT '2',
  `SOGoSieveFolderEncoding` varchar(255) NOT NULL DEFAULT 'UTF-7',
  `SOGoSubscriptionFolderFormat` varchar(255) NOT NULL DEFAULT '%{FolderName} (%{UserName} <%{Email}>)',
  `SOGoTimeZone` varchar(255) NOT NULL DEFAULT 'Europe/Berlin',
  `SOGoACLsSendEMailNotifications` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoAppointmentSendEMailNotifications` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoFoldersSendEMailNotifications` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoNotifyOnPersonalModifications` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoNotifyOnExternalModifications` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoForceExternalLoginWithEmail` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoMailAuxiliaryUserAccountsEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoMailCustomFromEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoCalendarDefaultRoles` varchar(255) NOT NULL DEFAULT 'PublicViewer,ConfidentialDAndTViewer',
  `SOGoContactsDefaultRoles` varchar(255) NOT NULL DEFAULT 'ObjectEditor',
  `SOGoForwardEnabled` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `SOGoIMAPAclConformsToIMAPExt` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoCalendarDefaultReminder` varchar(255) NOT NULL DEFAULT '-PT5M',
  `SOGoCalendarEventsDefaultClassification` varchar(255) NOT NULL DEFAULT 'PUBLIC',
  `SOGoCalendarTasksDefaultClassification` varchar(255) NOT NULL DEFAULT 'PUBLIC',
  `SOGoCalendarShouldDisplayWeekend` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoDayStartTime` int(11) NOT NULL DEFAULT '8',
  `SOGoDayEndTime` int(11) NOT NULL DEFAULT '18',
  `SOGoFirstDayOfWeek` int(11) NOT NULL DEFAULT '1',
  `SOGoFirstWeekOfYear` varchar(255) NOT NULL DEFAULT 'FirstFullWeek',
  `SOGoLanguage` varchar(255) NOT NULL DEFAULT 'English',
  `SOGoLoginModule` varchar(255) NOT NULL DEFAULT 'Mail',
  `SOGoMailComposeMessageType` varchar(255) NOT NULL DEFAULT 'text',
  `SOGoMailListViewColumnsOrder` varchar(255) NOT NULL DEFAULT 'Flagged,Attachment,Priority,From,Subject,Unread,Date,Size',
  `SOGoRefreshViewCheck` varchar(75) NOT NULL DEFAULT 'every_minute',
  `SOGoMailMessageForwarding` varchar(255) NOT NULL DEFAULT 'inline',
  `SOGoMailReplyPlacement` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoMailSignaturePlacement` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoTimeFormat` varchar(255) NOT NULL DEFAULT '%H:%M',
  `SOGoMailUseOutlookStyleReplies` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoCalendarDefaultCategoryColor` varchar(255) NOT NULL DEFAULT '#aaa',
  `SOGoDefaultCalendar` varchar(255) NOT NULL DEFAULT 'selected',
  `SOGoCustomXML` text,
  `SOGoIMAPAclStyle` enum('rfc2086','rfc4314') NOT NULL DEFAULT 'rfc4314',
  `SOGoMaximumSyncWindowSize` int(11) NOT NULL DEFAULT '0',
  `SOGoMaximumPingInterval` int(11) NOT NULL DEFAULT '10',
  `SOGoMaximumSyncInterval` int(11) NOT NULL DEFAULT '30',
  `SOGoInternalSyncInterval` int(11) NOT NULL DEFAULT '10',
  `SOGoMailShowSubscribedFoldersOnly` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `SOGoSMTPAuthenticationType` enum('PLAIN','NO') NOT NULL DEFAULT 'PLAIN',
  PRIMARY KEY (`sogo_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `sogo_domains`
--
CREATE TABLE IF NOT EXISTS `sogo_domains` (
  `sogo_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sys_userid` int(11) NOT NULL DEFAULT '0',
  `sys_groupid` int(11) NOT NULL DEFAULT '0',
  `sys_perm_user` varchar(5) DEFAULT NULL,
  `sys_perm_group` varchar(5) DEFAULT NULL,
  `sys_perm_other` varchar(5) DEFAULT NULL,
  `domain_id` int(11) NOT NULL DEFAULT '0',
  `domain_name` varchar(255) DEFAULT NULL,
  `server_id` int(11) NOT NULL DEFAULT '0',
  `server_name` varchar(255) DEFAULT NULL,
  `SOGoSieveScriptsEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoSieveServer` varchar(255) NOT NULL DEFAULT 'sieve://{SERVERNAME}:4190',
  `SOGoVacationEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoDraftsFolderName` varchar(255) NOT NULL DEFAULT 'Drafts',
  `SOGoSentFolderName` varchar(255) NOT NULL DEFAULT 'Sent',
  `SOGoTrashFolderName` varchar(255) NOT NULL DEFAULT 'Trash',
  `SOGoIMAPServer` varchar(255) NOT NULL DEFAULT 'imaps://{SERVERNAME}:143/?tls=YES',
  `SOGoSMTPServer` varchar(255) NOT NULL DEFAULT '{SERVERNAME}',
  `SOGoMailingMechanism` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoMailSpoolPath` varchar(255) NOT NULL DEFAULT '/var/spool/sogo',
  `SOGoSearchMinimumWordLength` int(11) NOT NULL DEFAULT '2',
  `SOGoSieveFolderEncoding` varchar(255) NOT NULL DEFAULT 'UTF-7',
  `SOGoSubscriptionFolderFormat` varchar(255) NOT NULL DEFAULT '%{FolderName} (%{UserName} <%{Email}>)',
  `SOGoTimeZone` varchar(255) NOT NULL DEFAULT 'Europe/Berlin',
  `SOGoACLsSendEMailNotifications` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoAppointmentSendEMailNotifications` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoFoldersSendEMailNotifications` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoNotifyOnPersonalModifications` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoNotifyOnExternalModifications` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoForceExternalLoginWithEmail` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoMailAuxiliaryUserAccountsEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoMailCustomFromEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoCalendarDefaultRoles` varchar(255) NOT NULL DEFAULT 'PublicViewer,ConfidentialDAndTViewer',
  `SOGoContactsDefaultRoles` varchar(255) NOT NULL DEFAULT 'ObjectEditor',
  `SOGoSuperUsernames` varchar(255) NOT NULL DEFAULT 'postmaster@{domain}',
  `SOGoIMAPAclConformsToIMAPExt` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoCalendarDefaultReminder` varchar(255) NOT NULL DEFAULT '-PT5M',
  `SOGoCalendarEventsDefaultClassification` varchar(255) NOT NULL DEFAULT 'PUBLIC',
  `SOGoCalendarTasksDefaultClassification` varchar(255) NOT NULL DEFAULT 'PUBLIC',
  `SOGoCalendarShouldDisplayWeekend` varchar(255) NOT NULL DEFAULT 'YES',
  `SOGoDayStartTime` int(11) NOT NULL DEFAULT '8',
  `SOGoDayEndTime` int(11) NOT NULL DEFAULT '18',
  `SOGoFirstDayOfWeek` int(11) NOT NULL DEFAULT '1',
  `SOGoFirstWeekOfYear` varchar(255) NOT NULL DEFAULT 'FirstFullWeek',
  `SOGoLanguage` varchar(255) NOT NULL DEFAULT 'English',
  `SOGoLoginModule` varchar(255) NOT NULL DEFAULT 'Mail',
  `SOGoMailComposeMessageType` varchar(255) NOT NULL DEFAULT 'text',
  `SOGoMailListViewColumnsOrder` varchar(255) NOT NULL DEFAULT 'Flagged,Attachment,Priority,From,Subject,Unread,Date,Size',
  `SOGoRefreshViewCheck` varchar(75) NOT NULL DEFAULT 'every_minute',
  `SOGoMailMessageForwarding` varchar(255) NOT NULL DEFAULT 'inline',
  `SOGoMailReplyPlacement` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoMailSignaturePlacement` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoTimeFormat` varchar(255) NOT NULL DEFAULT '%H:%M',
  `SOGoMailUseOutlookStyleReplies` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoCalendarDefaultCategoryColor` varchar(255) NOT NULL DEFAULT '#aaa',
  `SOGoDefaultCalendar` varchar(255) NOT NULL DEFAULT 'selected',
  `SOGoCustomXML` text,
  `SOGoIMAPAclStyle` enum('rfc2086','rfc4314') NOT NULL DEFAULT 'rfc4314',
  `SOGoForwardEnabled` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `SOGoMailShowSubscribedFoldersOnly` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `SOGoSMTPAuthenticationType` enum('PLAIN','NO') NOT NULL DEFAULT 'PLAIN',
  PRIMARY KEY (`sogo_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `sogo_module`
--
CREATE TABLE IF NOT EXISTS `sogo_module` (
  `smid` int(2) NOT NULL AUTO_INCREMENT,
  `sys_userid` int(11) unsigned NOT NULL,
  `sys_groupid` int(11) unsigned NOT NULL,
  `sys_perm_user` varchar(5) DEFAULT NULL,
  `sys_perm_group` varchar(5) DEFAULT NULL,
  `sys_perm_other` varchar(5) DEFAULT NULL,
  `server_id` int(11) NOT NULL,
  `all_domains` enum('y','n') NOT NULL DEFAULT 'y',
  `allow_same_instance` enum('y','n') NOT NULL DEFAULT 'y',
  `config_rebuild_on_mail_user_insert` enum('n','y') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`smid`),
  UNIQUE KEY `server_id` (`server_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Table structure for table `sogo_plugins`
--
CREATE TABLE IF NOT EXISTS `sogo_plugins` (
  `spid` bigint(20) NOT NULL AUTO_INCREMENT,
  `sys_userid` int(11) NOT NULL DEFAULT '0',
  `sys_groupid` int(11) NOT NULL DEFAULT '0',
  `sys_perm_user` varchar(5) NOT NULL DEFAULT 'NULL',
  `sys_perm_group` varchar(5) NOT NULL DEFAULT 'NULL',
  `sys_perm_other` varchar(5) NOT NULL DEFAULT 'NULL',
  `active` varchar(255) NOT NULL DEFAULT 'y',
  `client_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT 'Plugin Name',
  `description` text,
  `filetype` enum('download','link') NOT NULL DEFAULT 'download',
  `file` text,
  PRIMARY KEY (`spid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 -- some default sample values
-- INSERT INTO `sogo_plugins` (`spid`, `sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other`, `active`, `client_id`, `name`, `description`, `filetype`, `file`) VALUES
-- (1, 1, 1, 'riud', 'riud', 'r', 'y', 0, 'Mozilla Thunderbird 31 - Lightning - Official release 3.3.1', 'Mozilla Lightning - Official release 3.3.1\r\nis a calendar plugin that woks well with SOGo integrator\r\n\r\nRequirements\r\nSOGo Connector Thunderbird extension, Version 31.0.0\r\nAND\r\nSOGo Integrator Thunderbird extension, Version 31.0.0', 'link', 'https://addons.mozilla.org/en-US/thunderbird/addon/lightning/versions/3.3.1'),
-- (2, 1, 1, 'riud', 'riud', 'r', 'y', 0, 'Thunderbird 31 extension - SOGo Connector', 'SOGo Connector Thunderbird extension\r\nVersion 31.0.0 (released on September 26th 2014)\r\n\r\nif you are going to use Thunderbird you need this extension along with "SOGo Integrator" to fully integrate with SOGo', 'download', 'sogo-connector-31.0.0.xpi'),
-- (3, 1, 1, 'riud', 'riud', 'r', 'y', 0, 'Thunderbird 31 - SOGo Integrator', 'SOGo Connector Thunderbird extension\r\nVersion 31.0.0\r\n\r\nif you are going to use Thunderbird you need this extension along with "SOGo Integrator" to fully integrate with SOGo', 'download', 'sogo-integrator-31.0.0-sogo-demo.xpi');

--
-- Table structure for table `sogo_config_permissions_index`
--
CREATE TABLE IF NOT EXISTS `sogo_config_permissions_index` (
  `sys_userid` int(11) NOT NULL,
  `sys_groupid` int(11) NOT NULL,
  `sys_perm_user` varchar(5) NOT NULL,
  `sys_perm_group` varchar(5) NOT NULL,
  `sys_perm_other` varchar(5) NOT NULL,
  `scpi` int(11) NOT NULL,
  `scpi_is_global` tinyint(1) NOT NULL DEFAULT '0',
  `scpi_type` enum('client','reseller') NOT NULL DEFAULT 'client',
  `scpi_clients` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
--
-- Global permissions
--
INSERT INTO `sogo_config_permissions_index` (`sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other`, `scpi`, `scpi_is_global`, `scpi_type`, `scpi_clients`) VALUES
(0, 0, '', '', '', 1, 1, 'client', '*'),
(0, 0, '', '', '', 2, 1, 'reseller', '*');
--
-- Indexes for table `sogo_config_permissions_index`
--
ALTER TABLE `sogo_config_permissions_index`
 ADD PRIMARY KEY (`scpi`), ADD UNIQUE KEY `scpi` (`scpi`);
--
-- AUTO_INCREMENT for table `sogo_config_permissions_index`
--
ALTER TABLE `sogo_config_permissions_index`
 MODIFY `scpi` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;

--
-- Table structure for table `sogo_config_permissions`
--
CREATE TABLE IF NOT EXISTS `sogo_config_permissions` (
  `sys_userid` int(11) NOT NULL,
  `sys_groupid` int(11) NOT NULL,
  `sys_perm_user` varchar(5) NOT NULL,
  `sys_perm_group` varchar(5) NOT NULL,
  `sys_perm_other` varchar(5) NOT NULL,
  `scp` int(11) NOT NULL,
  `scp_index` int(11) NOT NULL,
  `scp_name` varchar(50) NOT NULL,
  `scp_allow` enum('y','n') NOT NULL DEFAULT 'n'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--
-- Indexes for table `sogo_config_permissions`
--
ALTER TABLE `sogo_config_permissions`
 ADD PRIMARY KEY (`scp`), ADD UNIQUE KEY `scp` (`scp`);
--
-- AUTO_INCREMENT for table `sogo_config_permissions`
--
ALTER TABLE `sogo_config_permissions`
MODIFY `scp` int(11) NOT NULL AUTO_INCREMENT;
--
--  Default records
--
INSERT INTO `sogo_config_permissions` (`sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other`, `scp`, `scp_index`, `scp_name`, `scp_allow`) VALUES
(0, 0, 'ru', 'ru', '', 1, 1, 'sieve_filter_forward', 'y'),
(0, 0, 'ru', 'ru', '', 2, 1, 'sieve_filter_vacation', 'y'),
(0, 0, 'ru', 'ru', '', 3, 1, 'sieve_server', 'n'),
(0, 0, 'ru', 'ru', '', 4, 1, 'sieve_filter_enable_disable', 'n'),
(0, 0, 'ru', 'ru', '', 5, 1, 'imap_folder_drafts', 'y'),
(0, 0, 'ru', 'ru', '', 6, 1, 'imap_folder_sent', 'y'),
(0, 0, 'ru', 'ru', '', 7, 1, 'imap_folder_trash', 'y'),
(0, 0, 'ru', 'ru', '', 8, 1, 'sieve_folder_encoding', 'n'),
(0, 0, 'ru', 'ru', '', 9, 1, 'imap_server', 'y'),
(0, 0, 'ru', 'ru', '', 10, 1, 'imap_acl_style', 'n'),
(0, 0, 'ru', 'ru', '', 11, 1, 'custom_xml', 'n'),
(0, 0, 'ru', 'ru', '', 12, 1, 'smtp_authentication_type', 'n'),
(0, 0, 'ru', 'ru', '', 13, 1, 'mail_custom_from_enabled', 'n'),
(0, 0, 'ru', 'ru', '', 14, 1, 'mail_spool_path', 'n'),
(0, 0, 'ru', 'ru', '', 15, 1, 'mailing_mechanism', 'n'),
(0, 0, 'ru', 'ru', '', 16, 1, 'smtp_server', 'n'),
(0, 0, 'ru', 'ru', '', 17, 1, 'mail_auxiliary_accounts', 'n'),
(0, 0, 'ru', 'ru', '', 18, 1, 'imap_conforms_imapext', 'n'),
(0, 0, 'ru', 'ru', '', 19, 1, 'subscription_folder_format', 'n'),
(0, 0, 'ru', 'ru', '', 20, 2, 'sieve_filter_forward', 'y'),
(0, 0, 'ru', 'ru', '', 21, 2, 'sieve_filter_vacation', 'y'),
(0, 0, 'ru', 'ru', '', 22, 2, 'sieve_filter_enable_disable', 'y'),
(0, 0, 'ru', 'ru', '', 23, 2, 'sieve_folder_encoding', 'y'),
(0, 0, 'ru', 'ru', '', 24, 2, 'imap_acl_style', 'y'),
(0, 0, 'ru', 'ru', '', 25, 2, 'imap_conforms_imapext', 'y'),
(0, 0, 'ru', 'ru', '', 26, 2, 'imap_folder_drafts', 'y'),
(0, 0, 'ru', 'ru', '', 27, 2, 'imap_folder_sent', 'y'),
(0, 0, 'ru', 'ru', '', 28, 2, 'imap_folder_trash', 'y'),
(0, 0, 'ru', 'ru', '', 29, 2, 'mail_custom_from_enabled', 'n'),
(0, 0, 'ru', 'ru', '', 30, 2, 'sieve_server', 'n'),
(0, 0, 'ru', 'ru', '', 31, 2, 'imap_server', 'n'),
(0, 0, 'ru', 'ru', '', 32, 2, 'subscription_folder_format', 'n'),
(0, 0, 'ru', 'ru', '', 33, 2, 'mail_auxiliary_accounts', 'n'),
(0, 0, 'ru', 'ru', '', 34, 2, 'smtp_server', 'n'),
(0, 0, 'ru', 'ru', '', 35, 2, 'mailing_mechanism', 'n'),
(0, 0, 'ru', 'ru', '', 36, 2, 'mail_spool_path', 'n'),
(0, 0, 'ru', 'ru', '', 37, 2, 'smtp_authentication_type', 'n'),
(0, 0, 'ru', 'ru', '', 38, 2, 'custom_xml', 'n');

INSERT INTO `sys_config` (`group`, `name`, `value`) VALUES ('interface', 'sogo_interface', '9');