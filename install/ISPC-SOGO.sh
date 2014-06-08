#!/bin/sh

#
# Install SOGo on debian/ubuntu/centos ISPConfig 3 server
#
# Update 7
#	 - removed the todo list i'm done this works on EVERY test i do, 
#	 - as fare as installing openchange setting up samba and openchange from a script will NOT be ideal..!
#
# Update 6
#    - inverse mirror for ubuntu lucid have difrent apt layout??! NOT (lucid lucid) BUT (lucid main)
#    - added checks to minimize dublicating of actions/content 
#        * if you tried to run the file once and fail then run it again that makes dublicats but not any more.
#     - removed install for ubuntu Hardy (8.04), Intrepid (8.10), Jaunty (9.04) and Karmic (9.10)
#         * install sogo before running this script
#
# Update 5.1
#   - renamed the script from ISPC-SOGO-debian.sh to ISPC-SOGO.sh
#
# Update 5
#   - select imap server config to use (Courier | Dovecot)
#   - added ubuntu support
#       JUST BE Aware.
#       quantal (12.10) will be installed using debian wheezy mirrors
#       Jaunty (9.04) and Karmic (9.10) will be installed using debian wheezy mirrors
#       Intrepid (8.10) will be installed using debian lenny mirrors (The same install as debian lenny build from source)
#   - added CentOS support ... thanks to howtoforge.com forum user: "lucaspr"
#       * read all about that here: http://www.howtoforge.com/forums/showthread.php?t=51162&page=5
#       - on centos check if we are on x86_64 or not and set apache vhost homedir to [/usr/lib64|/usr/lib]/GNUstep/SOGo/WebServerResources/
#   - added multi server support
#       option to create server config along with domain and default config.!
#   - Complete rewrite of the script ;()
#
# Update 4.1
#   - user password algorithm,, was selectable but not used in the script..!
#
# Update 4
#   - updated plugin 
#       create view for user-example.com is allow (dash is not allowed by mysql replace with _)
#       create view so user-example.com and example.com does not collate with each other by adding '@' to sql statement
#   - Debian lenny needs 'debhelper' added to install packages
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
# - (ISPConfig Minimum version 3.0.4)
#       older versions are not supported by the plugin
#       the databse table mail_user is not compatible with the current sqls,
#       May OR !!MAY NOT!! create a version check and added working sql i have not decided that yet.
#
# - (ISPConfig 3.0.5) -- the main thing to consider for using this with other versions of ISPConfig is the ""IMAP Server"" configuration..
#
# - OS
#       Debian Lenny (i386, amd64)
#       Debian Squeeze (i386, amd64)
#       CentOS 6.4 (i386, amd64)
#       CentOS 5.9 (i386)
#       Ubuntu quantal 12.10 (amd64)
#       Ubuntu precise 12.04 (i386)
#       Debian wheezy (amd64)
#
# - OS Setups
#       Debian Lenny: ISPConfig 3.0.5:  Apache2, BIND, Dovecot
#       Debian Squeeze: ISPConfig 3.0.5:  Apache2, BIND, Dovecot
#       Debian Lenny: ISPConfig 3.0.4:  Apache2, MyDNS, Courier 
#       CentOS 6.4: ISPConfig 3.0.5.1: Apache2, BIND, Courier
#       CentOS 6.4: ISPConfig 3.0.5.1: Apache2, BIND, Dovecot
#       Ubuntu quantal12.10: ISPConfig 3.0.5.1: Apache2, BIND, Courier
#       Ubuntu precise 12.04: ISPConfig 3.0.5.1: Apache2, BIND, Dovecot
#       CentOS 5.9: ISPConfig 3.0.5.1: Apache2, BIND, Dovecot
#       Debian wheezy: ISPConfig 3.0.5.2:  Apache2, BIND, Dovecot
#           
#
# Single server inviroment
#    - Multi domain, user sharing/ACL restricted to @DOMAIN.TLD
#       (User can't add other users ACLs unless allowed to by admin or other user.)
#    - Administrator of A domain is postmaster@DOMAIN.TLD
#    - ISPConfig plugin: 
#        i say BETA add/update/delete domain will trigger event to rebuild SOGo config.
#        - BuildConfig - 
#        only configure enabled domains :: OK
#        only allow IMAP enabled user to use SOGo :: OK
#        auto create sogo users view if none existing. :: OK
#        Remove SOGo view if domain is deleted :: OK
#        Remove user data from sogodb if user is deletet.. (if no data for user exists CRON log will show ''sogo-tool[nnn] No folder returned for user 'USER@DOMAIN.TLD') THATS not an error it just means the user never used sogo.. :: OK
#        
# Multi server inviroment --- this is not working to well.!
#   server1.example.dk (CentOS 6.4 i386 ISPConfig 3.0.5.1) :: apache2,dovecot,bind,pure-ftp,mysql
#   server2.example.dk (Debian 6 i386 ISPConfig 3.0.5) ::  apache2,bind,pure-ftp,mysql
#   server3.example.dk (Ubuntu 12.10 amd64 ISPConfig 3.0.5.1 <- FAILD to udate master server updated to "SVN[Sat Mar 23 14:52:17 CET 2013]") ::  apache2,courier,bind,pure-ftp,mysql
#    - Interface on server1
#    - SOGo on server1 (at this ponit SOGO have to be install on the interface server OR a server with a !!Complete COPY OF THE ISPConfig Database!! EVERY record in your system.!)
#    -- check list -- 
#       * plugin uses server defined configuration if exists else use default :: OK
#       * plugin uses domain defined configuration if exists else use default :: OK
#       * the sql view points to the corect imap server for a given domain :: OK
#       * -- Remove SOGo view if domain is deleted (regardless of server location) <<- dos not remove all unless plugin is on all mail servers
#       * -- Remove user data from sogodb if user is deleted (regardless of server location) <<- dos not remove all unless plugin is on all mail servers
#       * only configure enabled domains :: OK
#       * only allow IMAP enabled user to use SOGo :: OK
#       * create sogo users view if none existing (after mail domain [update / add or delete]) :HMM MISSING SOME THING: 
#       *
#        
# Bugs:
#    - in a multi server inviroment a sogo plugin needs to be pressent on all mail servers..!
#    - vhost may need tweeking before SOGo can be accessed (Default: /etc/apache2/conf.d/SOGo.conf)
#
# Common Problems:
#    -  imap folder layout PROBLEM if you use Courier-IMAP..
#           THE FIX IS TO: !!! OR change this file before running it..
#           NOTE* the folder change from 'Drafts' to 'Inbox.Drafts'
#
#               open [ISPConfig Install Path]/server/conf/sogo_domains/domains_default.conf
#               CHANGE THE KEY VARS: (At the top of the File)
#                   <key>SOGoDraftsFolderName</key>
#                   <string>Drafts</string>
#                   <key>SOGoSentFolderName</key>
#                   <string>Sent</string>
#                   <key>SOGoTrashFolderName</key>
#                   <string>Trash</string>
#               TO:
#                   <key>SOGoDraftsFolderName</key>
#                   <string>Inbox.Drafts</string>
#                   <key>SOGoSentFolderName</key>
#                   <string>Inbox.Sent</string>
#                   <key>SOGoTrashFolderName</key>
#                   <string>Inbox.Trash</string>
#               
#
# Refrenses
#    http://wiki.sogo.nu/CommonProblems
#    http://wiki.debian.org/SOGo
#    http://www.sogo.nu/english/downloads/documentation.html
#    http://www.sogo.nu/english/support/faq/article/how-to-install-sogo-and-sope-through-yum-1.html
#    ----
#    http://www.sogo.nu/english/downloads/frontends.html
#    http://www.sogo.nu/english/downloads/backend.html
#

