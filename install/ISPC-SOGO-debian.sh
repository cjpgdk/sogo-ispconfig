#!/bin/sh

#
# Install SOGo on debian ISPConfig 3 server
#
# Tests.
# Single server inviroment
#    - Install OK
#    - Multi domain, user sharing/ACL restricted to @DOMAIN.TLD
#    - Administrator of A domain is postmaster@DOMAIN.TLD
#    - ISPConfig plugin: 
#        i say BETA add/update/delete domain will trigger event to rebuild SOGo config.
#        - BuildConfig - 
#        only configure enabled domains: OK
#        only allow IMAP enabled user to use SOGo: OK
#        auto create sogo users view if none existing.
#        Remove SOGo view if domain is deleted: 50%, needs to use "sogo-tools" to remove the tables created for individual users.
#        
# Multi server inviroment
#        not tested, + i dont have the capacity to do the tests needed..
#        
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
#    - Allow sogo to use private db (Keep sogo and ispconfg seperated...)
#        vi /etc/mysql/my.cnf
#        [mysqld]
#        federated
#        ?? if not finding a better way the sql is at the bottom of the script..
#        !!! Plugin is not able to use that yet..
#
#
#    - Auto backup of users and configs using sogo-tools.
#    - 
#    - Make the script upon install create and pack plugins for thunderbird
#    - Add script support for Ubuntu, other OS not likely since they needs to be compiled from source..
#    - Create a web module for ispconfig in order to allow a per domain configuration insted of a predefined for all
#    - Add SOGo OpenChange backend --- http://www.sogo.nu/files/docs/SOGo%20Native%20Microsoft%20Outlook%20Configuration.pdf
#

echo -e "update 1 is released download ad use that? (y/n) [y]: \c "
read USEUPDATE
if [ -z "${USEUPDATE}" ]; then
    USEUPDATE="y"
fi
if [ "${USEUPDATE}" == "y" ]; then
    wget http://cmjscripter.net/files/scripts/ispc/ISPC-SOGO-debian-u1.sh -O `pwd`/ISPC-SOGO-debian-u1.sh
    chmod +x `pwd`/ISPC-SOGO-debian-u1.sh
    bash `pwd`/ISPC-SOGO-debian-u1.sh
    exit 0;
fi

OSTOCONF='debian'

echo -e "select debian distro name [lenny|squeeze|wheezy]: \c "
read DEBDISTRONAME
echo "
deb http://inverse.ca/debian ${DEBDISTRONAME} ${DEBDISTRONAME}
## deb http://inverse.ca/debian-nightly ${DEBDISTRONAME} ${DEBDISTRONAME}
" >> /etc/apt/sources.list

echo "Adding inverse gnupg keys from: keys.gnupg.net"
apt-key adv --keyserver keys.gnupg.net --recv-key 0x810273C4  > /dev/null 2>&1
echo "Updateing apt packages list ..."
aptitude update > /dev/null 2>&1
echo "Installing sogo, sope4.9-gdl1-mysql, memcached, rpl"
aptitude install -y sogo sope4.9-gdl1-mysql memcached rpl

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

echo -e "mysql root password: \c "
read MYSQLROOTPW

echo -e "ISPCONFIG database name [dbispconfig]\c "
read ISPCONFIGDB
if [ -z "${ISPCONFIGDB}" ]; then
    ISPCONFIGDB="dbispconfig"
fi
#echo -e "SOGo database name [sogodb]\c "
#read SOGODB
#if [ -z "${SOGODB}" ]; then
#    SOGODB="sogodb"
#fi

echo -e "ISPCONFIG install path [/usr/local/ispconfig]\c "
read ISPCONFIGINSTALLPATH

if [ -z "${ISPCONFIGINSTALLPATH}" ]; then
    ISPCONFIGINSTALLPATH="/usr/local/ispconfig"
fi
echo "ISPConfig user needs PRIVILEGES for Create view..."
echo -e "ISPConfig DB Username [ispconfig]\c "
read ISPCONFIGUSERN

if [ -z "${ISPCONFIGUSERN}" ]; then
    ISPCONFIGUSERN="ispconfig"
fi

echo -e "SOGO DB Username [sogosysuser]\c "
read SOGOUSERN

if [ -z "${SOGOUSERN}" ]; then
    SOGOUSERN="sogosysuser"
fi

