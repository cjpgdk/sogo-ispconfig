<?php

class UpdateClass extends PHPUpdateBaseClass {

    /**
     * method executed when updateing
     * @global db $db ISPConfig database object
     * @global string $ispchome ISPConfig install dir eg. /usr/local/ispconfig
     * @global array $conf ISPConfig config from "$ispchome . /server/lib/config.inc.php"
     * @global string $clientdb_host
     * @global string $clientdb_user
     * @global string $clientdb_password
     * return void
     */
    public function run() {
        global $db, $ispchome, $conf, $clientdb_host, $clientdb_user, $clientdb_password;

        //*## DB sogo_module update
        if ($conf['dbmaster_host'] != '' &&
                ($conf['dbmaster_host'] != $conf['db_host'] ||
                ($conf['dbmaster_host'] == $conf['db_host'] &&
                $conf['dbmaster_database'] != $conf['db_database']))) {
            /* not db master, so empty out table 'sogo_module', we start using 
              server based configs from update 7. */
            //$db->query("TRUNCATE sogo_module;"); //* not allowed with default user
            $sm = $db->queryAllRecords("SELECT * FROM `sogo_module`");
            foreach ($sm as $row)
                $db->query("DELETE FROM `sogo_module` WHERE `smid`={$row['smid']}");
        } else {
            $servers = array();
            //* db master, locate all sogo servers and set default module config
            $sogo_config = $db->queryAllRecords("SELECT `server_id`,`sogo_id` FROM `sogo_config`");
            foreach ($sogo_config as $value)
                if ((isset($value['sogo_id']) && isset($value['server_id'])) && (intval($value['sogo_id']) > 0 && intval($value['server_id']) > 0))
                    $servers[] = array('sogo_id' => $value['sogo_id'], 'server_id' => $value['server_id']);
            unset($sogo_config, $value);

            $sogo_module = $db->queryOneRecord("SELECT * FROM `sogo_module` WHERE `smid`=1");
            //$db->query("TRUNCATE sogo_module;"); //* not allowed with default user
            $sm = $db->queryAllRecords("SELECT * FROM `sogo_module`");
            foreach ($sm as $row)
                $db->query("DELETE FROM `sogo_module` WHERE `smid`={$row['smid']}");
            foreach ($servers as $value) {
                $sql = "INSERT INTO `sogo_module` "
                        . "(`smid`, `sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, "
                        . "`sys_perm_other`, `server_id`, `all_domains`, `allow_same_instance`, "
                        . "`config_rebuild_on_mail_user_insert`) VALUES "
                        . "(NULL, '{$sogo_module['sys_userid']}', '{$sogo_module['sys_groupid']}', "
                        . "'{$sogo_module['sys_perm_user']}', '{$sogo_module['sys_perm_group']}', "
                        . "'{$sogo_module['sys_perm_other']}', '{$value['server_id']}', "
                        . "'{$sogo_module['all_domains']}', '{$sogo_module['allow_same_instance']}', "
                        . "'{$sogo_module['config_rebuild_on_mail_user_insert']}');";
                $db->query($sql);
            }
        }
        //*/## DB sogo_module update
    }

}

$updateClass = new UpdateClass();
