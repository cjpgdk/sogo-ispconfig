<?php

//* SOGo config options.
if ($app->auth->get_client_limit($app->auth->get_user_id(), 'maildomain') != 0) {
    $items = array();
    $items[] = array('title' => 'Config',
        'target' => 'content',
        'link' => 'admin/sogo_config.php',
        'html_id' => 'mail_sogo_config');

    $items[] = array('title' => 'Domains',
        'target' => 'content',
        'link' => 'mail/mail_sogo_domain_list.php',
        'html_id' => 'mail_sogo_domain_list');

    $items[] = array('title' => 'Thunderbird Plugins',
        'target' => 'content',
        'link' => 'admin/sogo_thunderbird_plugins.php',
        'html_id' => 'mail_sogo_config');

    $module['nav'][] = array('title' => 'SOGo',
        'open' => 1,
        'items' => $items);
}