SOGOUSERPW=`< /dev/urandom tr -dc A-Za-z0-9_ | head -c15`
##mysql -u root -p${MYSQLROOTPW} -e "CREATE DATABASE ${SOGODB};";
mysql -u root -p${MYSQLROOTPW} -e "CREATE USER '${SOGOUSERN}'@'localhost' IDENTIFIED BY '${SOGOUSERPW}';";
mysql -u root -p${MYSQLROOTPW} -e "GRANT ALL PRIVILEGES ON \`${ISPCONFIGDB}\`.* TO '${SOGOUSERN}'@'localhost' WITH GRANT OPTION;";
#mysql -u root -p${MYSQLROOTPW} -e "GRANT ALL PRIVILEGES ON \`${SOGODB}\`.* TO '${SOGOUSERN}'@'localhost' WITH GRANT OPTION;";
mysql -u root -p${MYSQLROOTPW} -e "REVOKE ALL PRIVILEGES ON \`${ISPCONFIGDB}\`.* FROM '${ISPCONFIGUSERN}'@'localhost';";
mysql -u root -p${MYSQLROOTPW} -e "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE VIEW ON \`${ISPCONFIGDB}\`.* TO '${ISPCONFIGUSERN}'@'localhost';";
mysql -u root -p${MYSQLROOTPW} -e "FLUSH PRIVILEGES;";

echo -e "IMAP Server Addr [localhost]\c "
read IMAPSERVER
if [ -z "${IMAPSERVER}" ]; then
    IMAPSERVER="localhost"
fi
echo -e "SMTP Server Addr [localhost]\c "
read SMTPSERVER
if [ -z "${SMTPSERVER}" ]; then
    SMTPSERVER="localhost"
fi

echo -e "SOGo Language [English]\c "
read SOGOLANGUAGE
if [ -z "${SOGOLANGUAGE}" ]; then
    SOGOLANGUAGE="English"
fi

echo -e "SOGo TimeZone [Europe/Berlin]\c "
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
            <key>OCSFolderInfoURL</key>
            <string>mysql://${SOGOUSERN}:${SOGOUSERPW}@localhost:3306/${ISPCONFIGDB}/sogo_folder_info</string>
            <key>OCSSessionsFolderURL</key>
            <string>mysql://${SOGOUSERN}:${SOGOUSERPW}@localhost:3306/${ISPCONFIGDB}/sogo_sessions_folder</string>
            <key>SOGoProfileURL</key>
            <string>mysql://${SOGOUSERN}:${SOGOUSERPW}@localhost:3306/${ISPCONFIGDB}/sogo_user_profile</string>
            <key>SOGoACLsSendEMailNotifcations</key>
            <string>YES</string>
            <key>SOGoAppointmentSendEMailNotifcations</key>
            <string>YES</string>
            <key>SOGoAppointmentSendEMailReceipts</key>
            <string>NO</string>
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

echo "
<?php

class sogo_config_plugin { 
    var \$plugin_name = 'sogo_config_plugin'; 
    var \$class_name  = 'sogo_config_plugin';
    var \$sogopw  = '${SOGOUSERPW}';
    var \$sogouser  = '${SOGOUSERN}';
    /*var \$sogodb  = '${SOGODB}';*/
    var \$sogodb  = '${ISPCONFIGDB}';
    var \$sogobinary  = '${SOGOBINARY}';
    var \$sogotoolbinary  = '${SOGOTOOLBINARY}';
    var \$sogohomedir = '${SOGOHOMEDIR}';
    var \$sogoconffile = '${SOGOGNUSTEPCONFFILE}';
    var \$sogoinitscript = '${SOGOINITSCRIPT}';
    
    var \$templ_file = '${ISPCONFIGINSTALLPATH}/server/conf/sogo.conf-templ';

    function onInstall() {
        global \$conf;
        if(\$conf['services']['mail'] == true) {
            return true;
        } else {
            return false;
        }
    }
    
