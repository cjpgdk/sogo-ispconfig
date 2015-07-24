new REQUIRED user source setting "ISPConfigUrlPassword", 
if not isset after change in SQLSource.* no one will be able to login

SQLSource.patch								: runs on the latest versions
SOGo-v2.3.0.ISPConfigUrlPassword.patch.diff	: runs on SOGo v2.3.0
SOGo-v2.3.1.ISPConfigUrlPassword.patch.diff	: runs on SOGo v2.3.1

patch -p1 < ../SQLSource.patch

[..]
viewURL = "mysql://..._users";
ISPConfigUrlPassword = "mysql://mysql-user:mysql-password@127.0.0.1:3306/dbispconfig/mail_user";
[..]

to set the setting.


edit "server/lib/config.inc.local.php"

[..]
$conf['sogo_domain_extra_vars'] = array(
    [..]
    'ISPConfigUrlPassword'=>'mysql://mysql-user:mysql-password@127.0.0.1:3306/dbispconfig/mail_user'
    [..]
);
[..]

and then

edit "server/conf/sogo_domain.master"

[..]
<key>SOGoUserSources</key>
<array>
    <dict>
        [..]
{tmpl_if name='ISPConfigUrlPassword'}
        <key>ISPConfigUrlPassword</key>
        <string>{tmpl_var name='ISPConfigUrlPassword'}</string>
{/tmpl_if}
        [..]
    </dict>
</array>
[..]


and just set SOGoPasswordChangeEnabled to YES
