#!/bin/sh

# 
# Install SOGo on debian ISPConfig 3 server
#
# Update 3
#   - updated plugin to remove user data and settings from sogodb upon delete of mail user
#   - fixed plugin ''create sogo view''  now get the correct imap host if multi hosts exists...
#   - Enabled EMail Alarms in SOGo
#   - allow a per. domain config from config template ''ispc_path/server/conf/sogo_domains/..''
#   - added more option during the install
#       set passwd for sogo db user (leave emtpy to generate one.)
#   - set permissins on the new files to ispconfig user
#
# Update 2
#   - added more option during the install
#       Select imap user Password Algorithm [Default: crypt]
#   - Debian lenny install fails so building from source (apt-get -y source sogo)
#       FILE: SOGo-Source/debian/sogo.preinst <- useradd fails
#
# Update 1.
#   - move SOGo to seperated db.
#   - small cleaning of plugin script 
#   - added more option during the install
#       Mysql host.
#       Mysql Port.
#
# Tests.
# - (ISPConfig 3.0.5) -- the main thing to consider for using this with other versions of ISPConfig is the ""IMAP Server"" configuration..
# - OS
#       Debian Lenny
#       Debian Squeeze
#
# Single server inviroment
#    - Multi domain, user sharing/ACL restricted to @DOMAIN.TLD
#       (User can't add other users ACLs unless allowed to by admin or other user.)
#    - Administrator of A domain is postmaster@DOMAIN.TLD
#    - ISPConfig plugin: 
#        i say BETA add/update/delete domain will trigger event to rebuild SOGo config.
#        - BuildConfig - 
#        only configure enabled domains
#        only allow IMAP enabled user to use SOGo
#        auto create sogo users view if none existing.
#        Remove SOGo view if domain is deleted
#        Remove user data from sogodb if user is deletet.. (if no data for user exists CRON log will show ''sogo-tool[nnn] No folder returned for user 'USER@DOMAIN.TLD') THATS not an error it just means the user never used sogo..
#        
# Multi server inviroment
#        not tested, + i dont have the capacity to do the tests needed..
#        
# Bugs:
#    - vhost may need tweeking before SOGo can be accessed (Default: /etc/apache2/conf.d/SOGo.conf)
#
# Refrenses
#    http://wiki.debian.org/SOGo
#    http://www.sogo.nu/english/downloads/documentation.html
#    ----
#    http://www.sogo.nu/english/downloads/frontends.html
#    http://www.sogo.nu/english/downloads/backend.html
#
# TODO:
#    - More Testing !!!!
#    - 
#    - get mail aliases into SOGo tables..
#    - find a way to make sive work (i only know how to use "ManageSieve server" ) or not ISPConfig does a good job there
#    - Auto backup of users and configs using sogo-tools.
#    - 
#    - Make the script upon install create and pack plugins for thunderbird
#    - Add script support for Ubuntu, other OS not likely since they needs to be compiled from source..
#    - Create a web module for ispconfig in order to allow a per domain configuration insted of a predefined for all
#    - Add SOGo OpenChange backend -- MAY BE -- http://www.sogo.nu/files/docs/SOGo%20Native%20Microsoft%20Outlook%20Configuration.pdf
#

OSTOCONF='debian'

if [ "${OSTOCONF}" == "debian" ]; then

    echo -e "select debian distro name [lenny|squeeze|wheezy]: \c "
    read DEBDISTRONAME
    ##echo -e "\ndeb http://apt.cmjscripter.net/inverse.ca/debian ${DEBDISTRONAME} ${DEBDISTRONAME}" >> /etc/apt/sources.list
cat >> /etc/apt/sources.list << EOF
deb http://inverse.ca/debian ${DEBDISTRONAME} ${DEBDISTRONAME}
## deb http://inverse.ca/debian-nightly ${DEBDISTRONAME} ${DEBDISTRONAME}
EOF

else

    echo "Distro not supported yet use debian"
    exit 1;

fi


if [ "${DEBDISTRONAME}" == "lenny" ]; then
    ##echo -e "\ndeb-src http://apt.cmjscripter.net/inverse.ca/debian ${DEBDISTRONAME} ${DEBDISTRONAME}" >> /etc/apt/sources.list
