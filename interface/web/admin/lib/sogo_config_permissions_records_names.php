<?php

/*
 * Copyright (C) 2015  Christian M. Jensen
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
 * @author Christian M. Jensen <christian@cmjscripter.net>
 * @copyright 2015 Christian M. Jensen
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

/*
    Creating new permission types is as simple as adding them here.
    eg.
        create a record to control if clients can edit the search minimum word length of the domain defaults.
        
        //* control SOGoSearchMinimumWordLength
        $sogo_prn->search_minimum_word_length = "Search Minimum Word Length";
        $sogo_prn->help_description["search_minimum_word_length"] = "Allow or deny a client to set this value from the control panel";
        
        Thats it the new permission record will show up in the permission table
        
        of course, you still need to add the code to restrict this parameter in the template files
         
 */


//* a list of named permissions, used to automatic list the available rules when creating permissions records for clients/resellers
$sogo_prn = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);



//* Sieve filter rules
//
//* Sieve: Forward
$sogo_prn->sieve_filter_forward = $wb["sieve_filter_forward_txt"];
$sogo_prn->help_description["sieve_filter_forward"] = $wb["sieve_filter_forward_desc_txt"];
//* Sieve: Vacation
$sogo_prn->sieve_filter_vacation = $wb["sieve_filter_vacation_txt"];
$sogo_prn->help_description["sieve_filter_vacation"] = $wb["sieve_filter_vacation_desc_txt"];
//* Sieve: Enabled/Disabled
$sogo_prn->sieve_filter_enable_disable = $wb["sieve_filter_enable_disable_txt"];
$sogo_prn->help_description["sieve_filter_enable_disable"] = $wb["sieve_filter_enable_disable_desc_txt"];
//* Sieve: Folder Encoding
$sogo_prn->sieve_folder_encoding = $wb["sieve_folder_encoding_txt"];
$sogo_prn->help_description["sieve_folder_encoding"] = $wb["sieve_folder_encoding_desc_txt"];
//* Sieve: Server
$sogo_prn->sieve_server = $wb["sieve_server_txt"];
$sogo_prn->help_description["sieve_server"] = $wb["sieve_server_desc_txt"];

//* IMAP Server rules
//
//* IMAP: Server
$sogo_prn->imap_server = $wb["imap_server_txt"];
$sogo_prn->help_description["imap_server"] = $wb["imap_server_desc_txt"];
//* IMAP: Acl Style
$sogo_prn->imap_acl_style = $wb["imap_acl_style_txt"];
$sogo_prn->help_description["imap_acl_style"] = $wb["imap_acl_style_desc_txt"];
//* IMAP: Acl Conforms To IMAPExt
$sogo_prn->imap_conforms_imapext = $wb["imap_conforms_imapext_txt"];
$sogo_prn->help_description["imap_conforms_imapext"] = $wb["imap_conforms_imapext_desc_txt"];
//* IMAP: Drafts Folder Name
$sogo_prn->imap_folder_drafts = $wb["imap_folder_drafts_txt"];
$sogo_prn->help_description["imap_folder_drafts"] = $wb["imap_folder_drafts_desc_txt"];
//* IMAP: Sent Folder Name
$sogo_prn->imap_folder_sent = $wb["imap_folder_sent_txt"];
$sogo_prn->help_description["imap_folder_sent"] = $wb["imap_folder_sent_desc_txt"];
//* IMAP: Trash Folder Name
$sogo_prn->imap_folder_trash = $wb["imap_folder_trash_txt"];
$sogo_prn->help_description["imap_folder_trash"] = $wb["imap_folder_trash_desc_txt"];
//* Subscription Folder Format
$sogo_prn->subscription_folder_format = $wb["subscription_folder_format_txt"];
$sogo_prn->help_description["subscription_folder_format"] = $wb["subscription_folder_format_desc_txt"];
//* Mail: Auxiliary User Accounts Enabled 
$sogo_prn->mail_auxiliary_accounts = $wb["mail_auxiliary_accounts_txt"];
$sogo_prn->help_description["mail_auxiliary_accounts"] = $wb["mail_auxiliary_accounts_desc_txt"];

//* SMTP Server rules
//
//* SMTP: Server
$sogo_prn->smtp_server = $wb["smtp_server_txt"];
$sogo_prn->help_description["smtp_server"] = $wb["smtp_server_desc_txt"];
//* Mailing Mechanism
$sogo_prn->mailing_mechanism = $wb["mailing_mechanism_txt"];
$sogo_prn->help_description["mailing_mechanism"] = $wb["mailing_mechanism_desc_txt"];
//* Mail: Spool Path
$sogo_prn->mail_spool_path = $wb["mail_spool_path_txt"];
$sogo_prn->help_description["mail_spool_path"] = $wb["mail_spool_path_desc_txt"];
//* Mail: Custom From Enabled
$sogo_prn->mail_custom_from_enabled = $wb["mail_custom_from_enabled_txt"];
$sogo_prn->help_description["mail_custom_from_enabled"] = $wb["mail_custom_from_enabled_desc_txt"];
//* SMTP: Authentication Type 
$sogo_prn->smtp_authentication_type = $wb["smtp_authentication_type_txt"];
$sogo_prn->help_description["smtp_authentication_type"] = $wb["smtp_authentication_type_desc_txt"];

//* Misc
//
//* Custom XML, (sensitive should not be enabled to users OR resellers)
$sogo_prn->custom_xml = $wb["custom_xml_txt"];
$sogo_prn->help_description["custom_xml"] = $wb["custom_xml_desc_txt"];


//* need this named like this
$sogo_config_permissions_records_names = $sogo_prn;
unset($sogo_prn);