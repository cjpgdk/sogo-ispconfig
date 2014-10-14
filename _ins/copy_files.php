<?php

$files_copy = array(
    'interface' => array(
        'lib/classes/sogo_helper.inc.php',
        //* #ADMIN#
        //* admin
        'web/admin/sogo_conifg_del.php',
        'web/admin/sogo_conifg_edit.php',
        'web/admin/sogo_conifg_list.php',
        'web/admin/sogo_conifg_rebuild.php',
        'web/admin/sogo_domains_del.php',
        'web/admin/sogo_domains_edit.php',
        'web/admin/sogo_domains_list.php',
        'web/admin/sogo_module_settings.php',
        //* form
        'web/admin/form/sogo_config.tform.php',
        'web/admin/form/sogo_domains.tform.php',
        'web/admin/form/sogo_module.tform.php',
        //* lng
        'web/admin/lib/lang/en_sogo_config.lng',
        'web/admin/lib/lang/en_sogo_conifg_list.lng',
        'web/admin/lib/lang/en_sogo_domains.lng',
        'web/admin/lib/lang/en_sogo_domains_list.lng',
        'web/admin/lib/lang/en_sogo_module.lng',
        //* menu
        'web/admin/lib/menu.d/sogo.menu.php',
        //* list
        'web/admin/list/sogo_domains.list.php',
        'web/admin/list/sogo_server.list.php',
        //* templates
        'web/admin/templates/sogo_config_custom_edit.htm',
        'web/admin/templates/sogo_config_domain_edit.htm',
        'web/admin/templates/sogo_config_edit.htm',
        'web/admin/templates/sogo_config_user_edit.htm',
        'web/admin/templates/sogo_conifg_list.htm',
        'web/admin/templates/sogo_domains_custom_edit.htm',
        'web/admin/templates/sogo_domains_domain_edit.htm',
        'web/admin/templates/sogo_domains_list.htm',
        'web/admin/templates/sogo_domains_user_edit.htm',
        'web/admin/templates/sogo_module_edit.htm',
    //* /#ADMIN#
    //* #MAIL#
    //* currently empty
    //* /#MAIL#
    ),
    'server' => array(
        'conf/sogo_domain.master',
        'lib/config.inc.local.sogo-sample.php',
        'lib/classes/sogo_config.inc.php',
        'lib/classes/sogo_helper.inc.php',
        'mods-available/sogo_module.inc.php',
        'plugins-available/sogo_plugin.inc.php',
    ),
);
