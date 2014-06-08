<?php
require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
$list_def_file = "list/mail_sogo_domain_list.list.php";
$app->auth->check_module_permissions('mail');
$app->uses('listform_actions');
//* do not show maildomain aliases
$app->listform_actions->SQLExtWhere =" CONCAT('@',mail_domain.domain) NOT IN (SELECT mail_forwarding.source FROM mail_forwarding WHERE mail_forwarding.active='y' AND mail_forwarding.type='aliasdomain')";
$app->listform_actions->onLoad();
?>