cat >> /etc/apt/sources.list << EOF
deb-src http://inverse.ca/debian ${DEBDISTRONAME} ${DEBDISTRONAME}
## deb-src http://inverse.ca/debian-nightly ${DEBDISTRONAME} ${DEBDISTRONAME}
EOF
    echo "Adding inverse gnupg keys from: keys.gnupg.net"
    apt-key adv --keyserver keys.gnupg.net --recv-key 0x810273C4  > /dev/null 2>&1
    echo "Updateing apt packages list ..."
    aptitude update > /dev/null 2>&1
    echo ".."
    echo "."
    echo "Debian lenny will fail to install sogo, thers an error in the package debian/sogo.preinst"
    echo "so we build it from source"
    sleep 3
    cd /tmp/
    aptitude install -y memcached rpl
    aptitude install -y dpkg-dev gobjc libgnustep-base-dev libsope-appserver4.9-dev libsope-core4.9-dev libsope-gdl1-4.9-dev libsope-ldap4.9-dev libsope-mime4.9-dev libsope-xml4.9-dev libmemcached-dev libxml2-dev libsbjson-dev libssl-dev libcurl4-openssl-dev
    apt-get -y source sogo
    cd sogo-*
cat > debian/sogo.preinst << EOF
#!/bin/bash

set -x

# summary of how this script can be called:
#        * <new-preinst> \`install'
#        * <new-preinst> \`install' <old-version>
#        * <new-preinst> \`upgrade' <old-version>
#        * <old-preinst> \`abort-upgrade' <new-version>
#
# for details, see http://www.debian.org/doc/debian-policy/ or
# the debian-policy package

if [ "\$1" == "install" ] || [ "\$1" == "upgrade" ]; then

  if ! id sogo 1> /dev/null 2>&1; then
    groupadd -f -r sogo
    useradd -d /var/lib/sogo -g sogo -c "SOGo daemon" -s /usr/sbin/nologin -r sogo
  fi

  # create mandatory dirs and enforce owner/perms
  for dir in lib log run spool; do
    install -m 750 -o sogo -g sogo -d /var/\$dir/sogo
  done
fi

#DEBHELPER#

exit 0
EOF
    dpkg-buildpackage -b
    echo "Installing tmpreaper, sogo, sope4.9-gdl1-mysql"
    aptitude install -y sope4.9-libxmlsaxdriver tmpreaper
    dpkg -i ../sogo_2*.deb
    aptitude install -y sope4.9-gdl1-mysql
else

    echo "Adding inverse gnupg keys from: keys.gnupg.net"
    apt-key adv --keyserver keys.gnupg.net --recv-key 0x810273C4  > /dev/null 2>&1
    echo "Updateing apt packages list ..."
    aptitude update > /dev/null 2>&1
    echo "Installing sogo, sope4.9-gdl1-mysql, memcached, rpl"
    aptitude install -y sogo sope4.9-gdl1-mysql memcached rpl

fi

#echo "Installing openchangeserver for SOGo..."
#apt-get install -t squeeze-backports libwbclient-dev samba-common smbclient libsmbclient libsmbclient-dev
#apt-get update
#apt-get install samba4
#apt-get install openchangeserver sogo-openchange openchangeproxy openchange-ocsmanager openchange-rpcproxy

#### remove the warning of tmpreaper 
echo "Removeing warning of tmpreaper"
rpl 'SHOWWARNING=true' 'SHOWWARNING=false' /etc/tmpreaper.conf  > /dev/null 2>&1
#### memcached not happy w. IPv6
echo "memcached not happy w. IPv6, settings to 127.0.0.1"
rpl '127.0.0.1' localhost /etc/memcached.conf  > /dev/null 2>&1
/etc/init.d/memcached restart  > /dev/null 2>&1

echo -e "mysql admin user [root]: \c "
read MYSQLADMUSER
if [ -z "${MYSQLADMUSER}" ]; then
    MYSQLADMUSER="root"
fi

echo -e "mysql root password: \c "
read MYSQLROOTPW

echo -e "mysql host [127.0.0.1]: \c "
read MYSQLHOST
if [ -z "${MYSQLHOST}" ]; then
    MYSQLHOST="127.0.0.1"
fi

echo -e "mysql port [3306]: \c "
read MYSQLPORT
if [ -z "${MYSQLPORT}" ]; then
    MYSQLPORT="3306"
fi

echo -e "ISPCONFIG database name [dbispconfig]: \c "
read ISPCONFIGDB
if [ -z "${ISPCONFIGDB}" ]; then
    ISPCONFIGDB="dbispconfig"
fi

echo -e "SOGo database name [sogodb]: \c "
read SOGODB
if [ -z "${SOGODB}" ]; then
    SOGODB="sogodb"
fi

echo -e "ISPCONFIG install path [/usr/local/ispconfig]: \c "
read ISPCONFIGINSTALLPATH

