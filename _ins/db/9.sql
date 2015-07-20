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
(0, 0, '', '', '', 1, 1, 'client', '*'), (0, 0, '', '', '', 2, 1, 'reseller', '*');
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
(0, 0, 'ru', 'ru', '', 1, 1, 'sieve_filter_forward', 'y'),(0, 0, 'ru', 'ru', '', 2, 1, 'sieve_filter_vacation', 'y'),
(0, 0, 'ru', 'ru', '', 3, 1, 'sieve_server', 'n'),(0, 0, 'ru', 'ru', '', 4, 1, 'sieve_filter_enable_disable', 'n'),
(0, 0, 'ru', 'ru', '', 5, 1, 'imap_folder_drafts', 'y'),(0, 0, 'ru', 'ru', '', 6, 1, 'imap_folder_sent', 'y'),
(0, 0, 'ru', 'ru', '', 7, 1, 'imap_folder_trash', 'y'),(0, 0, 'ru', 'ru', '', 8, 1, 'sieve_folder_encoding', 'n'),
(0, 0, 'ru', 'ru', '', 9, 1, 'imap_server', 'y'),(0, 0, 'ru', 'ru', '', 10, 1, 'imap_acl_style', 'n'),
(0, 0, 'ru', 'ru', '', 11, 1, 'custom_xml', 'n'),(0, 0, 'ru', 'ru', '', 12, 1, 'smtp_authentication_type', 'n'),
(0, 0, 'ru', 'ru', '', 13, 1, 'mail_custom_from_enabled', 'n'),(0, 0, 'ru', 'ru', '', 14, 1, 'mail_spool_path', 'n'),
(0, 0, 'ru', 'ru', '', 15, 1, 'mailing_mechanism', 'n'),(0, 0, 'ru', 'ru', '', 16, 1, 'smtp_server', 'n'),
(0, 0, 'ru', 'ru', '', 17, 1, 'mail_auxiliary_accounts', 'n'),(0, 0, 'ru', 'ru', '', 18, 1, 'imap_conforms_imapext', 'n'),
(0, 0, 'ru', 'ru', '', 19, 1, 'subscription_folder_format', 'n'),(0, 0, 'ru', 'ru', '', 20, 2, 'sieve_filter_forward', 'y'),
(0, 0, 'ru', 'ru', '', 21, 2, 'sieve_filter_vacation', 'y'),(0, 0, 'ru', 'ru', '', 22, 2, 'sieve_filter_enable_disable', 'y'),
(0, 0, 'ru', 'ru', '', 23, 2, 'sieve_folder_encoding', 'y'),(0, 0, 'ru', 'ru', '', 24, 2, 'imap_acl_style', 'y'),
(0, 0, 'ru', 'ru', '', 25, 2, 'imap_conforms_imapext', 'y'),(0, 0, 'ru', 'ru', '', 26, 2, 'imap_folder_drafts', 'y'),
(0, 0, 'ru', 'ru', '', 27, 2, 'imap_folder_sent', 'y'),(0, 0, 'ru', 'ru', '', 28, 2, 'imap_folder_trash', 'y'),
(0, 0, 'ru', 'ru', '', 29, 2, 'mail_custom_from_enabled', 'n'),(0, 0, 'ru', 'ru', '', 30, 2, 'sieve_server', 'n'),
(0, 0, 'ru', 'ru', '', 31, 2, 'imap_server', 'n'),(0, 0, 'ru', 'ru', '', 32, 2, 'subscription_folder_format', 'n'),
(0, 0, 'ru', 'ru', '', 33, 2, 'mail_auxiliary_accounts', 'n'),(0, 0, 'ru', 'ru', '', 34, 2, 'smtp_server', 'n'),
(0, 0, 'ru', 'ru', '', 35, 2, 'mailing_mechanism', 'n'),(0, 0, 'ru', 'ru', '', 36, 2, 'mail_spool_path', 'n'),
(0, 0, 'ru', 'ru', '', 37, 2, 'smtp_authentication_type', 'n'),(0, 0, 'ru', 'ru', '', 38, 2, 'custom_xml', 'n');

