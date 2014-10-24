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


//* SOGo config option.
if ($app->auth->get_client_limit($app->auth->get_user_id(), 'maildomain') != 0) {
    $items = array(
        /*array(
            'title' => 'Domains',
            'target' => 'content',
            'link' => 'mail/sogo_mail_domain_list.php',
            'html_id' => 'sogo_mail_domain_list'
        ),*/
        array(
            'title' => 'Plugins',
            'target' => 'content',
            'link' => 'mail/sogo_mail_plugins_list.php',
            'html_id' => 'sogo_mail_plugins_list'
        )
    );
    $module['nav'][] = array('title' => 'SOGo', 'open' => 1, 'items' => $items);
}