    function onLoad() { 
        global \$app;
        \$app->plugins->registerEvent('mail_domain_delete',\$this->plugin_name,'reconfigure');
        \$app->plugins->registerEvent('mail_domain_insert',\$this->plugin_name,'reconfigure');
        \$app->plugins->registerEvent('mail_domain_update',\$this->plugin_name,'reconfigure');
    } 
/*
mail_domain_insert
    [new] => Array
    [old] => Array
        (
            [domain_id] => 6
            [sys_userid] => 1
            [sys_groupid] => 0
            [sys_perm_user] => riud
            [sys_perm_group] => ru
            [sys_perm_other] =>
            [server_id] => 1
            [domain] => example.us
            [active] => y
        )
*/
    function reconfigure(\$event_name,\$data) {
        global \$app, \$conf;
        \$flag=false;
        if(\$event_name == 'mail_domain_delete') {
            \$flag=\$this->remove_sogo_maildomain((isset(\$data['new']['domain']) ? \$data['new']['domain'] : \$data['old']['domain']));
        } else if(\$event_name == 'mail_domain_insert') {
            \$flag=true;
        } else if(\$event_name == 'mail_domain_update') {
            \$flag=true;
        }else{
            //* i can't work with that give me a command...
            // /PATH/to/ISPConfig_DIR/server/SOGO-reconfigure.log
            // file_put_contents('SOGO-reconfigure.log', print_r(\$event_name,true).\"\n\n\".print_r(\$data,true));
        }
        if(\$flag){
            \$active_mail_domains = \$app->db->queryAllRecords('SELECT \`domain\` FROM \`mail_domain\` WHERE \`active\`=\'y\'');
            \$sogo_conf = file_get_contents(\$this->templ_file);
            \$tmp_conf = \"\";
            foreach(\$active_mail_domains as \$vd){
                \$tmp_conf .= \$this->build_conf_sogo_maildomain(\$vd['domain']);
                //* create if not exist
                \$this->create_sogo_view(\$vd['domain']);
            }
            \$sogo_conf = str_replace('{{SOGODOMAINSCONF}}', \$tmp_conf, \$sogo_conf);
            
            if (!file_put_contents(\$this->sogoconffile, \$sogo_conf)) {
                \$app->log('ERROR. unable to reconfigure SOGo..',LOGLEVEL_ERROR);
                return;
            } else {
                exec(\$this->sogoinitscript . ' restart');
                //** make the system wait..
                sleep(2);
            }
        }
    }

    function remove_sogo_maildomain(\$dom){
        global \$app, \$conf;
        //* TODO: validate domain the correct way not by filter_var
        if(empty(\$dom) || filter_var('http://'.\$dom, FILTER_VALIDATE_URL)===false){
            \$app->log('ERROR. removeing sogo mail domain.. domain invalid ['.\$dom.']',LOGLEVEL_ERROR);
            return false;
        }
/*
/usr/sbin/sogo-tool --help
2013-03-02 16:46:56.893 sogo-tool[5257] ERROR(+[GCSFolderManager defaultFolderManager]): default 'OCSFolderInfoURL' is not configured.
2013-03-02 16:46:56.904 sogo-tool[5257] sogo-tool [-v|--verbose] [-h|--help] command [argument1] ...
  -v, --verbose enable verbose mode
  -h, --help    display this help information

  argument1, ...        arguments passed to the specified command

  Available commands:
        backup              -- backup user folders
        check-doubles       -- check user addressbooks with duplicate contacts
        dump-defaults       -- Prints the sogod GNUstep domain configuration as a property list
        expire-autoreply    -- disable auto reply for reached end dates
        expire-sessions     -- Expires user sessions without activity for specified number of minutes
        remove              -- remove user data and settings from the db
        remove-doubles      -- remove duplicate contacts from the specified user addressbook
        rename-user         -- update records pertaining to a user after a change of user id
        restore             -- restore user folders
        user-preferences    -- set user defaults / settings in the database
*/
        \$dom_no_point = str_replace('.', '_', \$dom);
        \$app->db->query('DROP VIEW \`sogo_users_'.\$dom_no_point.'\`');
        return true;
    }
    