-- Change varchar(255) => enum(yes, no)
ALTER TABLE `sogo_domains` CHANGE `SOGoSieveScriptsEnabled` `SOGoSieveScriptsEnabled` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NO';
ALTER TABLE `sogo_domains` CHANGE `SOGoVacationEnabled` `SOGoVacationEnabled` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NO';
ALTER TABLE `sogo_domains` CHANGE `SOGoACLsSendEMailNotifications` `SOGoACLsSendEMailNotifications` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'YES';
ALTER TABLE `sogo_domains` CHANGE `SOGoAppointmentSendEMailNotifications` `SOGoAppointmentSendEMailNotifications` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'YES';
ALTER TABLE `sogo_domains` CHANGE `SOGoFoldersSendEMailNotifications` `SOGoFoldersSendEMailNotifications` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NO';
ALTER TABLE `sogo_domains` CHANGE `SOGoNotifyOnPersonalModifications` `SOGoNotifyOnPersonalModifications` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NO';
ALTER TABLE `sogo_domains` CHANGE `SOGoNotifyOnExternalModifications` `SOGoNotifyOnExternalModifications` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NO';
ALTER TABLE `sogo_domains` CHANGE `SOGoForceExternalLoginWithEmail` `SOGoForceExternalLoginWithEmail` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'YES';
ALTER TABLE `sogo_domains` CHANGE `SOGoMailAuxiliaryUserAccountsEnabled` `SOGoMailAuxiliaryUserAccountsEnabled` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NO';
ALTER TABLE `sogo_domains` CHANGE `SOGoMailCustomFromEnabled` `SOGoMailCustomFromEnabled` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NO';
ALTER TABLE `sogo_domains` CHANGE `SOGoIMAPAclConformsToIMAPExt` `SOGoIMAPAclConformsToIMAPExt` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'YES';
ALTER TABLE `sogo_domains` CHANGE `SOGoCalendarShouldDisplayWeekend` `SOGoCalendarShouldDisplayWeekend` ENUM('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'YES';

-- 'PUBLIC','CONFIDENTIAL','PRIVATE'
-- Change varchar(255) => enum(UTF-7, UTF-8)
ALTER TABLE `sogo_domains` CHANGE `SOGoSieveFolderEncoding` `SOGoSieveFolderEncoding` ENUM('UTF-7','UTF-8') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'UTF-7';

-- Change imap folder names varchar(255) => varchar(50)
ALTER TABLE `sogo_domains` CHANGE `SOGoDraftsFolderName` `SOGoDraftsFolderName` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Drafts';
ALTER TABLE `sogo_domains` CHANGE `SOGoSentFolderName` `SOGoSentFolderName` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Sent';
ALTER TABLE `sogo_domains` CHANGE `SOGoTrashFolderName` `SOGoTrashFolderName` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Trash';

-- Change varchar(255) => enum(smtp, sendmail)
ALTER TABLE `sogo_domains` CHANGE `SOGoMailingMechanism` `SOGoMailingMechanism` ENUM('smtp','sendmail') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'smtp';

-- Change varchar(255) => enum(Calendar, Mail, Contacts)
ALTER TABLE `sogo_domains` CHANGE `SOGoLoginModule` `SOGoLoginModule` ENUM('Calendar','Mail','Contacts') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Mail';

-- Change varchar(255) => enum(...)
ALTER TABLE `sogo_domains` CHANGE `SOGoCalendarDefaultReminder` `SOGoCalendarDefaultReminder` ENUM('-PT5M','-PT10M','-PT15M','-PT30M','-PT45M','-PT1H','-PT2H','-PT5H','-PT15H','-P1D','-P2D','-P1W') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '-PT5M';
ALTER TABLE `sogo_domains` CHANGE `SOGoFirstWeekOfYear` `SOGoFirstWeekOfYear` ENUM('January1','First4DayWeek','FirstFullWeek') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'FirstFullWeek';

-- Change varchar(255) => enum(text, html)
ALTER TABLE `sogo_domains` CHANGE `SOGoMailComposeMessageType` `SOGoMailComposeMessageType` ENUM('text','html') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'text';

UPDATE 
    `sys_config`
SET
    `value` = '9' 
WHERE 
    CONVERT( `sys_config`.`group` USING utf8 ) = 'interface' 
    AND CONVERT( `sys_config`.`name` USING utf8 ) = 'sogo_interface' 
    AND CONVERT( `sys_config`.`value` USING utf8 ) = '8' LIMIT 1;