if [ -z "${ISPCONFIGINSTALLPATH}" ]; then
    ISPCONFIGINSTALLPATH="/usr/local/ispconfig"
fi
#echo "ISPConfig user needs PRIVILEGES for Create view..."
#echo -e "ISPConfig DB Username [ispconfig]\c "
#read ISPCONFIGUSERN
#if [ -z "${ISPCONFIGUSERN}" ]; then
#    ISPCONFIGUSERN="ispconfig"
#fi

echo -e "SOGO DB Username [sogosysuser]: \c "
read SOGOUSERN
if [ -z "${SOGOUSERN}" ]; then
    SOGOUSERN="sogosysuser"
fi

echo "SOGO DB Username Password"
echo -e "leave empty for auto generation: \c "
read SOGOUSERPW
if [ -z "${SOGOUSERPW}" ]; then
    SOGOUSERPW=`< /dev/urandom tr -dc A-Za-z0-9_ | head -c25`
fi


mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "CREATE DATABASE ${SOGODB};";
mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "CREATE USER '${SOGOUSERN}'@'${MYSQLHOST}' IDENTIFIED BY '${SOGOUSERPW}';";
mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "GRANT ALL PRIVILEGES ON \`${SOGODB}\`.* TO '${SOGOUSERN}'@'${MYSQLHOST}' WITH GRANT OPTION;";
mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "GRANT SELECT ON \`${ISPCONFIGDB}\`.* TO '${SOGOUSERN}'@'${MYSQLHOST}';";
mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "FLUSH PRIVILEGES;";

echo -e "Default IMAP Server Addr [localhost]: \c "
read IMAPSERVER
if [ -z "${IMAPSERVER}" ]; then
    IMAPSERVER="localhost"
fi

echo "Select IMAP user password algorithm"
echo "plain|crypt|md5-crypt|md5|plain-md5"
echo "Confirm with your imap server config || http://wiki.dovecot.org/Authentication/PasswordSchemes"
echo -e "Use algorithm [crypt]: \c "
read IMAPPWALGORITHM
if [ -z "${IMAPPWALGORITHM}" ]; then
    IMAPPWALGORITHM="crypt"
fi


echo -e "Default SMTP Server Addr [localhost]: \c "
read SMTPSERVER
if [ -z "${SMTPSERVER}" ]; then
    SMTPSERVER="localhost"
fi

echo -e "Default SOGo Language [English]: \c "
read SOGOLANGUAGE
if [ -z "${SOGOLANGUAGE}" ]; then
    SOGOLANGUAGE="English"
fi

echo -e "Default SOGo TimeZone [Europe/Berlin]: \c "
read SOGOTIMEZONE
if [ -z "${SOGOTIMEZONE}" ]; then
    SOGOTIMEZONE="Europe/Berlin"
fi

SOGOBINARY=`which sogod`
SOGOTOOLBINARY=`which sogo-tool`
SOGOHOMEDIR=$(getent passwd sogo | cut -d: -f6)
SOGOGNUSTEPCONFFILE=${SOGOHOMEDIR}/GNUstep/Defaults/.GNUstepDefaults
SOGOZIPPATH=`which zip`
#if [ "${OSTOCONF}" -eq "debian" ]; then
    ## hmm maby theres a diff on debian and ubuntu...
#fi

if [ -f /etc/init.d/sogo ]; then
    SOGOINITSCRIPT=/etc/init.d/sogo
fi
if [ -f /etc/init.d/sogod ]; then
    SOGOINITSCRIPT=/etc/init.d/sogod
fi


if ! id ispconfig 1> /dev/null 2>&1; then
    echo -e "can't find system user 'ispconfig' enter the name thank you: \c "
    read ISPCSYSTEMUSER
else
    ISPCSYSTEMUSER="ispconfig"
