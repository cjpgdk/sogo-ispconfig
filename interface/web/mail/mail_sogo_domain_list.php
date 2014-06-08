<?php
require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
$list_def_file = "list/mail_sogo_domain_list.list.php";
$app->auth->check_module_permissions('mail');
$app->uses('listform_actions');
$app->listform_actions->onLoad();
?>