<?php


//* SOGo config option.
if ($app->auth->get_client_limit($app->auth->get_user_id(), 'maildomain') != 0) {
    $items = array();
    $items[] = array('title' => 'SOGo Domains',
        'target' => 'content',
        'link' => 'mail/mail_sogo_domain_list.php',
        'html_id' => 'mail_sogo_domain_list');
    $module['nav'][] = array('title' => 'SOGo',
        'open' => 1,
        'items' => $items);
}