fi

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!DOCTYPE plist PUBLIC \"-//GNUstep//DTD plist 0.9//EN\" \"http://www.gnustep.org/plist-0_9.xml\">
<plist version=\"0.9\">
    <dict>
        <key>NSGlobalDomain</key>
        <dict></dict>
        <key>sogod</key>
        <dict>
            <key>SOGoPageTitle</key>
            <string>ISPConfig 3 w/SOGo</string>
            <key>SOGoLoginModule</key>
            <string>Mail</string>
            <key>SOGoZipPath</key>
            <string>${SOGOZIPPATH}</string>
            <key>SOGoSoftQuotaRatio</key>
            <string>0.9</string>
            <key>SOGoMailUseOutlookStyleReplies</key>
            <string>NO</string>
            <key>SOGoMailAuxiliaryUserAccountsEnabled</key>
            <string>NO</string>
            <key>SOGoMailCustomFromEnabled</key>
            <string>NO</string>
            <key>SOGoDefaultCalendar</key>
            <string>selected</string>
            <key>SOGoMailListViewColumnsOrder</key>
            <array>
                <string>Flagged</string>
                <string>Priority</string>
                <string>Date</string>
                <string>From</string>
                <string>Subject</string>
                <string>Attachment</string>
                <string>Unread</string>
                <string>Size</string>
            </array>
            <key>NGImap4ConnectionStringSeparator</key>
            <string>.</string>
            <key>SOGoEnableEMailAlarms</key>
            <string>YES</string>
            <key>OCSEMailAlarmsFolderURL</key>
            <string>mysql://${SOGOUSERN}:${SOGOUSERPW}@${MYSQLHOST}:${MYSQLPORT}/${SOGODB}/sogo_mailalarms_folder</string>
            <key>OCSFolderInfoURL</key>
            <string>mysql://${SOGOUSERN}:${SOGOUSERPW}@${MYSQLHOST}:${MYSQLPORT}/${SOGODB}/sogo_folder_info</string>
            <key>OCSSessionsFolderURL</key>
            <string>mysql://${SOGOUSERN}:${SOGOUSERPW}@${MYSQLHOST}:${MYSQLPORT}/${SOGODB}/sogo_sessions_folder</string>
            <key>SOGoProfileURL</key>
            <string>mysql://${SOGOUSERN}:${SOGOUSERPW}@${MYSQLHOST}:${MYSQLPORT}/${SOGODB}/sogo_user_profile</string>
            <key>SOGoACLsSendEMailNotifcations</key>
            <string>YES</string>
            <key>SOGoAppointmentSendEMailNotifcations</key>
            <string>YES</string>
            <key>SOGoAppointmentSendEMailReceipts</key>
            <string>YES</string>
            <key>SOGoAuthenticationMethod</key>
            <string>SQL</string>
            <key>SOGoCalendarDefaultRoles</key>
            <array>
                <string>PublicViewer</string>
                <string>ConfidentialDAndTViewer</string>
            </array>
            <key>SOGoContactsDefaultRoles</key>
            <array>
                <string>ObjectViewer</string>
            </array>
            <key>SOGoFirstDayOfWeek</key>
            <string>1</string>
            <key>SOGoFirstWeekOfYear</key>
            <string>FirstFullWeek</string>
            <key>SOGoFirtDayOfWeek</key>
            <string>1</string>
            <key>SOGoFoldersSendEMailNotifcations</key>
            <string>YES</string>
            <key>SOGoForceIMAPLoginWithEmail</key>
            <string>YES</string>
            <key>SOGoForwardEnabled</key>
            <string>NO</string>
            <key>SOGoIMAPAclConformsToIMAPExt</key>
            <string>Yes</string>
            <key>SOGoIMAPServer</key>
            <string>${IMAPSERVER}</string>
            <key>SOGoLanguage</key>
            <string>${SOGOLANGUAGE}</string>
            <key>SOGoMailMessageCheck</key>
            <string>every_minute</string>
            <key>SOGoMailReplyPlacement</key>
            <string>above</string>
            <key>SOGoMailingMechanism</key>
            <string>smtp</string>
            <key>SOGoPasswordChangeEnabled</key>
            <string>NO</string>
            <key>SOGoSMTPServer</key>
            <string>${SMTPSERVER}</string>
            <key>SOGoSieveScriptsEnabled</key>
            <string>NO</string>
            <key>SOGoSieveServer</key>
            <string>sieve://localhost:4190</string>
            <key>SOGoTimeZone</key>
            <string>${SOGOTIMEZONE}</string>
            <key>SOGoVacationEnabled</key>
            <string>NO</string>
            <key>SxVMemLimit</key>
            <string>512</string>
            <key>WOUseRelativeURLs</key>
            <string>YES</string>
            <key>WOWorkersCount</key>
            <string>1</string>
            <key>domains</key>
            <dict>{{SOGODOMAINSCONF}}
            </dict>
        </dict>
    </dict>
</plist>" >${ISPCONFIGINSTALLPATH}/server/conf/sogo.conf-templ
chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} ${ISPCONFIGINSTALLPATH}/server/conf/sogo.conf-templ

mkdir -p ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains

