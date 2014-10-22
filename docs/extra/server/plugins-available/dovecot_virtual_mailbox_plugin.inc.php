<?php

/**
 * Copyright 2014, Christian M. Jensen
 * do what you want just keep the copyright thank you!
 
 * dovecot_virtual_mailbox_plugin
 * creates the folders need/specified in this file for use with dovecot virtual mailboxes
 * 
 * 
 * The following is required in dovecot config (ONLY tested this with dovecot 2+)
 * 
 * namespace {
 *  list = yes
 *  # this line is importent we store virtual mailbox in domain(%d) for eache user(%n) under Maildir/virtual
 *  # we use "." as separator so ":LAYOUT=maildir++" must be set othervize layout defaults to fs
 *  location = virtual:/var/vmail/%d/%n/Maildir/virtual:LAYOUT=maildir++
 *  # if you whant to use difrent index for virtual mail boxes use something like this.
 *  #location = virtual:/var/vmail/%d/%n/Maildir/virtual:INDEX=/var/vmail/%d/%n/Maildir/virtual:LAYOUT=maildir++
 * 
 *  mailbox All {
 *    auto = subscribe
 *  }
 *  mailbox Flagged {
 *    auto = subscribe
 *  }
 *  mailbox Sent {
 *    auto = subscribe
 *  }
 *  mailbox Trash {
 *    auto = subscribe
 *  }
 *  mailbox Unseen {
 *    auto = subscribe
 *  }
 *  # this can be any thing just like google uses "[Gmail]"
 *  prefix = [ISPCONFIG].
 *  separator = .
 *  subscriptions = yes
 *  type = private
 * }
 * protocol imap {
 *   mail_plugins = ...... virtual
 * }
 * protocol lda {
 *   mail_plugins = ...... virtual
 * }
 * 
 * 
 * 
 * 
 * @author Christian M. Jensen [cmjscripter.net]
 */
class dovecot_virtual_mailbox_plugin {

    var $plugin_name = 'dovecot_virtual_mailbox_plugin';
    var $class_name = 'dovecot_virtual_mailbox_plugin';

    /**
     * the virtual mailboxes to create<br />
     * Dont delete config for them only remove them from the arry if you dont like them<br />
     * you can allso just create your own like this
     * <pre><code>
     * var $virtual_mailboxes = array('MyCustomVirtualMailBox');
     * // Create a "dovecot-virtual" config file
     * // -- server/conf/dovecot_virtual_mailbox_mycustomvirtualmailbox.master
     * !INBOX
     * work/*
     *   unseen
     * work/*
     *   flagged
     * </code></pre>
     * @link http://wiki2.dovecot.org/Plugins/Virtual Dovecot Virtual mailbox plugin docs
     * @var array
     */
    var $virtual_mailboxes = array('All', 'Flagged', 'Sent', 'Trash', 'Unseen');
    var $_conf = array(
        //* the name of the virtual directory
        'virtual_dir' => 'virtual',
        //* the directory separator
        'dir_sep' => '.',
        /*
         * if templates are not found in [server/(conf|conf-custom)/...] we use these as default
         * default configs are automatic saved in server/conf/dovecot_virtual_mailbox_[MAIL_BOX_IN LOWER].master id they are not found
         */
        'virtual_dirs' => array(
            /* dovecot_virtual_mailbox_all.master */
            'All' => '*
  all',
            /* dovecot_virtual_mailbox_flagged.master */
            'Flagged' => '*
  flagged',
            /* dovecot_virtual_mailbox_sent.master */
            'Sent' => 'Sent*
  all',
            /* dovecot_virtual_mailbox_trash.master */
            'Trash' => '+Trash
+Trash/*
  all
*
  deleted',
            /* dovecot_virtual_mailbox_flagged.master */
            'Unseen' => '*
  unseen',
        ),
    );

    function onInstall() {
        return false;
    }

