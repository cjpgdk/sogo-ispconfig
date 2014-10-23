<?php

/*
 * Copyright (C) 2014  Christian M. Jensen
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author Christian M. Jensen <christian@cmjscripter.net>
 *  @copyright 2014 Christian M. Jensen
 *  @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

$tform_def_file = "form/sogo_plugins.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

if ($conf['demo_mode'] == true)
    $app->error('This function is disabled in demo mode.');

$app->auth->check_module_permissions('admin');
if (method_exists($app->auth, 'check_security_permissions')) {
    $app->auth->check_security_permissions('admin_allow_server_services');
} else {
    if (!$app->auth->is_admin())
        die('only allowed for administrators.');
}
$app->uses('tpl,tform,functions');
$app->load('tform_actions');

class tform_action extends tform_actions {

    private $upload_ok = FALSE;
    public function onShowEnd() {
        global $app, $conf;
        if ($this->dataRecord['filetype'] == "download" && !file_exists("{$conf['sogo_plugins_upload_dir']}/{$this->dataRecord['file']}")) {
            $this->dataRecord['active']= 'n'; //* deactivate if file is not found
            $app->tpl->setVar('error', sprintf($app->tform->wordbook['download_file_missing'], "{$conf['sogo_plugins_upload_dir']}/{$this->dataRecord['file']}"));
        }
        parent::onShowEnd();
    }

    public function onBeforeInsert() {
        $this->upload_ok = $this->setFileName();
        parent::onBeforeInsert();
    }

    public function onAfterInsert() {
        parent::onAfterInsert();
        if ($this->upload_ok) {
            $_REQUEST["next_tab"] = ""; //* force redirect to list
        }
    }

    public function onBeforeUpdate() {
        $this->upload_ok = $this->setFileName(true);
        
        if ($this->dataRecord['filetype'] == "download" && !file_exists("{$conf['sogo_plugins_upload_dir']}/{$this->dataRecord['file']}")) {
            $this->dataRecord['active']= 'n';
            $app->tpl->setVar('error', sprintf($app->tform->wordbook['download_file_missing'], "{$conf['sogo_plugins_upload_dir']}/{$this->dataRecord['file']}"));
        }
        parent::onBeforeUpdate();
    }

    /**
     * 
     * @global app $app
     */
    public function onAfterUpdate() {
        global $app;
        parent::onAfterUpdate();
        if ($this->upload_ok) {
            $_REQUEST["next_tab"] = 'plugins';
            $app->tpl->setVar('msg', str_replace(array('{LINK}', '{/LINK}'), array('<a href="#" onclick="loadContent(\'admin/sogo_plugins_list.php\');">', '</a>'), $app->tform->wordbook['all_good_return']));
        }
    }

    //* upload file or set file name to http link
    private function setFileName($isupd = false) {

        global $app, $conf;
        if ($this->dataRecord['filetype'] == "download") {
            if (($isupd !== false && isset($_FILES['file_download']) && is_uploaded_file($_FILES['file_download']['tmp_name'])) || $isupd === false) {
                //* do file upload
                if (!is_dir($conf['sogo_plugins_upload_dir'])) {
                    //* create dirs if they do not exists
                    $_base_dir = str_replace(ISPC_ROOT_PATH, '', $conf['sogo_plugins_upload_dir']);
                    if (!empty($_base_dir)) {
                        $_base_dirs = explode('/', $_base_dir);
                        $_base_dir = ISPC_ROOT_PATH;
                        foreach ($_base_dirs as $value) {
                            $_base_dir .= "/{$value}";
                            if (!is_dir("{$_base_dir}"))
                                @mkdir("{$_base_dir}");
                        }
                    }
                }
                //* do an extra check here and isset errors we don't want invalid data in database
                if (!is_dir($conf['sogo_plugins_upload_dir'])) {
                    $app->tform->errorMessage .= $app->lng("I cannot upload files, upload dir is invalid");
                } else {
                    if (!isset($_FILES['file_download'])) {
                        $app->tform->errorMessage .= $app->lng("No input file selected for upload") . "<br>";
                    } else {
                        $file_name = basename($_FILES["file_download"]["name"]);
                        if (file_exists($conf['sogo_plugins_upload_dir'] . "/" . $file_name))
                            @unlink($conf['sogo_plugins_upload_dir'] . "/" . $file_name);
                        if (move_uploaded_file($_FILES["file_download"]["tmp_name"], $conf['sogo_plugins_upload_dir'] . "/" . $file_name)) {
                            $this->dataRecord['file'] = $file_name;
                        } else {
                            $app->tform->errorMessage .= $app->lng("Sorry, there was an error uploading your file.") . "<br>";
                        }
                    }
                }
            }
            if ($isupd !== false && !is_uploaded_file($_FILES['file_download']['tmp_name'])) {
                $this->dataRecord['file'] = $_POST['file_http'];
            }
        } else {
            $this->dataRecord['file'] = $this->dataRecord['file_http'];
        }
        return $app->tform->errorMessage == "" ? true : false;
    }

}

$app->tform_action = new tform_action();
$app->tform_action->onLoad();