## function inverse_debian
## ads inverse debian mirror to source.list
function inverse_debian() {
    if grep --ignore-case -q "deb http://inverse.ca/debian" "/etc/apt/sources.list"
    then
        echo "Debian inverse mirror already exists in /etc/apt/sources.list"
    else
        cat >> /etc/apt/sources.list << EOF
deb http://inverse.ca/debian ${1} ${1}
## deb http://inverse.ca/debian-nightly ${1} ${1}
EOF
    fi
}

## function inverse_debian_src
## ads inverse debian (SOURCE) mirror to source.list
function inverse_debian_src() {

    if grep --ignore-case -q "deb-src http://inverse.ca/debian" "/etc/apt/sources.list"
    then
        echo "Debian inverse source mirror already exists in /etc/apt/sources.list"
    else
        cat >> /etc/apt/sources.list << EOF
deb-src http://inverse.ca/debian ${1} ${1}
## deb-src http://inverse.ca/debian-nightly ${1} ${1}
EOF
    fi
}
## function inverse_ubuntu
## ads inverse ubuntu mirror to source.list
function inverse_ubuntu() {

    if grep --ignore-case -q "deb http://inverse.ca/ubuntu" "/etc/apt/sources.list"
    then
        echo "Ubuntu inverse mirror already exists in /etc/apt/sources.list"
    else
        cat >> /etc/apt/sources.list << EOF
deb http://inverse.ca/ubuntu ${1} ${1}
## deb http://inverse.ca/ubuntu-nightly ${1} ${1}
EOF
    fi
}
## function inverse_ubuntu_src
## ads inverse ubuntu mirror to source.list
function inverse_ubuntu_src() {
    if grep --ignore-case -q "deb-src http://inverse.ca/ubuntu" "/etc/apt/sources.list"
    then
        echo "Ubuntu inverse source mirror already exists in /etc/apt/sources.list"
    else
    cat >> /etc/apt/sources.list << EOF
deb-src http://inverse.ca/ubuntu ${1} ${1}
## deb-src http://inverse.ca/ubuntu-nightly ${1} ${1}
EOF
    fi
}
## function inverse_rhel6
## ads inverse RHEL6 mirror to /etc/yum.repos.d/SOGo.repo
function inverse_rhel6(){
    if [ ! -f /etc/yum.repos.d/SOGo.repo ]; then
        echo "Adding SOGo Mirrors to repos [/etc/yum.repos.d/SOGo.repo]"
        cat >> /etc/yum.repos.d/SOGo.repo << EOF
[sogo-Centos6]
name=Inverse SOGo Repository
baseurl=http://inverse.ca/downloads/SOGo/RHEL6/\$basearch
enabled=1
gpgcheck=0
EOF
    else
        echo "SOGo Mirror file /etc/yum.repos.d/SOGo.repo already exists"
    fi
}
## function inverse_centos5
## ads inverse CentOS5 mirror to /etc/yum.repos.d/SOGo.repo
function inverse_centos5(){
    if [ ! -f /etc/yum.repos.d/SOGo.repo ]; then
        echo "Adding SOGo Mirrors to repos [/etc/yum.repos.d/SOGo.repo]"
        cat >> /etc/yum.repos.d/SOGo.repo << EOF
[sogo-Centos5]
name=Inverse SOGo Repository
baseurl=http://inverse.ca/downloads/SOGo/CentOS5/\$basearch
enabled=1
gpgcheck=0
EOF
    else
        echo "SOGo Mirror file /etc/yum.repos.d/SOGo.repo already exists"
    fi
}
## function epel_exclude_gnustep
## ads exclude=gnustep-* to '[epel]' mirror
function epel_exclude_gnustep(){
    echo -e "epel.repo file [/etc/yum.repos.d/epel.repo]: \c "
    read EPELREPOFILE
    if [ -z "${EPELREPOFILE}" ]; then
        EPELREPOFILE="/etc/yum.repos.d/epel.repo"
    fi
    if [ -f ${EPELREPOFILE} ]; then
        cp ${EPELREPOFILE} ${EPELREPOFILE}.bak
        echo "Adding exclude=gnustep-* to '[epel]'"
        sed -i '/\[epel\]/ a\
exclude=gnustep-*' ${EPELREPOFILE}
    else
        echo "file ${EPELREPOFILE} not Found..!"
        sleep 5
    fi
}
function install_sogo_build_from_src(){
    local TMPWITCHBIN=`which sogod`
    if [ -z "${TMPWITCHBIN}" ]; then
        echo "Adding inverse gnupg keys from: keys.gnupg.net"
        apt-key adv --keyserver keys.gnupg.net --recv-key 0x810273C4  > /dev/null 2>&1
        echo "Update apt packages list ..."
        aptitude update > /dev/null 2>&1
        echo ".."
        echo "."
        echo "Building SOGo from source"
        sleep 3
        cd /tmp/
        aptitude install -y memcached rpl
        aptitude install -y debhelper dpkg-dev gobjc libgnustep-base-dev libsbjson2.3 libsope-appserver4.9-dev libsope-core4.9-dev libsope-gdl1-4.9-dev libsope-ldap4.9-dev libsope-mime4.9-dev libsope-xml4.9-dev libmemcached-dev libxml2-dev libsbjson-dev libssl-dev libcurl4-openssl-dev
        apt-get -y source sogo
        cd sogo-*
        dpkg-buildpackage -b
        echo "Installing tmpreaper, sogo, sope4.9-gdl1-mysql"
        aptitude install -y sope4.9-libxmlsaxdriver tmpreaper
        dpkg -i ../sogo_2*.deb
        aptitude install -y sope4.9-gdl1-mysql
    else
        echo "SOGo is installed..!"
    fi
}
## function install_sogo_debian_lenny
## installs sogo debian lenny
function install_sogo_debian_lenny(){
    local TMPWITCHBIN=`which sogod`
    if [ -z "${TMPWITCHBIN}" ]; then
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
        aptitude install -y debhelper dpkg-dev gobjc libgnustep-base-dev libsope-appserver4.9-dev libsope-core4.9-dev libsope-gdl1-4.9-dev libsope-ldap4.9-dev libsope-mime4.9-dev libsope-xml4.9-dev libmemcached-dev libxml2-dev libsbjson-dev libssl-dev libcurl4-openssl-dev
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
        echo "SOGo is installed..!"
    fi
}
## function removeing_warning_of_tmpreaper
## removes warning of tmpreaper
function removeing_warning_of_tmpreaper(){
    #### remove the warning of tmpreaper 
    echo "Removeing warning of tmpreaper"
    rpl 'SHOWWARNING=true' 'SHOWWARNING=false' /etc/tmpreaper.conf  > /dev/null 2>&1
}
## function memcached_deb_config
## configuration for memcached 
function memcached_deb_config(){
    #### memcached not happy w. IPv6
    echo "memcached not happy w. IPv6, settings to 127.0.0.1"
    rpl 'localhost' '127.0.0.1' /etc/memcached.conf  > /dev/null 2>&1
    /etc/init.d/memcached restart  > /dev/null 2>&1
}
## function memcached_centos_config
## configuration for memcached 
function memcached_centos_config(){
    if [ -f /etc/sysconfig/memcached ]; then
    cat > /etc/sysconfig/memcached << EOF
# Running on Port 11211
PORT="11211"
# Start as memcached daemon
USER="memcached"
# Set max simultaneous connections to 1024
MAXCONN="1024"
# Set Memory size to 128 - 4GB(4096)
CACHESIZE="128"
#Set server IP address
OPTIONS="-l 127.0.0.1"
EOF
    else
        echo "Unable to configure memcached file /etc/sysconfig/memcached not found.."
    fi
}
## function default_settings
## get all the default settings needed for this script to complete 
function default_settings(){

    echo -e "mysql host [127.0.0.1]: \c "
    read MYSQLHOST
    if [ -z "${MYSQLHOST}" ]; then
        MYSQLHOST="127.0.0.1"
    fi

    echo -e "mysql admin user [root]: \c "
    read MYSQLADMUSER
    if [ -z "${MYSQLADMUSER}" ]; then
        MYSQLADMUSER="root"
    fi

    echo -e "mysql root password []: \c "
    read MYSQLROOTPW

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

    echo -e "SOGO DB Username [sogodbuser]: \c "
    read SOGOUSERN
    if [ -z "${SOGOUSERN}" ]; then
        SOGOUSERN="sogodbuser"
    fi
    
    SOGODBTMPPASSWD=`< /dev/urandom tr -dc A-Za-z0-9_ | head -c25`
    echo -e "SOGO DB Username Password: [${SOGODBTMPPASSWD}]\c "
    read SOGOUSERPW
    if [ -z "${SOGOUSERPW}" ]; then
        SOGOUSERPW=${SOGODBTMPPASSWD}
    fi

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
    if [ -z "${SOGOBINARY}" ]; then
        echo -e "sogod binary not found where is it []: \c "
        read SOGOBINARY
    fi
    SOGOTOOLBINARY=`which sogo-tool`
    if [ -z "${SOGOTOOLBINARY}" ]; then
        echo -e "sogo-tool binary not found where is it []: \c "
        read SOGOTOOLBINARY
    fi
    SOGOHOMEDIR=$(getent passwd sogo | cut -d: -f6)
    if [ -z "${SOGOHOMEDIR}" ]; then
        echo -e "sogo home dir not found where is it []: \c "
        read SOGOHOMEDIR
    fi
    SOGOGNUSTEPCONFFILE=${SOGOHOMEDIR}/GNUstep/Defaults/.GNUstepDefaults
    if [ -z "${SOGOGNUSTEPCONFFILE}" ]; then
        echo -e "sogo GNUstep Defaults file not found where is it []: \c "
        read SOGOGNUSTEPCONFFILE
    fi
    SOGOZIPPATH=`which zip`

    if [ -f /etc/init.d/sogo ]; then
        SOGOINITSCRIPT=/etc/init.d/sogo
    elif [ -f /etc/init.d/sogod ]; then
        SOGOINITSCRIPT=/etc/init.d/sogod
    else
        echo -e "SOGo INIT script where not found where is it []: \c"
        read SOGOINITSCRIPT
    fi

    if ! id ispconfig 1> /dev/null 2>&1; then
        echo -e "can't find system user 'ispconfig' enter the name thank you []: \c "
        read ISPCSYSTEMUSER
    else
        ISPCSYSTEMUSER="ispconfig"
    fi

    echo -e "What imap server do you use? "
    echo -e "(if you have multi imap servers select one and check out ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/read_me)"
    echo -e "(dovecot/courier) [dovecot]: \c "
    read IMAPSERVERDEFAULT
    if [ -z "${IMAPSERVERDEFAULT}" ]; then
        IMAPSERVERDEFAULT="dovecot"
    fi

}
## function mysql_db
## creates mysql database to use for sogo and adds user with read permissions on ISPConfig database.
function mysql_db(){
    if [[ ! -z "`mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "SHOW DATABASES LIKE '${ISPCONFIGDB}';"; 2>&1`" ]];
    then
        if [[ ! -z "`mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "SHOW DATABASES LIKE '${SOGODB}';"; 2>&1`" ]];
        then
            echo "${SOGODB} DATABASE EXIST"
        else
            echo "CREATE DATABASE ${SOGODB}"
            mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "CREATE DATABASE IF NOT EXISTS ${SOGODB};";
            echo "CREATE USER ${SOGOUSERN}"
            mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "CREATE USER '${SOGOUSERN}'@'${MYSQLHOST}' IDENTIFIED BY '${SOGOUSERPW}';";
            echo "SET GRANTS for user ${SOGOUSERN}"
            mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "GRANT ALL PRIVILEGES ON \`${SOGODB}\`.* TO '${SOGOUSERN}'@'${MYSQLHOST}' WITH GRANT OPTION;";
            mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "GRANT SELECT ON \`${ISPCONFIGDB}\`.* TO '${SOGOUSERN}'@'${MYSQLHOST}';";
            echo "FLUSH PRIVILEGES"
            mysql -u ${MYSQLADMUSER} -h ${MYSQLHOST} -p${MYSQLROOTPW} -e "FLUSH PRIVILEGES;";
        fi
    else
      echo "${ISPCONFIGDB} DATABASE DOES NOT EXIST"
      exit 1
    fi

}