    function create_sogo_view(\$dom){
        global \$app, \$conf;
        \$dom_no_point = str_replace('.', '_', \$dom);
        \$sql1=\"SELECT \`TABLE_NAME\` FROM \`information_schema\`.\`VIEWS\` WHERE \`TABLE_SCHEMA\`='${ISPCONFIGDB}' AND \`TABLE_NAME\`='sogo_users_\".\$dom_no_point.\"'\";
        \$tmp = \$app->db->queryOneRecord(\$sql1);
        if(isset(\$tmp['TABLE_NAME']) && \$tmp['TABLE_NAME'] == 'sogo_users_'.\$dom_no_point){
            return true;
        }else{
            \$sql2=\"CREATE VIEW sogo_users_{\$dom_no_point}
AS SELECT
\`login\` AS c_uid,
\`login\` AS c_name,
\`password\` AS c_password,
\`name\` AS c_cn,
\`email\` AS mail,
(SELECT \`server_name\` FROM \`server\`,\`mail_user\` WHERE \`mail_user\`.\`server_id\`=\`server\`.\`server_id\` AND \`mail_server\`=1 LIMIT 1) AS imap_host
FROM \`mail_user\` WHERE \`email\` LIKE '%\$dom' AND disableimap='n';\";
            \$app->db->query(\$sql2);
            return true;
        }
    }

    function build_conf_sogo_maildomain(\$dom){
        global \$app, \$conf;
        \$dom_no_point = str_replace('.', '_', \$dom);
        \$sogo_conf = <<< EOF

                <key>\$dom</key>
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
                    <string>${SOGOLANGUAGE}</string>
                    <key>SOGoMailDomain</key>
                    <string>\$dom</string>
                    <key>SOGoSuperUsernames</key>
                    <array>
                        <string>postmaster@\$dom</string>
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
                            <string>Users in \$dom</string>
                            <key>hostname</key>
                            <string>localhost</string>
                            <key>id</key>
                            <string>\$dom_no_point</string>
                            <key>viewURL</key>
                            <string>mysql://{\$this->sogouser}:{\$this->sogopw}@127.0.0.1:3306/{\$this->sogodb}/sogo_users_{\$dom_no_point}</string>
                        </dict>
                    </array>
                </dict>
EOF;
        return \$sogo_conf;
    }

}
?>
">${ISPCONFIGINSTALLPATH}/server/plugins-available/sogo_config_plugin.php
### /usr/local/ispconfig/server/plugins-available/sogo_config_plugin.php
## configure sogo before restarting sogo service..

# enable the plugin..
ln -s ${ISPCONFIGINSTALLPATH}/server/plugins-available/sogo_config_plugin.php ${ISPCONFIGINSTALLPATH}/server/plugins-enabled/sogo_config_plugin.inc.php

echo "Allmost there wee just need to configure the vhost"
echo "."
echo -e "Domain vhost to configure [`hostname --fqdn`]: \c "
read SOGOVHOSTNAME
if [ -z "${SOGOVHOSTNAME}" ]; then
    SOGOVHOSTNAME=`hostname --fqdn`
fi
echo -e "HTTP Protocol: [https]\c "
read SOGOPROTOCAL
if [ -z "${SOGOPROTOCAL}" ]; then
    SOGOPROTOCAL="https"
fi
echo -e "HTTP Port: [80]\c "
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
               /usr/lib/GNUstep/SOGo/$1.SOGo/Resources/$2
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
echo -e "SOGo Bin:\t\t${SOGOBINARY}"
echo -e "SOGo-Tool Bin:\t\t${SOGOTOOLBINARY}"
echo -e "SOGo Home:\t\t${SOGOHOMEDIR}"
echo -e "SOGo Config:\t\t${SOGOGNUSTEPCONFFILE}"
echo -e "SOGo Init:\t\t${SOGOINITSCRIPT}"
#echo -e "DB:\t\t${SOGODB}"
echo -e "DB Name:\t\t${ISPCONFIGDB}"
echo -e "DB User:\t\t${SOGOUSERN}"
echo -e "DB Psswd:\t\t${SOGOUSERPW}"
echo -e ""
echo -e "Adminitrator is postmaster@DOMAIN.TLD"
echo -e "if postmaster mail addr is not added go add it and login to SOGo to start administrat the domain"
echo -e "Enable SOGo logins by update/delete or add a mail domain"
echo -e "----------------------------------------"

exit 1;








##### MySQL federated ####

MYSQL_FEDERATED_SQL = << EOF
CREATE TABLE IF NOT EXISTS \`ispc_servers\` (
  \`server_id\` int(11) unsigned NOT NULL AUTO_INCREMENT,
  \`sys_userid\` int(11) unsigned NOT NULL DEFAULT '0',
  \`sys_groupid\` int(11) unsigned NOT NULL DEFAULT '0',
  \`sys_perm_user\` varchar(5) NOT NULL DEFAULT '',
  \`sys_perm_group\` varchar(5) NOT NULL DEFAULT '',
  \`sys_perm_other\` varchar(5) NOT NULL DEFAULT '',
  \`server_name\` varchar(255) NOT NULL DEFAULT '',
  \`mail_server\` tinyint(1) NOT NULL DEFAULT '0',
  \`web_server\` tinyint(1) NOT NULL DEFAULT '0',
  \`dns_server\` tinyint(1) NOT NULL DEFAULT '0',
  \`file_server\` tinyint(1) NOT NULL DEFAULT '0',
  \`db_server\` tinyint(1) NOT NULL DEFAULT '0',
  \`vserver_server\` tinyint(1) NOT NULL DEFAULT '0',
  \`proxy_server\` tinyint(1) NOT NULL DEFAULT '0',
  \`firewall_server\` tinyint(1) NOT NULL DEFAULT '0',
  \`config\` text NOT NULL,
  \`updated\` bigint(20) unsigned NOT NULL DEFAULT '0',
  \`mirror_server_id\` int(11) unsigned NOT NULL DEFAULT '0',
  \`dbversion\` int(11) unsigned NOT NULL DEFAULT '1',
  \`active\` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (\`server_id\`)
) 
ENGINE=FEDERATED
DEFAULT CHARSET=utf8
CONNECTION='mysql://ispconfig:5bf4ef0201133d78c148fb4126d65768@127.0.0.1:3306/dbispconfig/server';

CREATE TABLE IF NOT EXISTS \`ispc_mailusers\` (
  \`mailuser_id\` int(11) unsigned NOT NULL AUTO_INCREMENT,
  \`sys_userid\` int(11) unsigned NOT NULL DEFAULT '0',
  \`sys_groupid\` int(11) unsigned NOT NULL DEFAULT '0',
  \`sys_perm_user\` varchar(5) NOT NULL DEFAULT '',
  \`sys_perm_group\` varchar(5) NOT NULL DEFAULT '',
  \`sys_perm_other\` varchar(5) NOT NULL DEFAULT '',
  \`server_id\` int(11) unsigned NOT NULL DEFAULT '0',
  \`email\` varchar(255) NOT NULL DEFAULT '',
  \`login\` varchar(255) NOT NULL,
  \`password\` varchar(255) NOT NULL,
  \`name\` varchar(255) NOT NULL DEFAULT '',
  \`uid\` int(11) unsigned NOT NULL DEFAULT '5000',
  \`gid\` int(11) unsigned NOT NULL DEFAULT '5000',
  \`maildir\` varchar(255) NOT NULL DEFAULT '',
  \`quota\` bigint(20) NOT NULL DEFAULT '-1',
  \`cc\` varchar(255) NOT NULL DEFAULT '',
  \`homedir\` varchar(255) NOT NULL,
  \`autoresponder\` enum('n','y') NOT NULL DEFAULT 'n',
  \`autoresponder_start_date\` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  \`autoresponder_end_date\` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  \`autoresponder_subject\` varchar(255) NOT NULL DEFAULT 'Out of office reply',
  \`autoresponder_text\` mediumtext,
  \`move_junk\` enum('n','y') NOT NULL DEFAULT 'n',
  \`custom_mailfilter\` mediumtext,
  \`postfix\` enum('n','y') NOT NULL,
  \`access\` enum('n','y') NOT NULL,
  \`disableimap\` enum('n','y') NOT NULL DEFAULT 'n',
  \`disablepop3\` enum('n','y') NOT NULL DEFAULT 'n',
  \`disabledeliver\` enum('n','y') NOT NULL DEFAULT 'n',
  \`disablesmtp\` enum('n','y') NOT NULL DEFAULT 'n',
  \`disablesieve\` enum('n','y') NOT NULL DEFAULT 'n',
  \`disablelda\` enum('n','y') NOT NULL DEFAULT 'n',
  \`disabledoveadm\` enum('n','y') NOT NULL DEFAULT 'n',
  PRIMARY KEY (\`mailuser_id\`),
  KEY \`server_id\` (\`server_id\`,\`email\`),
  KEY \`email_access\` (\`email\`,\`access\`)
)
ENGINE=FEDERATED
DEFAULT CHARSET=utf8
CONNECTION='mysql://ispconfig:5bf4ef0201133d78c148fb4126d65768@127.0.0.1:3306/dbispconfig/mail_user';

CREATE VIEW 
sogo_users_example_com
AS SELECT
\`ispc_mailusers\`.\`login\` AS c_uid,
\`ispc_mailusers\`.\`login\` AS c_name,
\`ispc_mailusers\`.\`password\` AS c_password,
\`ispc_mailusers\`.\`name\` AS c_cn,
\`ispc_mailusers\`.\`email\` AS mail,
(SELECT \`ispc_servers\`.\`server_name\` FROM \`ispc_servers\`, \`ispc_mailusers\` WHERE \`ispc_mailusers\`.\`server_id\`=\`ispc_servers\`.\`server_id\` AND \`ispc_servers\`.\`mail_server\`=1 LIMIT 1) AS imap_host
FROM \`ispc_mailusers\` WHERE \`ispc_mailusers\`.\`email\` LIKE '%example.com' AND \`ispc_mailusers\`.disableimap='n'
EOF












