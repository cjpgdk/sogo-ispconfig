<?php
require_once('../../lib/config.inc.php');
require_once('../../lib/app.inc.php');
$list_def_file = "list/sogo_thunderbird_plugins.list.php";
$app->auth->check_module_permissions('admin');
$app->uses('listform_actions');
class lista extends listform_actions {}
$app->listform_actions = new lista();
$app->listform_actions->onLoad();