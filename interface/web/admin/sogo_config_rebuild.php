<?php
require_once('../../lib/config.inc.php');
require_once('../../lib/app.inc.php');

$app->auth->check_module_permissions('admin');
$msg="";
/*
 * sogo_config_rebuild.php?id=ALL
 */
function _fake_update_datalog($server_id,$change) {
        global $app;

        $diffstr = $app->db->quote(serialize(array('old'=>$change,'new'=>$change)));
        $app->db->query("INSERT INTO sys_datalog (dbtable,dbidx,server_id,action,tstamp,user,data) VALUES ('fake_tb_sogo','server_id:{$server_id}','{$server_id}','u','" . time() . "','{$app->db->quote($_SESSION['s']['user']['username'])}','{$diffstr}')");
    }
if (isset($_REQUEST['id'])) {
    switch (strtolower($_REQUEST['id'])) {
        case 'all':
            $query = "SELECT `server_id`,`server_name` FROM `server` WHERE `mail_server`=1;";
            break;
        default:
            $query = "SELECT `server_id`,`server_name` FROM `server` WHERE `mail_server`=1 AND `server_id`=" . intval($_REQUEST['id']) . ";";
            break;
    }
    $result = $app->db->queryAllRecords($query);
    foreach ($result as $key => $value) {
        if (isset($value['server_id'])) {
            $msg .= "Rebuild Task created for: {$value['server_name']}";
            _fake_update_datalog(intval($value['server_id']),array('server_id'=>intval($value['server_id'])));
        }
    }
}
header('Location: sogo_config.php?msg='.$msg);
exit;