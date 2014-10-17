<?php
echo "No settings to alter yet!";
/*
  `smid` int(2) NOT NULL AUTO_INCREMENT,
  `sys_userid` int(11) unsigned NOT NULL,
  `sys_groupid` int(11) unsigned NOT NULL,
  `sys_perm_user` varchar(5) DEFAULT NULL,
  `sys_perm_group` varchar(5) DEFAULT NULL,
  `sys_perm_other` varchar(5) DEFAULT NULL,
  `all_domains` enum('y','n') NOT NULL DEFAULT 'y',
  `allow_same_instance` enum('y','n') NOT NULL DEFAULT 'y',
  `sql_of_mail_server` enum('y','n') NOT NULL DEFAULT 'n',

$tform_def_file = "form/sogo_module.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$app->auth->check_module_permissions('admin');
//* for this version (Update 10) only
if (method_exists($app->auth, 'check_security_permissions')) {
    //* we check if admin is allowed 
    $app->auth->check_security_permissions('admin_allow_server_services');
}
$app->uses('tpl,tform,functions');
$app->load('tform_actions');

$_REQUEST['id'] = 1; //* always 1, no use for multi settings ?yet

class tform_action extends tform_actions {
    
}

$page = new tform_action();
$page->onLoad();
 */