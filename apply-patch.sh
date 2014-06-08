#!/bin/sh

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo -e "is ISPConfig installed here [/usr/local/ispconfig]: \c "
read ISPCBASEDIR
if [ -z "${ISPCBASEDIR}" ]; then
    ISPCBASEDIR="/usr/local/ispconfig"
fi

echo -e "what ISPConfig version are we patching (304|3041|3042|3043|3044|3045|3046) [304]: \c "
read ISPCVERSION
if [ -z "${ISPCVERSION}" ]; then
    ISPCVERSION="304"
fi

if ! id ispconfig 1> /dev/null 2>&1; then
    echo -e "can't find system user 'ispconfig' enter the name thank you []: \c "
    read ISPCSYSTEMUSER
    if ! id ${ISPCSYSTEMUSER} 1> /dev/null 2>&1; then
        echo "User ${ISPCSYSTEMUSER} dossent exists.. bye bye"
        exit 1
    fi
else
    ISPCSYSTEMUSER="ispconfig"
fi

## PATCH ISPConfig 3.0.4
if [ "${ISPCVERSION}" == "304" ] || [ "${ISPCVERSION}" == "3041" ]; then
    # Admin module.conf.php
    echo "PATCHING: ${ISPCBASEDIR}/interface/web/admin/lib/module.conf.php"
    patch ${ISPCBASEDIR}/interface/web/admin/lib/module.conf.php < ${DIR}/patch-ispc304.admin.diff
    chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} ${ISPCBASEDIR}/interface/web/admin/lib/module.conf.php
    # Mail module.conf.php
    echo "PATCHING: ${ISPCBASEDIR}/interface/web/mail/lib/module.conf.php"
    patch ${ISPCBASEDIR}/interface/web/mail/lib/module.conf.php < ${DIR}/patch-ispc304.mail.diff
    chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} ${ISPCBASEDIR}/interface/web/mail/lib/module.conf.php
    echo "All done you should now be ready to use ISPConfig with SOGo thanks for using my script :)";
    ## fix some permissions..
    chmod 775 ${ISPCBASEDIR}/server/conf/sogo.conf
    chmod 775 -R ${ISPCBASEDIR}/server/conf/sogo_domains
    chmod 775 -R ${ISPCBASEDIR}/server/conf-custom/sogo/
    exit 0
elif [ "${ISPCVERSION}" == "3042" ] || [ "${ISPCVERSION}" == "3043" ] || [ "${ISPCVERSION}" == "3044" ] || [ "${ISPCVERSION}" == "3045" ] || [ "${ISPCVERSION}" == "3046" ]; then
    # Admin module.conf.php
    echo "PATCHING: ${ISPCBASEDIR}/interface/web/admin/lib/module.conf.php"
    patch ${ISPCBASEDIR}/interface/web/admin/lib/module.conf.php < ${DIR}/patch-ispc3042.admin.diff
    chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} ${ISPCBASEDIR}/interface/web/admin/lib/module.conf.php
    # Mail module.conf.php
    echo "PATCHING: ${ISPCBASEDIR}/interface/web/mail/lib/module.conf.php"
    patch ${ISPCBASEDIR}/interface/web/mail/lib/module.conf.php < ${DIR}/patch-ispc304.mail.diff
    chown ${ISPCSYSTEMUSER}:${ISPCSYSTEMUSER} ${ISPCBASEDIR}/interface/web/mail/lib/module.conf.php
    echo "All done you should now be ready to use ISPConfig with SOGo thanks for using my script :)";
    ## fix some permissions..
    chmod 775 ${ISPCBASEDIR}/server/conf/sogo.conf
    chmod 775 -R ${ISPCBASEDIR}/server/conf/sogo_domains
    chmod 775 -R ${ISPCBASEDIR}/server/conf-custom/sogo/
    exit 0
fi

