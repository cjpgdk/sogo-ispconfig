<?php
require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
$list_def_file = "list/sogo_config.list.php";

$app->auth->check_module_permissions('admin');

$app->uses('listform_actions');
$app->listform_actions->onLoad();