cat > ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/domains_default.conf << EOF

                <key>{{DOMAIN}}</key>
                <dict>
                    <key>SOGoDraftsFolderName</key>
                    <string>Drafts</string>
                    <key>SOGoSentFolderName</key>
                    <string>Sent</string>
                    <key>SOGoTrashFolderName</key>
                    <string>Trash</string>
                    <key>SOGoMailShowSubscribedFoldersOnly</key>
                    <string>NO</string>
                    <key>SOGoLanguage</key>
                    <string>English</string>
                    <key>SOGoMailDomain</key>
                    <string>{{DOMAIN}}</string>
                    <key>SOGoSuperUsernames</key>
                    <array>
                        <string>{{DOMAINADMIN}}</string>
                    </array>
                    <key>SOGoUserSources</key>
                    <array>
                        <dict>
                            <key>userPasswordAlgorithm</key>
                            <string>crypt</string>
                            <key>prependPasswordScheme</key>
                            <string>NO</string>
                            <key>LoginFieldNames</key>
                            <array>
                                <string>c_uid</string>
                                <string>mail</string>
                            </array>
                            <key>IMAPHostFieldName</key>
                            <string>imap_host</string>
                            <key>IMAPLoginFieldName</key>
                            <string>c_uid</string>
                            <key>type</key>
                            <string>sql</string>
                            <key>isAddressBook</key>
                            <string>NO</string>
                            <key>canAuthenticate</key>
                            <string>YES</string>
                            <key>displayName</key>
                            <string>Users in {{DOMAIN}}</string>
                            <key>hostname</key>
                            <string>localhost</string>
                            <key>id</key>
                            <string>{{SOGOUNIQID}}</string>
                            <key>viewURL</key>
                            <string>{{CONNECTIONVIEWURL}}</string>
                        </dict>
                    </array>
                </dict>
EOF
cat > ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/read_me << EOF
for how to configure the config file and the format of the files see SOGo documentation
the file names must be as follow (must end with .conf)
they are not automaticly created you need to do this by hand.

a template for domain example.com wil be named.
example.com.conf

domains_default.conf :: default for all domains..
EOF

chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} -R ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains

cat > ${ISPCONFIGINSTALLPATH}/server/plugins-available/sogo_config_plugin.php << EOF
<?php

/*
 * Copyright (C) 2013 Christian M. Jensen
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
 * 
 * NOTE* for more info, modifications etc.. contact me at http://www.howtoforge.com/ my user name: 'psykosen'
 */

class sogo_config_plugin {

    var \$plugin_name = 'sogo_config_plugin';
    var \$class_name = 'sogo_config_plugin';
    var \$sogo_su_cmd = "sudo -u sogo";
    var \$sogopw = '${SOGOUSERPW}';
    var \$sogouser = '${SOGOUSERN}';
    var \$sogodb = '${SOGODB}';
    var \$ispcdb = '${ISPCONFIGDB}';
    var \$sogobinary = '${SOGOBINARY}';
    var \$sogotoolbinary = '${SOGOTOOLBINARY}';
    var \$sogohomedir = '${SOGOHOMEDIR}';
    var \$sogoconffile = '${SOGOGNUSTEPCONFFILE}';
    var \$sogoinitscript = '${SOGOINITSCRIPT}';
    var \$templ_file = '${ISPCONFIGINSTALLPATH}/server/conf/sogo.conf-templ';
    var \$templ_domains_dir = '${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains';
    var \$mysql_server_host = '${MYSQLHOST}:${MYSQLPORT}';

    function onInstall() {
        global \$conf;
        if (\$conf['services']['mail'] == true) {
            return true;
        } else {
            return false;
        }
    }

    function onLoad() {
        global \$app;
        \$app->plugins->registerEvent('mail_domain_delete', \$this->plugin_name, 'reconfigure');
        \$app->plugins->registerEvent('mail_domain_insert', \$this->plugin_name, 'reconfigure');
        \$app->plugins->registerEvent('mail_domain_update', \$this->plugin_name, 'reconfigure');
        \$app->plugins->registerEvent('mail_user_delete', \$this->plugin_name, 'remove_sogo_mail_user');
    }

    function remove_sogo_mail_user(\$event_name, \$data) {
        global \$app, \$conf;
        if (\$event_name == 'mail_user_delete') {
            exec(\$this->sogo_su_cmd . ' ' . \$this->sogotoolbinary . ' remove ' . escapeshellarg(\$data['old']['login']));
            sleep(1);
        }
    }