    /** @global app $app */
    function onLoad() {
        global $app;
        $app->plugins->registerEvent('mail_user_insert', $this->plugin_name, 'insert');
        $app->plugins->registerEvent('mail_user_update', $this->plugin_name, 'insert' /*'update'*/);
        //$app->plugins->registerEvent('mail_user_delete', $this->plugin_name, 'delete');
    }

    /**
     * methode bliver kaldt når event "mail_user_insert" er blevet sat til insert
     * @global app $app Class app() [server/lib/app.inc.php]
     * @global array $conf et array der indeholder serverens konfiguration (Den server vi bliver loaded på)
     * @param string $event_name en streng der forteller hvilken event der er blevet kaldt 
     * @param array $data et array af data der er blevet sendt med denne event [array('old'=>array(),'new'=>array())]
     */
    function insert($event_name, $data) {
        global $app, $conf;

        // get the config
        $app->uses('getconf,system');
        $mail_config = $app->getconf->get_server_config($conf['server_id'], 'mail');

        if ($mail_config['pop3_imap_daemon'] == 'dovecot') {

            //* tilpasset fra: server/plugins-available/mail_plugin.inc.php:~229
            $maildomain_path = $data['new']['maildir'];
            $tmp_basepath = $data['new']['maildir'];
            $tmp_basepath_parts = explode('/', $tmp_basepath);
            unset($tmp_basepath_parts[count($tmp_basepath_parts) - 1]);
            $base_path = implode('/', $tmp_basepath_parts);
            if (!empty($base_path) && !is_dir($base_path)) {
                //exec("su -c 'mkdir -p ".escapeshellcmd($base_path)."' ".$mail_config['mailuser_name']);
                $app->system->mkdirpath($base_path, 0700, $mail_config['mailuser_name'], $mail_config['mailuser_group']);
                $app->log('Created Directory: ' . $base_path, LOGLEVEL_DEBUG);
            }

            $maildomain_path .= '/Maildir/' . $this->_conf['virtual_dir'];

            if (!is_dir($maildomain_path)) {
                $app->system->mkdirpath($maildomain_path, 0700, $mail_config['mailuser_name'], $mail_config['mailuser_group']);
                $app->log('Created Directory: ' . $base_path, LOGLEVEL_DEBUG);
            }
            if (is_dir($maildomain_path)) {
                $app->load('tpl');
                foreach ($this->virtual_mailboxes as $vmbox) {
                    $tmp_dir = $maildomain_path . '/' . ($this->_conf['dir_sep'] == '.' ? '.' : '') . $vmbox;
                    if (!is_dir($tmp_dir)) {
                        $app->system->mkdirpath($tmp_dir, 0700, $mail_config['mailuser_name'], $mail_config['mailuser_group']);
                    }
                    if (!is_file($tmp_dir . '/dovecot-virtual')) {
                        $tmp_file = escapeshellcmd($tmp_dir . '/dovecot-virtual');
                        touch($tmp_file);

                        //* vis ingen default konfiguration er lave den ud fra settings i toppen
                        if (!file_exists("{$conf["rootpath"]}/conf/dovecot_virtual_mailbox_" . strtolower($vmbox) . ".master"))
                            file_put_contents("{$conf["rootpath"]}/conf/dovecot_virtual_mailbox_" . strtolower($vmbox) . ".master", isset($this->_conf['virtual_dirs'][$vmbox]) ? $this->_conf['virtual_dirs'][$vmbox] : '# empty file');

                        $tpl = new tpl();
                        $tpl->newTemplate('dovecot_virtual_mailbox_' . strtolower($vmbox) . '.master');
                        file_put_contents($tmp_file, $tpl->grab());
                        chmod($tmp_file, 0600);
                        chown($tmp_file, $mail_config['mailuser_name']);
                        chgrp($tmp_file, $mail_config['mailuser_group']);
                    }
                }
            }
        }
        return true;
    }

}
