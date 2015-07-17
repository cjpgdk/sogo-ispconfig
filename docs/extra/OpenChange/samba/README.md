### howto samba4, bind 9 and sssd(>= 1.10.0)
a not so detailed guide in setting up samba4 DC, bind 9 dns and sssd for local users

in this simple guide i use the following for my dc

```
Domain: EXAMPLE
Realm: example.com
Netbios name: EXAMPLEDC
Hostname: exampledc.example.com ## This is the fqdn of the server

Administrator password: Pa$$WorD
```


Stop samba
```
/etc/init.d/samba stop
ps aux | grep samba
# still running
killall samba
```

save this script as `samba_provision.sh`

```
#
# USAGE:
#   ./samba_provision realm domain netbios adminpass
# eg.
#   ./samba_provision example.com EXAMPLE EXAMPLEDC 'Pa$\-$WorD'
#

OPTIONS=""
REALM=$1
DOMAIN_NAME=$2
NETBIOS_NAME=$3
SERVER_STRING="$REALM dc server"
ADMIN_PASSWORD=$4
DNS_TYPE=BIND9_DLZ
OPTIONS+=" --option='server services = -dns'"

#OPTIONS+=" --host-ip=172.16.10.1 --option='interfaces = lo, vmbr0' --option='bind interfaces only = Yes'"
OPTIONS+=" -use-xattr=yes --use-rfc2307 --option='idmap_ldb:use rfc2307 = yes'"

CONFIGURE_CMD=" --domain=$DOMAIN_NAME --dns-backend=$DNS_TYPE"
CONFIGURE_CMD+=" --server-role=dc --function-level=2008_R2"
CONFIGURE_CMD+=" --realm=$REALM"
CONFIGURE_CMD+=" --option='netbios name = $NETBIOS_NAME'"
CONFIGURE_CMD+=" --option='server signing = auto'"
CONFIGURE_CMD+=" --option='dsdb:schema update allowed = yes'"
CONFIGURE_CMD+=" --option='drs:max object sync = 1200'"
CONFIGURE_CMD+=" --option='log level = 3'"
CONFIGURE_CMD+=" --option='log file = /var/log/samba/samba.log'"
CONFIGURE_CMD+=" --option='max log size = 10000'"
CONFIGURE_CMD+=" --option='server string = $SERVER_STRING'"
CONFIGURE_CMD+=" --option='server role check:inhibit = yes'"
CONFIGURE_CMD+=" --adminpass=$ADMIN_PASSWORD $OPTIONS"

SMBTOOL=`which samba-tool`

$SMBTOOL domain provision $CONFIGURE_CMD

$SMBTOOL domain passwordsettings set --complexity=off
$SMBTOOL domain passwordsettings set --min-pwd-length=1
$SMBTOOL user setexpiry --noexpiry Administrator

exit 0
```

make the script executable

```
chmod +x samba_provision.sh
```

Run the script

```
./samba_provision.sh example.com EXAMPLE EXAMPLEDC 'Pa$$WorD'
```

open `/etc/samba/smb.conf` and add homes share

`vi /etc/samba/smb.conf`

```
[global]
    .....

    include = /etc/samba/shares.conf
    #include = /etc/samba/openchange.conf
```

create `/etc/samba/shares.conf`

`vi /etc/samba/shares.conf`

```
[homes]
    comment = Home Directories
    path = /home/%S
    read only = no
    browseable = no
    create mask = 0611
    directory mask = 0711
    vfs objects = acl_xattr full_audit
#    vfs objects = acl_xattr full_audit scannedonly recycle
    full_audit:success = connect opendir disconnect unlink mkdir rmdir open rename
    full_audit:failure = connect opendir disconnect unlink mkdir rmdir open rename
#    recycle: repository = .recycle
#    recycle: directory_mode = 0700
#    recycle: keeptree = Yes
#    recycle: inherit_nt_acl = Yes
#    recycle: versions = Yes
#    recycle: excludedir = /tmp|/var/tmp

# add the shares you whant here.
```

####Configure bind to use the samba dlz zone

set permissions for dns.keytab

```
chown bind:bind /var/lib/samba/private/dns
chgrp bind /var/lib/samba/private/dns.keytab
chmod g+r /var/lib/samba/private/dns.keytab
chmod 775 /var/lib/samba/private/dns
```

edit `/var/lib/samba/private/named.conf` and selected the correct dlz version for me it's 9.9.x

`vi /var/lib/samba/private/named.conf`

```
dlz "AD DNS Zone" {
    # For BIND 9.8.x
    # database "dlopen /usr/lib/x86_64-linux-gnu/samba/bind9/dlz_bind9.so";

    # For BIND 9.9.x
     database "dlopen /usr/lib/x86_64-linux-gnu/samba/bind9/dlz_bind9_9.so";

    # For BIND 9.10.x
    # database "dlopen /usr/lib/x86_64-linux-gnu/samba/bind9/dlz_bind9_10.so";
};
```

now include `/var/lib/samba/private/named.conf` in bind config

`vi /etc/bind/named.conf`

```
.....
include "/var/lib/samba/private/named.conf";
.....
```

now open `/etc/bind/named.conf.options` and add `tkey-gssapi-keytab "/var/lib/samba/private/dns.keytab"`

`vi /etc/bind/named.conf.options`

```
options {
        .....
        tkey-gssapi-keytab "/var/lib/samba/private/dns.keytab";
        .....
};
```

####Configure sssd(>= 1.10.0)


Export a keytab for sssd to use

```
samba-tool domain exportkeytab /etc/krb5.sssd.keytab --principal=EXAMPLEDC$
chown root:root /etc/krb5.sssd.keytab 
chmod 600 /etc/krb5.sssd.keytab
```

now create the sssd configuration 

`vi /etc/sssd/sssd.conf`

```
[sssd]
services = nss, pam
config_file_version = 2
domains = example.com

[nss]
    debug_level = 5

[pam]
    debug_level = 5

[domain/example.com]
    debug_level = 5
    enumerate = false

    id_provider = ad
    access_provider = ad

    ldap_schema = ad
    ldap_id_mapping=false

    ad_hostname = exampledc.example.com
    # ad_server = exampledc.example.com
    # ad_domain = example.com

    auth_provider = ad
    chpass_provider = ad

    dyndns_update = false
    krb5_keytab=/etc/krb5.sssd.keytab
    ldap_sasl_mech = gssapi
    ldap_krb5_init_creds = true

    override_homedir = /home/%u
    fallback_homedir = /home/%u

    shell_fallback = /bin/bash
    default_shell = /bin/bash
```


and your are done.. !!but before you start sssd!!

now start samba and bind, join a pc and use RSAT tools to configure UNIX attributes for you groubs and users, then start sssd 

the configuration in this guide expect POSIX attrbutes to be isset in the ad directory and sssd will not functon bere they are isset