    function reconfigure(\$event_name, \$data) {
        global \$app, \$conf;
        \$flag = false;
        if (\$event_name == 'mail_domain_delete') {
            \$flag = \$this->remove_sogo_maildomain((isset(\$data['new']['domain']) ? \$data['new']['domain'] : \$data['old']['domain']));
        } else if (\$event_name == 'mail_domain_insert') {
            \$flag = true;
        } else if (\$event_name == 'mail_domain_update') {
            \$flag = true;
        } else {
            //* i can\'t work with that give me a command...
            // /PATH/to/ISPConfig_DIR/server/SOGO-reconfigure.log
            // file_put_contents('SOGO-reconfigure.log', print_r(\$event_name,true)."\n\n".print_r(\$data,true));
        }
        if (\$flag) {
            \$active_mail_domains = \$app->db->queryAllRecords('SELECT \`domain\` FROM \`mail_domain\` WHERE \`active\`=\'y\'');
            \$sogo_conf = file_get_contents(\$this->templ_file);
            \$tmp_conf = "";
            foreach (\$active_mail_domains as \$vd) {
                \$tmp_conf .= \$this->build_conf_sogo_maildomain(\$vd['domain']);
                //* create if not exist
                \$this->create_sogo_view(\$vd['domain']);
            }
            \$sogo_conf = str_replace('{{SOGODOMAINSCONF}}', \$tmp_conf, \$sogo_conf);
            if (!file_put_contents(\$this->sogoconffile, \$sogo_conf)) {
                \$app->log('ERROR. unable to reconfigure SOGo..', LOGLEVEL_ERROR);
                return;
            } else {
                exec(\$this->sogoinitscript . ' restart');
                //** make the system wait..
                sleep(2);
            }
        }
    }

    function remove_sogo_maildomain(\$dom) {
        global \$app, \$conf;
        //* TODO: validate domain the correct way not by filter_var
        if (empty(\$dom) || filter_var('http://' . \$dom, FILTER_VALIDATE_URL) === false) {
            \$app->log('ERROR. removeing sogo mail domain.. domain invalid [' . \$dom . ']', LOGLEVEL_ERROR);
            return false;
        }

        \$dom_no_point = str_replace('.', '_', \$dom);
        \$sqlres = \$this->_sqlConnect();
        \$sqlres->query('DROP VIEW \`sogo_users_' . \$dom_no_point . '\`');
        /* Broke my connection??? */
        /* @\$sqlres->close(); */
        return true;
    }

