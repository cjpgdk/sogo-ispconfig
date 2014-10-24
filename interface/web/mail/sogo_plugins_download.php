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
$_http_404_result = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
</body></html>';
$_http_401_result = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>401 Unauthorized</title>
</head><body>
<h1>Unauthorized</h1>
<p>Sorry no access to you, if you believe this is an error contact your hosting provider</p>
</body></html>';

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
//* this is currently the only security i can see that's needed, maybe i'm wrong.
if ($app->auth->get_user_id() > 0 && $app->auth->get_user_id() !== FALSE) {
    if (isset($_GET['pid']) && ((intval($_GET['pid']) > 0) && ((string) intval($_GET['pid']) == $_GET['pid']))) {
        require_once "list/sogo_plugins.list.php";
        $app = new app;
        $result = $app->db->queryOneRecord("SELECT * FROM `{$liste["table"]}` WHERE `{$liste["table_idx"]}`='" . intval($_GET['pid']) . "' AND `active`='y'");
        if (
                (isset($result['name']) && isset($result['file']) && isset($result['filetype'])) &&
                ($result['filetype'] == 'download' && file_exists($conf['sogo_plugins_upload_dir'] . "/" . $result['file']))) {
            //* file download
            $_file = $conf['sogo_plugins_upload_dir'] . "/" . $result['file'];
            $_file_name = basename($_file);
            $fp = @fopen($_file, 'rb');

            if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
                header('Content-Type: "application/octet-stream"');
                header('Content-Disposition: attachment; filename="' . $_file_name . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header("Content-Transfer-Encoding: binary");
                header('Pragma: public');
                header("Content-Length: " . filesize($_file));
            } else {
                header('Content-Type: "application/octet-stream"');
                header('Content-Disposition: attachment; filename="' . $_file_name . '"');
                header("Content-Transfer-Encoding: binary");
                header('Expires: 0');
                header('Pragma: no-cache');
                header("Content-Length: " . filesize($_file));
            }

            fpassthru($fp);
            fclose($fp);
            exit;
        } else if (
                (isset($result['name']) && isset($result['file']) && isset($result['filetype'])) &&
                ($result['filetype'] == 'link' && filter_var($result['file'], FILTER_VALIDATE_URL))) {
            //* link to file
            header('Location: ' . $result['file'], true, 302); //* use 302 in case we change it to a download from this server
            exit;
        } else {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
            header("Status: 404 Not Found");
            die($_http_404_result);
        }
    } else {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        header("Status: 404 Not Found");
        die($_http_404_result);
    }
} else {
    header($_SERVER["SERVER_PROTOCOL"] . " 401 Unauthorized");
    header("Status: 401 Unauthorized");
    die($_http_401_result);
}