## function sogoconf_templs
## creates the sogo configuration template file. and templates directory
function sogoconf_templs(){
    if [ -f ${ISPCONFIGINSTALLPATH}/server/conf/sogo.conf-templ ]; then
        echo -e "A Template for sogo configuration EXIST OVERIDE IT? (y/n) [n]: \c"
        read LOCALOVERIDETMLP
        if [ -z "${LOCALOVERIDETMLP}" ]; then
            LOCALOVERIDETMLP="n"
        fi
    else
        LOCALOVERIDETMLP="y"
    fi
    
    if [ "${LOCALOVERIDETMLP}" == "y" ]; then
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
            <key>SOGoMemcachedHost</key>
            <string>127.0.0.1</string>
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
    fi
    chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} ${ISPCONFIGINSTALLPATH}/server/conf/sogo.conf-templ

    if [ ! -d "${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains" ]; then
        mkdir -p ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains
    fi
    
    sogo_domains_conf_dovecot
    sogo_domains_conf_courier
    
    if [ "${IMAPSERVERDEFAULT}" == "courier" ]; then
        cp ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/domains_default_courier.conf ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/domains_default.conf
    else
        cp ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/domains_default_dovecot.conf ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/domains_default.conf
    fi
    cat > ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/read_me << EOF