    function create_sogo_view(\$dom) {
        global \$app, \$conf;
        \$sqlres = \$this->_sqlConnect();

        \$dom_no_point = str_replace('.', '_', \$dom);
        \$sql1 = "SELECT \`TABLE_NAME\` FROM \`information_schema\`.\`VIEWS\` WHERE \`TABLE_SCHEMA\`='{\$this->sogodb}' AND \`TABLE_NAME\`='sogo_users_" . \$dom_no_point . "'";

        \$tmp = \$sqlres->query(\$sql1);
        while (\$obj = \$tmp->fetch_object()) {
            if (\$obj->TABLE_NAME == 'sogo_users_' . \$dom_no_point) {
                return true;
            }
        }

        \$sqlres->query('CREATE VIEW sogo_users_' . \$dom_no_point . ' AS SELECT
	\`login\` AS c_uid,
	\`login\` AS c_name,
	\`password\` AS c_password,
	\`name\` AS c_cn,
	\`email\` AS mail,
	(SELECT \`server_name\` FROM ' . \$this->ispcdb . '.\`server\`, ' . \$this->ispcdb . '.\`mail_user\` WHERE \`mail_user\`.\`server_id\`=\`server\`.\`server_id\` AND \`server\`.\`mail_server\`=1 AND ispcmu.\`login\`=\`mail_user\`.\`login\` LIMIT 1) AS imap_host 
        FROM ' . \$this->ispcdb . '.\`mail_user\` AS ispcmu  WHERE \`email\` LIKE \'%' . \$dom_no_point . '\' AND disableimap=\'n\'');
        if (!empty(\$sqlres->error))
            \$app->log('ERROR. unable to create SOGo view[sogo_users_' . \$dom_no_point . '].. ' . \$sqlres->error, LOGLEVEL_ERROR);
        /* Broke my connection??? */
        /* @\$sqlres->close(); */
        return true;
    }

    function build_conf_sogo_maildomain(\$dom) {
        global \$app, \$conf;
        \$dom_no_point = str_replace('.', '_', \$dom);
        /* For mail aliases..
          <key>MailFieldNames</key>
          <array>
          <string>Col1</string>
          <string>Col2</string>
          <string>Col3</string>
          </array>
         */
        \$sogo_conf = "";
        \$sogo_conf_vars = array(
            '{{DOMAIN}}' => \$dom,
            '{{DOMAINADMIN}}' => 'postmaster@' . \$dom,
            '{{SOGOUNIQID}}' => \$dom_no_point,
            '{{CONNECTIONVIEWURL}}'=>"mysql://{\$this->sogouser}:{\$this->sogopw}@{\$this->mysql_server_host}/{\$this->sogodb}/sogo_users_{\$dom_no_point}"
        );
        if (file_exists("{\$this->templ_domains_dir}/{\$dom}.conf")) {
            \$sogo_conf = file_get_contents("{\$this->templ_domains_dir}/{\$dom}.conf");
        } else {
            if (!file_exists("{\$this->templ_domains_dir}/{\$dom}.conf"))
                \$app->log('ERROR. loading domains config.. file: '."{\$this->templ_domains_dir}/{\$dom}.conf", LOGLEVEL_DEBUG);
            if (file_exists("{\$this->templ_domains_dir}/domains_default.conf")) {
                \$sogo_conf = file_get_contents("{\$this->templ_domains_dir}/domains_default.conf");
            }  else {
                \$app->log('ERROR. loading domain config.. file: ' . "{\$this->templ_domains_dir}/domains_default.conf", LOGLEVEL_ERROR);
                return;
            }
        }
        if (!empty(\$sogo_conf)) {
            foreach (\$sogo_conf_vars as \$key => \$value) {
                \$sogo_conf = preg_replace("/{\$key}/i", \$value, \$sogo_conf);
            }
        }
        return \$sogo_conf;
    }

    function _sqlConnect() {
        \$_sqlserver = explode(':', \$this->mysql_server_host);
        \$sqlres = new mysqli(\$_sqlserver[0], \$this->sogouser, \$this->sogopw, \$this->sogodb, \$_sqlserver[1]);
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        return \$sqlres;
    }
}
?>
EOF
chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} ${ISPCONFIGINSTALLPATH}/server/plugins-available/sogo_config_plugin.php
### /usr/local/ispconfig/server/plugins-available/sogo_config_plugin.php
## configure sogo before restarting sogo service..

# enable the plugin..
ln -s ${ISPCONFIGINSTALLPATH}/server/plugins-available/sogo_config_plugin.php ${ISPCONFIGINSTALLPATH}/server/plugins-enabled/sogo_config_plugin.inc.php

echo "Allmost there wee just need to configure the vhost"
echo "."
echo -e "SOGo Domain vhost to configure [`hostname --fqdn`]: \c "
read SOGOVHOSTNAME
if [ -z "${SOGOVHOSTNAME}" ]; then
    SOGOVHOSTNAME=`hostname --fqdn`
fi
echo -e "HTTP Protocol [http]: \c "
read SOGOPROTOCAL
if [ -z "${SOGOPROTOCAL}" ]; then
    SOGOPROTOCAL="http"
fi
echo -e "HTTP Port: [80]: \c "
read SOGOHTTPPORT
if [ -z "${SOGOHTTPPORT}" ]; then
    SOGOHTTPPORT="80"
fi

echo "
<VirtualHost *:${SOGOHTTPPORT}>
   Servername ${SOGOVHOSTNAME}:${SOGOHTTPPORT}
   DocumentRoot /usr/lib/GNUstep/SOGo/WebServerResources/
   ## ErrorLog /var/log/apache2/sogo-error.log
   ## Customlog /var/log/apache2/sogo-access.log combined
   ServerSignature Off
    Alias /SOGo.woa/WebServerResources/ \
          /usr/lib/GNUstep/SOGo/WebServerResources/
    Alias /SOGo/WebServerResources/ \
          /usr/lib/GNUstep/SOGo/WebServerResources/
    AliasMatch /SOGo/so/ControlPanel/Products/(.*)/Resources/(.*) \
               /usr/lib/GNUstep/SOGo/\$1.SOGo/Resources/\$2
    <Directory /usr/lib/GNUstep/SOGo/>
        AllowOverride None
        Order deny,allow
        Allow from all
        # Explicitly allow caching of static content to avoid browser specific behavior.
        # A resource's URL MUST change in order to have the client load the new version.
        <IfModule expires_module>
          ExpiresActive On
          ExpiresDefault \"access plus 1 year\"
        </IfModule>
    </Directory>
    <LocationMatch \"^/SOGo/so/ControlPanel/Products/.*UI/Resources/.*\.(jpg|png|gif|css|js)\">
      SetHandler default-handler
    </LocationMatch>
    ## Uncomment the following to enable proxy-side authentication, you will then
    ## need to set the \"SOGoTrustProxyAuthentication\" SOGo user default to YES and
    ## adjust the \"x-webobjects-remote-user\" proxy header in the \"Proxy\" section
    ## below.
    #<Location /SOGo>
    #  AuthType XXX
    #  Require valid-user
    #  SetEnv proxy-nokeepalive 1
    #  Allow from all
    #</Location>
    ProxyRequests Off
    SetEnv proxy-nokeepalive 1
    ProxyPreserveHost On
    # When using CAS, you should uncomment this and install cas-proxy-validate.py
    # in /usr/lib/cgi-bin to reduce server overloading
    #
    # ProxyPass /SOGo/casProxy http://localhost/cgi-bin/cas-proxy-validate.py
    # <Proxy http://localhost/app/cas-proxy-validate.py>
    #   Order deny,allow
    #   Allow from your-cas-host-addr
    # </Proxy>
    ProxyPass /SOGo http://127.0.0.1:20000/SOGo retry=0
    <Proxy http://127.0.0.1:20000/SOGo>
    ## adjust the following to your configuration
      RequestHeader set \"x-webobjects-server-port\" \"${SOGOHTTPPORT}\"
      RequestHeader set \"x-webobjects-server-name\" \"${SOGOVHOSTNAME}\"
      RequestHeader set \"x-webobjects-server-url\" \"${SOGOPROTOCAL}://${SOGOVHOSTNAME}:${SOGOHTTPPORT}\"
    ## When using proxy-side autentication, you need to uncomment and
    ## adjust the following line:
    #  RequestHeader set \"x-webobjects-remote-user\" \"%{REMOTE_USER}e\"
      RequestHeader set \"x-webobjects-server-protocol\" \"HTTP/1.0\"
      RequestHeader set \"x-webobjects-remote-host\" %{REMOTE_HOST}e env=REMOTE_HOST
      AddDefaultCharset UTF-8
      Order allow,deny
      Allow from all
    </Proxy>
    ## We use mod_rewrite to pass remote address to the SOGo proxy.
    # The remote address will appear in SOGo's log files and in the X-Forward
    # header of emails.
    RewriteEngine On
    RewriteRule ^/SOGo/(.*)\$ /SOGo/\$1 [env=REMOTE_HOST:%{REMOTE_ADDR},PT]
    Redirect permanent /index.html ${SOGOPROTOCAL}://${SOGOVHOSTNAME}:${SOGOHTTPPORT}/SOGo
</virtualhost>
"> /etc/apache2/conf.d/SOGo.conf

## final restart services 
/etc/init.d/sogo restart
a2enmod proxy proxy_http headers rewrite
/etc/init.d/apache2 restart
clear
echo -e "------------ SOGo Installed ------------"
echo -e "${SOGOPROTOCAL}://${SOGOVHOSTNAME}:${SOGOHTTPPORT}/SOGo"
echo -e "VHOST Conf:\t\t/etc/apache2/conf.d/SOGo.conf"
echo -e ""
echo -e "ISPC Plugin:\t\t${ISPCONFIGINSTALLPATH}/server/plugins-available/sogo_config_plugin.php"
echo -e "ISPC Template:\t\t${ISPCONFIGINSTALLPATH}/server/conf/sogo.conf-templ"
echo -e "SOGo Domain Templates:\t\t${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/"
echo -e "SOGo Bin:\t\t${SOGOBINARY}"
echo -e "SOGo-Tool Bin:\t\t${SOGOTOOLBINARY}"
echo -e "SOGo Home:\t\t${SOGOHOMEDIR}"
echo -e "SOGo Config:\t\t${SOGOGNUSTEPCONFFILE}"
echo -e "SOGo Init:\t\t${SOGOINITSCRIPT}"
echo -e "DB Name:\t\t${SOGODB}"
#echo -e "DB Name:\t\t${ISPCONFIGDB}"
echo -e "DB User:\t\t${SOGOUSERN}"
echo -e "DB Psswd:\t\t${SOGOUSERPW}"
echo -e ""
echo -e "Adminitrator is postmaster@DOMAIN.TLD"
echo -e "if postmaster mail addr is not added go add it and login to SOGo to start administrat the domain"
echo -e "Enable SOGo logins by update/delete or add a mail domain"
echo -e "----------------------------------------"

exit 0;