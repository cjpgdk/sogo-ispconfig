<?php

$items = array();

if($app->auth->get_client_limit($userid, 'maildomain') != 0){
    $items[] = array('title' => 'SOGo Config',
        'target' => 'content',
        'link' => 'admin/sogo_config.php',
        'html_id' => 'mail_sogo_config');
    
    $items[] = array('title' => 'SOGo Domains',
        'target' => 'content',
        'link' => 'mail/mail_sogo_domain_list.php',
        'html_id' => 'mail_sogo_domain_list');
}

if (count($items)) {
    $module['nav'][] = array('title' => 'SOGo',
        'open' => 1,
        'items' => $items);
}