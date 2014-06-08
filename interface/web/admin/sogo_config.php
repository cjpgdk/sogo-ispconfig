<?php

require_once('../../lib/config.inc.php');
require_once('../../lib/app.inc.php');
$list_def_file = "list/sogo_config.list.php";
$app->auth->check_module_permissions('admin');
$app->uses('listform_actions');

class lista extends listform_actions {

    public function onLoad() {
        global $app;
        $app->uses('tpl,listform,tform');
        if (isset($_GET['msg']) && !empty($_GET['msg'])) {
            $app->tpl->setVar('msg', $_GET['msg']);
        }
        parent::onLoad();
    }

}

$app->listform_actions = new lista();
$app->listform_actions->SQLExtWhere = " `mail_server`=1";
$app->listform_actions->SQLOrderBy = 'ORDER BY `server_name`';

$app->listform_actions->onLoad();
