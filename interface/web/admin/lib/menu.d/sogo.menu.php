<?php

/*
 * Copyright (C) 2015  Christian M. Jensen
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
 * @author Christian M. Jensen <christian@cmjscripter.net>
 * @copyright 2014-2015 Christian M. Jensen
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

$module['nav'][] = array(
    'title' => 'SOGo',
    'open' => 1,
    'items' => array(
        array(
            'title' => 'Configuration',
            'target' => 'content',
            'link' => 'admin/sogo_conifg_list.php',
            'html_id' => 'sogo_conifg_list'
        ),
        array(
            'title' => 'Domains',
            'target' => 'content',
            'link' => 'admin/sogo_domains_list.php',
            'html_id' => 'sogo_domains_list'
        ),
        array(
            'title' => 'Settings',
            'target' => 'content',
            'link' => 'admin/sogo_module_settings_list.php',
            'html_id' => 'sogo_module_settings'
        ),
        array(
            'title' => 'Permissions',
            'target' => 'content',
            'link' => 'admin/sogo_config_permissions_list.php',
            'html_id' => 'sogo_config_permissions'
        ),
        array(
            'title' => 'Plugins',
            'target' => 'content',
            'link' => 'admin/sogo_plugins_list.php',
            'html_id' => 'sogo_plugins_list'
        ),
    )
);
