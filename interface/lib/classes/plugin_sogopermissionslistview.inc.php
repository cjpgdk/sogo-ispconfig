<?php

//* based on ./interface/lib/classes/plugin_listview
class plugin_sogopermissionslistview extends plugin_base {

    /** @global app $app */
    function onShow() {
        global $app;

        //* load list
        $app->uses('listform');
        $app->listform->loadListDef($this->options["listdef"]);

        //* load template
        $tpl = new tpl();
        $tpl->newTemplate('templates/' . $app->listform->listDef["name"] . '_list.htm');

        //* set some defaults
        $app->listform->listDef["file"] = $app->tform->formDef["action"];
        $app->listform->listDef["page_params"] = "&id=" . $this->form->id . "&next_tab=" . $_SESSION["s"]["form"]["tab"];
        $tpl->setVar('parent_id', $this->form->id);
        $tpl->setVar('theme', $_SESSION['s']['theme']);

        // load lng file
        $lng_file = "lib/lang/" . $_SESSION["s"]["language"] . "_" . $app->listform->listDef['name'] . "_list.lng";
        include $lng_file;
        $tpl->setVar($wb);

        //* load names map
        if (file_exists($this->options["record_list"]))
            require $this->options["record_list"];
        else
            $sogo_config_permissions_records_names = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);


        $global_permission = array();
        //* check if this is global or not, if not load global config
        $_index_sql = "SELECT * FROM `sogo_config_permissions_index` WHERE `scpi`=" . intval($this->options["permission_index"]);
        if ($_permission_index = $app->db->queryOneRecord($_index_sql)) {
            if ($_permission_index['scpi_is_global'] != 1) {
                //** not global load global
                if ($tmp = $app->db->queryOneRecord("SELECT * FROM `sogo_config_permissions_index` WHERE `scpi_is_global`=1 AND `scpi_type`='{$_permission_index['scpi_type']}'")) {
                    $global_permission = $app->db->queryAllRecords("SELECT * FROM " . $app->listform->listDef["table"] . " WHERE `scp_index`='{$tmp["scpi"]}'");
                    unset($tmp);
                }
            }
        }

        // load data
        $records = array();
        $_existing_records = array();
        $bgcolor = "#FFFFFF";
        if ($recs = $app->db->queryAllRecords("SELECT * FROM " . $app->listform->listDef["table"] . " WHERE {$this->options["sqlextwhere"]} {$this->options["sql_order_by"]}")) {

            $idx_key = $app->listform->listDef["table_idx"];
            foreach ($recs as $rec) {
                $_allowed = $rec["scp_allow"];
                $rec = $app->listform->decode($rec);
                $rec["scp_allowed"] = $_allowed;

                // set background color
                $bgcolor = ($bgcolor == "#FFFFFF") ? "#EEEEEE" : "#FFFFFF";
                $rec["bgcolor"] = $bgcolor;
                $rec["permission_index"] = $this->options["permission_index"];

                //* CODE From: ./interface/lib/classes/listform_tpl_generator.inc.php
                if (is_array($app->listform->listDef['item']) && count($app->listform->listDef['item']) > 0) {
                    foreach ($app->listform->listDef['item'] as $field) {
                        $key = $field['field'];
                        if (isset($field['formtype']) && $field['formtype'] == 'SELECT') {
                            if (strtolower($rec[$key]) == 'y' or strtolower($rec[$key]) == 'n') {
                                // Set a additional image variable for bolean fields
                                $rec['_' . $key . '_'] = (strtolower($rec[$key]) == 'y') ? 'x16/tick_circle.png' : 'x16/cross_circle.png';
                            }
                            //* substitute value for select field
                            $rec[$key] = @$field['value'][$rec[$key]];
                        }
                    }
                }
                //* END CODE From: ./interface/lib/classes/listform_tpl_generator.inc.php

                $rec["scp_name_format_txt"] = $rec["scp_name"];
                $rec["scp_name_desc_txt"] = "";
                if (isset($sogo_config_permissions_records_names) && $sogo_config_permissions_records_names instanceof ArrayObject) {
                    $rec["scp_name_format_txt"] = (isset($sogo_config_permissions_records_names->{$rec["scp_name"]}) ? $sogo_config_permissions_records_names->{$rec["scp_name"]} : $rec["scp_name"]);
                    $rec["scp_name_desc_txt"] = (isset($sogo_config_permissions_records_names->help_description[$rec["scp_name"]]) ? $sogo_config_permissions_records_names->help_description[$rec["scp_name"]] : '');
                }
                $_existing_records[] = $rec["scp_name"];
                $rec["id"] = $rec[$idx_key];
                $records[] = $rec;
            }
        }