for how to configure the config file and the format(XML Format) of the files see SOGo documentation
the file names must be as follow (must end with .conf)
they are not automaticly created you need to do this by hand.

a template for domain example.com wil be named.
example.com.conf

domains_default.conf :: default for all domains..

domains_server1.example.dk.conf :: default for domains on: server1.example.dk
EOF

    chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} -R ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains
}
function sogo_domains_conf_dovecot_serverex(){
    cat > ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/domains_serverNnN.example.dk.conf << EOF

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
                            <string>${IMAPPWALGORITHM}</string>
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
}
## function sogo_domains_conf_dovecot
## creates the sogo configuration template file for default domains (dovecot)
function sogo_domains_conf_dovecot(){
    cat > ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/domains_default_dovecot.conf << EOF

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
                            <string>${IMAPPWALGORITHM}</string>
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
    sogo_domains_conf_dovecot_serverex
}
## function sogo_domains_conf_courier
## creates the sogo configuration template file for default domains (courier)
function sogo_domains_conf_courier(){
    cat > ${ISPCONFIGINSTALLPATH}/server/conf/sogo_domains/domains_default_courier.conf << EOF

                <key>{{DOMAIN}}</key>
                <dict>
                    <key>SOGoDraftsFolderName</key>
                    <string>Inbox.Drafts</string>
                    <key>SOGoSentFolderName</key>
                    <string>Inbox.Sent</string>
                    <key>SOGoTrashFolderName</key>
                    <string>Inbox.Trash</string>
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
                            <string>${IMAPPWALGORITHM}</string>
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
    sogo_domains_conf_dovecot_serverex
}
## function sogo_config_plugin
## creates the sogo plugin.
function sogo_config_plugin(){
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
 * NOTE* for more info, modifications etc.. contact me at http://www.howtoforge.com/forums my user name: 'psykosen'
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
            //* i can't work with that give me a command...
            // /PATH/to/ISPConfig_DIR/server/SOGO-reconfigure.log
            // file_put_contents('SOGO-reconfigure.log', print_r(\$event_name,true)."\n\n".print_r(\$data,true));
        }
        if (\$flag) {
            \$active_mail_domains = \$app->db->queryAllRecords('SELECT \`domain\`,\`server_id\` FROM \`mail_domain\` WHERE \`active\`=\'y\'');
            \$sogo_conf = file_get_contents(\$this->templ_file);
            \$tmp_conf = "";
            foreach (\$active_mail_domains as \$vd) {
                \$tmp_conf .= \$this->build_conf_sogo_maildomain(\$vd['domain'], \$vd['server_id']);
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

        \$dom_no_point = str_replace('-', '_', str_replace('.', '_', \$dom));
        \$sqlres = \$this->_sqlConnect();
        \$sqlres->query('DROP VIEW \`sogo_users_' . \$dom_no_point . '\`');
        if (file_exists("{\$this->templ_domains_dir}/{\$dom}.conf")) {
            @unlink("{\$this->templ_domains_dir}/{\$dom}.conf");
        }
        /* Broke my connection??? */
        /* @\$sqlres->close(); */
        return true;
    }

    function create_sogo_view(\$dom) {
        global \$app, \$conf;
        \$sqlres = \$this->_sqlConnect();

        \$dom_no_point = str_replace('-', '_', str_replace('.', '_', \$dom));
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
        FROM ' . \$this->ispcdb . '.\`mail_user\` AS ispcmu  WHERE \`email\` LIKE \'%@' . \$dom_no_point . '\' AND disableimap=\'n\'');
        if (!empty(\$sqlres->error))
            \$app->log('ERROR. unable to create SOGo view[sogo_users_' . \$dom_no_point . '].. ' . \$sqlres->error, LOGLEVEL_ERROR);
        /* Broke my connection??? */
        /* @\$sqlres->close(); */
        return true;
    }

    function build_conf_sogo_maildomain(\$dom, \$sid = 1) {
        global \$app, \$conf;
        \$dom_no_point = str_replace('-', '_', str_replace('.', '_', \$dom));
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
            '{{CONNECTIONVIEWURL}}' => "mysql://{\$this->sogouser}:{\$this->sogopw}@{\$this->mysql_server_host}/{\$this->sogodb}/sogo_users_{\$dom_no_point}"
        );
        if (file_exists("{\$this->templ_domains_dir}/{\$dom}.conf")) {
            \$sogo_conf = file_get_contents("{\$this->templ_domains_dir}/{\$dom}.conf");
        } else {
            \$server_name_result = \$app->db->queryOneRecord("SELECT \`server_name\` FROM \`server\` WHERE \`server_id\`=" . intval(\$sid));
            if (isset(\$server_name_result['server_name']) && !empty(\$server_name_result['server_name']) && file_exists("{\$this->templ_domains_dir}/{\$server_name_result['server_name']}.conf")) {
                \$sogo_conf = file_get_contents("{\$this->templ_domains_dir}/{\$server_name_result['server_name']}.conf");
            } else if (file_exists("{\$this->templ_domains_dir}/domains_default.conf")) {
                \$sogo_conf = file_get_contents("{\$this->templ_domains_dir}/domains_default.conf");
            } else {
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
            exit;
        }
        return \$sqlres;
    }

}

?>
EOF

    chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} ${ISPCONFIGINSTALLPATH}/server/plugins-available/sogo_config_plugin.php
    # enable the plugin..
    if [ ! -L "${ISPCONFIGINSTALLPATH}/server/plugins-enabled/sogo_config_plugin.inc.php" ]; then
        ln -s ${ISPCONFIGINSTALLPATH}/server/plugins-available/sogo_config_plugin.php ${ISPCONFIGINSTALLPATH}/server/plugins-enabled/sogo_config_plugin.inc.php
    fi

}

## function apache2_vhost
## creates the apache2 vhost config in [/etc/apache2/conf.d/SOGo.conf|/etc/httpd/conf.d/SOGo.conf]
function apache2_vhost(){

    echo "Allmost there wee just need to configure the vhost"
    echo "."
    echo -e "Continue with vhost config? (n/y) [y]: \c "
    read SOGOVHOSTCONIUE
    if [ -z "${SOGOVHOSTCONIUE}" ]; then
        SOGOVHOSTCONIUE="y"
    fi
    if [ "${SOGOVHOSTCONIUE}" == "y" ]; then
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
        OSARC=`uname -m`;
        VHOSTLIBDIR="/usr/lib";
        if [ "${OSTOCONF}" == "centos" ]; then
            if [ "${OSARC}" == "x86_64" ]; then
                VHOSTLIBDIR="/usr/lib64";
            fi
        fi
        echo -e "Apache2/Httpd config directory: [/etc/apache2/conf.d/]: \c "
        read HTTPDCONFDIR
        if [ -z "${HTTPDCONFDIR}" ]; then
            HTTPDCONFDIR="/etc/apache2/conf.d/"
        fi
        echo "
<VirtualHost *:${SOGOHTTPPORT}>
   Servername ${SOGOVHOSTNAME}:${SOGOHTTPPORT}
   DocumentRoot ${VHOSTLIBDIR}/GNUstep/SOGo/WebServerResources/
   ## ErrorLog /var/log/apache2/sogo-error.log
   ## Customlog /var/log/apache2/sogo-access.log combined
   ServerSignature Off
    Alias /SOGo.woa/WebServerResources/ \
          ${VHOSTLIBDIR}/GNUstep/SOGo/WebServerResources/
    Alias /SOGo/WebServerResources/ \
          ${VHOSTLIBDIR}/GNUstep/SOGo/WebServerResources/
    AliasMatch /SOGo/so/ControlPanel/Products/(.*)/Resources/(.*) \
               ${VHOSTLIBDIR}/GNUstep/SOGo/\$1.SOGo/Resources/\$2
    <Directory ${VHOSTLIBDIR}/GNUstep/SOGo/>
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
    # in ${VHOSTLIBDIR}/cgi-bin to reduce server overloading
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
"> ${HTTPDCONFDIR}SOGo.conf
    fi
}
## function inverse_debubun_install
## install sogo on debian/ubuntu
function inverse_debubun_install(){
    local TMPWITCHBIN=`which sogod`
    if [ -z "${TMPWITCHBIN}" ]; then
        echo "Adding inverse gnupg keys from: keys.gnupg.net"
        apt-key adv --keyserver keys.gnupg.net --recv-key 0x810273C4  > /dev/null 2>&1
        echo "Updateing apt packages list ..."
        aptitude update > /dev/null 2>&1
        echo "Installing sogo, sope4.9-gdl1-mysql, memcached, rpl"
        aptitude install -y sogo sope4.9-gdl1-mysql memcached rpl
    else
        echo "SOGo is installed.."
    fi
}
function os_no_install(){
    LOCALSOGOHOMEDIR=$(getent passwd sogo | cut -d: -f6)
    if [ -z "${LOCALSOGOHOMEDIR}" ]; then
        echo "Found SOGo in: ${LOCALSOGOHOMEDIR}"
        echo "Moving on.."
    else
        echo "Sorry to see you go like this. :(. install sogo with out this script and i will be happy to setup the rest for you.! :)"
        exit 1
     fi
}

#
# Start of the actual setup/configuration collection
#

echo -e "select distro name [debian|ubuntu|centos]: \c "
read OSTOCONF

if [ "${OSTOCONF}" == "debian" ]; then

    echo -e "select debian distro name [lenny|squeeze|wheezy]: \c "
    read DEBDISTRONAME
    inverse_debian ${DEBDISTRONAME}
    inverse_debian_src  ${DEBDISTRONAME}
    if [ "${DEBDISTRONAME}" == "lenny" ]; then
        install_sogo_debian_lenny
    else
        inverse_debubun_install
    fi
    
    removeing_warning_of_tmpreaper
    memcached_deb_config
    default_settings
    mysql_db
    sogoconf_templs
    sogo_config_plugin
    apache2_vhost
    
    ## final restart services 
    ${SOGOINITSCRIPT} restart
    a2enmod proxy proxy_http headers rewrite
    /etc/init.d/apache2 restart

elif [ "${OSTOCONF}" == "ubuntu" ]; then
    ##Lucid/10.04, Maverick/10.10, Natty/11.04, Oneiric/11.10 and Precise/12.04
    echo -e "...."
    echo -e ".."
    echo -e "quantal (12.10) will be installed using debian wheezy mirrors"
    echo -e "[quantal|precise|oneiric|natty|maverick|lucid|no_install]"
    echo -e "select ubuntu distro name: []: \c "
    read UBUNDISTRONAME
    if [ "${UBUNDISTRONAME}" == "no_install" ]; then
        os_no_install
#    elif [ "${UBUNDISTRONAME}" == "Precise" ]; then
#        inverse_debian 'wheezy'
#        inverse_debian_src 'wheezy'
#        inverse_debubun_install
    elif [ "${UBUNDISTRONAME}" == "quantal" ]; then
        inverse_debian 'wheezy'
        inverse_debian_src 'wheezy'
        inverse_debubun_install
    elif [ "${UBUNDISTRONAME}" == "lucid" ]; then
        cat >> /etc/apt/sources.list << EOF
deb http://inverse.ca/ubuntu lucid main
## deb http://inverse.ca/ubuntu-nightly lucid main
EOF
        cat >> /etc/apt/sources.list << EOF
deb-src http://inverse.ca/ubuntu lucid main
## deb-src http://inverse.ca/ubuntu-nightly lucid main
EOF
        inverse_debubun_install
    else
        inverse_ubuntu ${UBUNDISTRONAME}
        inverse_ubuntu_src  ${UBUNDISTRONAME}
        inverse_debubun_install
    fi
    
    removeing_warning_of_tmpreaper
    memcached_deb_config
    default_settings
    mysql_db
    sogoconf_templs
    sogo_config_plugin
    apache2_vhost
    
    ## final restart services 
    ${SOGOINITSCRIPT} restart
    a2enmod proxy proxy_http headers rewrite
    /etc/init.d/apache2 restart

elif [ "${OSTOCONF}" == "centos" ]; then

    echo -e "CentOS Base release (5/6) [6]: \c "
    read OSBASERELEASE
    if [ -z "${OSBASERELEASE}" ]; then
        OSBASERELEASE="6"
    fi

    if [ "${OSBASERELEASE}" == "6" ]; then
        inverse_rhel6
    elif [ "${OSBASERELEASE}" == "5" ]; then
        inverse_centos5
    else
        echo "Sorry unable to add repo for CentOS ${OSBASERELEASE}, valid versions are 5 or 6"
        exit 1;
    fi
    
    epel_exclude_gnustep
    echo "Updateing REPOs"
    yum -y update
    echo "Installing SOGo & SOGo-Tool"
    yum -y install sogo sogo-tool sope49-gdl1-mysql sope49-xml
    memcached_centos_config
    chkconfig --levels 235 memcached on
    /etc/init.d/memcached start

    default_settings
    mysql_db
    sogoconf_templs
    sogo_config_plugin
    apache2_vhost

    ## final restart services 
    ${SOGOINITSCRIPT} restart
    /etc/init.d/httpd restart

else
    echo "Distro not supported yet use debian"
    exit 1;
fi

echo -e "------------ SOGo Installed ------------"
echo -e "Web:\t\t${SOGOPROTOCAL}://${SOGOVHOSTNAME}:${SOGOHTTPPORT}/SOGo"
echo -e "VHOST Conf:\t\t${HTTPDCONFDIR}SOGo.conf"
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
echo -e "DB User:\t\t${SOGOUSERN}"
echo -e "DB Psswd:\t\t${SOGOUSERPW}"
echo -e ""
echo -e "Adminitrator is postmaster@DOMAIN.TLD"
echo -e "if postmaster mail addr is not added go add it and login to SOGo to start administrat the domain"
echo -e "Enable SOGo logins by update/delete or add a mail domain"
echo -e "----------------------------------------"
echo -e "REMEMBER THAT You need TO ADD/DELETE OR UPDATE a mail domain NOT A MAIL USER...!"
echo -e "before you can login to sogo.....!"
echo -e "----------------------------------------"

exit 0;


#echo "Installing openchangeserver for SOGo..."
#apt-get install -t squeeze-backports libwbclient-dev samba-common smbclient libsmbclient libsmbclient-dev
#apt-get update
#apt-get install samba4
#apt-get install openchangeserver sogo-openchange openchangeproxy openchange-ocsmanager openchange-rpcproxy




