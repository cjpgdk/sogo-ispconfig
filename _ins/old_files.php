<?php

$old_files = array(
    /* Server modules files */
    array(
        'type'=>'file',
        'file' => 'server/mods-enabled/sogo_module.inc.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file' => 'server/mods-available/sogo_module.inc.php',
        'action' => 'delete',
    ),
    /* Server pluigin files */
    array(
        'type'=>'file',
        'file' => 'server/plugins-enabled/sogo_config_plugin.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file' => 'server/plugins-enabled/sogo_config_plugin.inc.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file' => 'server/plugins-available/sogo_config_plugin.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file' => 'server/plugins-available/sogo_config_plugin.inc.php',
        'action' => 'delete',
    ),
    /* Server configs */
    array(
        'type'=>'file',
        'file'=>'server/conf/sogo.conf',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'server/conf/sogo-sogod.plist.conf',
        'action' => 'delete',
    ),
    array(
        'type'=>'folder',
        'folder'=>'server/conf/sogo_domains',
        'action' => 'delete',
    ),
    array(
        'type'=>'folder',
        'folder'=>'server/conf-custom/sogo/domains/',
        'action' => 'delete',
    ),
    array(
        'type'=>'folder',
        'folder'=>'server/conf-custom/sogo',
        'action' => 'delete',
    ),
    /* Interface files */
    array(
        'type'=>'file',
        'file'=>'interface/lib/classes/sogo_config.inc.php',
        'action' => 'delete',
    ),
    /* #ADMIN# */
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/sogo_thunderbird_plugins.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/sogo_config_rebuild.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/sogo_config_list.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/sogo_config_edit.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/sogo_config.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/templates/sogo_server_list.htm',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/templates/sogo_config_list.htm',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/templates/sogo_config_edit.htm',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/templates/ico/drill.png',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/templates/ico/download.png',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/templates/ico/download.png',
        'action' => 'delete',
    ),
    array(
        'type'=>'folder',
        'folder'=>'interface/web/admin/templates/ico',
        'action' => 'delete_if_empty',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/lib/menu.d/sogo.menu.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'folder',
        'folder'=>'interface/web/admin/lib/menu.d',
        'action' => 'delete_if_empty',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/lib/lang/en_sogo_config.lng',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/lib/lang/en_sogo_server_list.lng',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/lib/lang/en_sogo_config_list.lng',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/list/sogo_config.list.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/admin/form/sogo_config.tform.php',
        'action' => 'delete',
    ),
    /* #MAIL# */
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/lib/menu.d/sogo.menu.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'folder',
        'folder'=>'interface/web/mail/lib/menu.d',
        'action' => 'delete_if_empty',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/lib/lang/en_mail_sogo_domain_reseller_list.lng',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/lib/lang/en_mail_sogo_domain_list.lng',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/lib/lang/en_mail_sogo_domain_admin_list.lng',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/lib/lang/en_mail_sogo_domain.lng',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/lib/lang/mail_sogo_domain_reseller_list.lng',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/templates/mail_sogo_domain_reseller_list.htm',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/templates/mail_sogo_domain_reseller_edit.htm',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/templates/mail_sogo_domain_admin_edit.htm',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/templates/mail_sogo_domain_admin_list.htm',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/templates/mail_sogo_domain_list.htm',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/templates/mail_sogo_domain_edit.htm',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/list/mail_sogo_domain_list.list.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/form/mail_sogo_domain.tform.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/mail_sogo_domain_edit.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/mail_sogo_domain_list.php',
        'action' => 'delete',
    ),
    array(
        'type'=>'file',
        'file'=>'interface/web/mail/templates/sogo_domains_reseller_edit.htm',
        'action' => 'delete',
    ),
);