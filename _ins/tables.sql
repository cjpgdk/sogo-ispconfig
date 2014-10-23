-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny9
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 05. 10 2014 kl. 21:02:52
-- Serverversion: 5.1.58
-- PHP-version: 5.3.10-1~dotdeb.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `dbispconfig`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sogo_config`
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
  `SOGoSieveServer` varchar(255) NOT NULL DEFAULT 'sieve://localhost:4190',
  `SOGoVacationEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoDraftsFolderName` varchar(255) NOT NULL DEFAULT 'Drafts',
  `SOGoSentFolderName` varchar(255) NOT NULL DEFAULT 'Sent',
  `SOGoTrashFolderName` varchar(255) NOT NULL DEFAULT 'Trash',
  `SOGoIMAPServer` varchar(255) NOT NULL DEFAULT 'imaps://127.0.0.1:143/?tls=YES',
  `SOGoSMTPServer` varchar(255) NOT NULL DEFAULT '127.0.0.1',
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
  `SOGoForwardEnabled` varchar(255) NOT NULL DEFAULT 'NO',
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
  `SOGoMailMessageCheck` varchar(255) NOT NULL DEFAULT 'every_minute',
  `SOGoMailMessageForwarding` varchar(255) NOT NULL DEFAULT 'inline',
  `SOGoMailReplyPlacement` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoMailSignaturePlacement` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoTimeFormat` varchar(255) NOT NULL DEFAULT '%H:%M',
  `SOGoMailUseOutlookStyleReplies` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoCalendarDefaultCategoryColor` varchar(255) NOT NULL DEFAULT '#aaa',
  `SOGoDefaultCalendar` varchar(255) NOT NULL DEFAULT 'selected',
  `SOGoCustomXML` text,
  PRIMARY KEY (`sogo_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sogo_domains`
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
  `SOGoSieveServer` varchar(255) NOT NULL DEFAULT 'sieve://localhost:4190',
  `SOGoVacationEnabled` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoDraftsFolderName` varchar(255) NOT NULL DEFAULT 'Drafts',
  `SOGoSentFolderName` varchar(255) NOT NULL DEFAULT 'Sent',
  `SOGoTrashFolderName` varchar(255) NOT NULL DEFAULT 'Trash',
  `SOGoIMAPServer` varchar(255) NOT NULL DEFAULT 'imaps://127.0.0.1:143/?tls=YES',
  `SOGoSMTPServer` varchar(255) NOT NULL DEFAULT '127.0.0.1',
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
  `SOGoSuperUsernames` varchar(255) NOT NULL DEFAULT 'postmaster@${DOMAIN}',
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
  `SOGoMailMessageCheck` varchar(255) NOT NULL DEFAULT 'every_minute',
  `SOGoMailMessageForwarding` varchar(255) NOT NULL DEFAULT 'inline',
  `SOGoMailReplyPlacement` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoMailSignaturePlacement` varchar(255) NOT NULL DEFAULT 'below',
  `SOGoTimeFormat` varchar(255) NOT NULL DEFAULT '%H:%M',
  `SOGoMailUseOutlookStyleReplies` varchar(255) NOT NULL DEFAULT 'NO',
  `SOGoCalendarDefaultCategoryColor` varchar(255) NOT NULL DEFAULT '#aaa',
  `SOGoDefaultCalendar` varchar(255) NOT NULL DEFAULT 'selected',
  `SOGoCustomXML` text,
  PRIMARY KEY (`sogo_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `sogo_module` (
  `smid` int(2) NOT NULL AUTO_INCREMENT,
  `sys_userid` int(11) unsigned NOT NULL,
  `sys_groupid` int(11) unsigned NOT NULL,
  `sys_perm_user` varchar(5) DEFAULT NULL,
  `sys_perm_group` varchar(5) DEFAULT NULL,
  `sys_perm_other` varchar(5) DEFAULT NULL,
  `all_domains` enum('y','n') NOT NULL DEFAULT 'y',
  `allow_same_instance` enum('y','n') NOT NULL DEFAULT 'y',
  `sql_of_mail_server` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`smid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
INSERT INTO `sogo_module` (`smid` ,`sys_userid` ,`sys_groupid` ,`sys_perm_user` ,`sys_perm_group` ,`sys_perm_other` ,`all_domains` ,`allow_same_instance` ,`sql_of_mail_server`) VALUES ('1', '1', '0', 'riu', 'riu', NULL , 'y', 'y', 'n');


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
INSERT INTO `sogo_plugins` (`spid`, `sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other`, `active`, `client_id`, `name`, `description`, `filetype`, `file`) VALUES
(1, 1, 1, 'riud', 'riud', 'r', 'y', 0, 'Mozilla Thunderbird 31 - Lightning - Official release 3.3.1', 'Mozilla Lightning - Official release 3.3.1\r\nis a calendar plugin that woks well with SOGo integrator\r\n\r\nRequirements\r\nSOGo Connector Thunderbird extension, Version 31.0.0\r\nAND\r\nSOGo Integrator Thunderbird extension, Version 31.0.0', 'link', 'https://addons.mozilla.org/en-US/thunderbird/addon/lightning/versions/3.3.1'),
(2, 1, 1, 'riud', 'riud', 'r', 'y', 0, 'Thunderbird 31 extension - SOGo Connector', 'SOGo Connector Thunderbird extension\r\nVersion 31.0.0 (released on September 26th 2014)\r\n\r\nif you are going to use Thunderbird you need this extension along with "SOGo Integrator" to fully integrate with SOGo', 'download', 'sogo-connector-31.0.0.xpi'),
(3, 1, 1, 'riud', 'riud', 'r', 'y', 0, 'Thunderbird 31 - SOGo Integrator', 'SOGo Connector Thunderbird extension\r\nVersion 31.0.0\r\n\r\nif you are going to use Thunderbird you need this extension along with "SOGo Integrator" to fully integrate with SOGo', 'download', 'sogo-integrator-31.0.0-sogo-demo.xpi');


INSERT INTO `sys_config` (`group`, `name`, `value`) VALUES ('interface', 'sogo_interface', '4');