        //* set all the missing permission records
        if (isset($sogo_config_permissions_records_names)) {
            foreach ($sogo_config_permissions_records_names as $key => $value) {
                //* ignore existing records
                if ($key == "help_description" || in_array($key, $_existing_records))
                    continue;

                $bgcolor = ($bgcolor == "#FFFFFF") ? "#EEEEEE" : "#FFFFFF";

                //* isset global values.
                $_is_global = false;
                //* bit of a roundabout but the quickest way to results
                foreach ($global_permission as $_gp_value) {
                    if ($_gp_value['scp_name'] == $key) {
                        $records[] = array(
                            'sys_userid' => 0, 'sys_groupid' => 0, 'sys_perm_user' => 'rui', 'sys_perm_group' => 'r', 'sys_perm_other' => 'r',
                            'scp' => -1,
                            'scp_index' => (count($records) > 0 ? $records[0]['scp_index'] : -1),
                            'scp_name' => $key,
                            'scp_allowed' => $_gp_value['scp_allow'],
                            'scp_allow' => ($_gp_value['scp_allow'] == 'y' ? '<div class="swap" id="ir-Yes"><span>Yes</span></div>' : '<div id="ir-No" class="swap"><span>No</span></div>'),
                            'scp_name_format_txt' => $value,
                            'scp_name_desc_txt' => (isset($sogo_config_permissions_records_names->help_description[$key]) ? $sogo_config_permissions_records_names->help_description[$key] : ''),
                            'id' => -1,
                            'permission_index' => $this->options["permission_index"],
                        );
                        $_is_global = true;
                        break;
                    }
                }
                if ($_is_global)
                    continue;

                //* not global set dummy.
                $records[] = array(
                    'sys_userid' => 0, 'sys_groupid' => 0, 'sys_perm_user' => 'rui', 'sys_perm_group' => 'r', 'sys_perm_other' => 'r',
                    'scp' => -1,
                    'scp_index' => (count($records) > 0 ? $records[0]['scp_index'] : -1),
                    'scp_name' => $key,
                    'scp_allowed' => 'n',
                    'scp_allow' => '<div id="ir-No" class="swap"><span>No</span></div>',
                    'scp_name_format_txt' => $value,
                    'scp_name_desc_txt' => (isset($sogo_config_permissions_records_names->help_description[$key]) ? $sogo_config_permissions_records_names->help_description[$key] : ''),
                    'id' => -1,
                    'permission_index' => $this->options["permission_index"],
                );
            }
        }

        $tpl->setVar('ajax_key', $_SESSION['s']['module']["_sogo_config_permissions_ajax_key"]);



        //* CODE From: ./interface/lib/classes/plugin_listview.inc.php
        $tpl->setLoop('records', $records);
        // Setting Returnto information in the session
        $list_name = $app->listform->listDef["name"];
        // $_SESSION["s"]["list"][$list_name]["parent_id"] = $app->tform_actions->id;
        $_SESSION["s"]["list"][$list_name]["parent_id"] = $this->form->id;
        $_SESSION["s"]["list"][$list_name]["parent_name"] = $app->tform->formDef["name"];
        $_SESSION["s"]["list"][$list_name]["parent_tab"] = $_SESSION["s"]["form"]["tab"];
        $_SESSION["s"]["list"][$list_name]["parent_script"] = $app->tform->formDef["action"];
        $_SESSION["s"]["form"]["return_to"] = $list_name;
        //die(print_r($_SESSION["s"]["list"][$list_name]));
        //* END CODE From: ./interface/lib/classes/plugin_listview.inc.php

        return $tpl->grab();
    }

}

?>
