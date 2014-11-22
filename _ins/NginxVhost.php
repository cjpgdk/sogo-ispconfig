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
if (!class_exists('VhostBase')) {
    require 'VhostBase.php';
}

class NginxVhost extends VhostBase {

    public static function Run() {
        self::$GnuStepDir = self::getSOGoGnuStepDir();
        echo PHP_EOL . "SOGo internaly listen on [" . self::$SOGoListenIpPort . "]: ";
        self::$SOGoListenIpPort = Installer::readInput(self::$SOGoListenIpPort);
        echo PHP_EOL . "Enable Microsoft ActiveSync (Y/N) [N]: ";
        self::$EnableMSActiveSync = (strtolower(Installer::readInput('n')) == 'n' ? '#' : '');
        if (self::SOGovHostConfig()) {
            die(PHP_EOL . PHP_EOL . "No vhost for SOGo is configured but i left the one that comes with SOGo you need to edit it, before you can access SOGo" . PHP_EOL . PHP_EOL);
        }
        
        echo PHP_EOL . "All done here select 'Y' to write the new configuration";
        echo PHP_EOL . "select 'N' to just print it so you can do the rest";
        echo PHP_EOL . "(Y/N) [Y]: ";
        if (strtolower(Installer::readInput('y')) == 'y') {
            //* write config file
            @file_put_contents("/etc/nginx/sites-available/SOGo.vhost", self::printCronfig());
            self::execWriteOut("ln -s /etc/nginx/sites-available/SOGo.vhost /etc/nginx/sites-enabled/");
            self::execWriteOut("/etc/init.d/nginx restart");
            echo PHP_EOL . PHP_EOL;
        } else {
            echo PHP_EOL . PHP_EOL . str_repeat('=', 12) . PHP_EOL . PHP_EOL . self::printCronfig() . PHP_EOL . PHP_EOL . str_repeat('=', 12) . PHP_EOL . PHP_EOL;
        }
    }

    public static function printCronfig() {
        return str_replace(array(
            '{SOGoHostPort}',
            '{GnuStepDir}',
            '{SOGoListenIpPort}',
            '{EnableMSActiveSync}',
            '{SOGoHostname}',
            '{SOGoServerURL}'
                ), array(
            self::$SOGoHostPort,
            self::$GnuStepDir,
            self::$SOGoListenIpPort,
            self::$EnableMSActiveSync,
            self::$SOGoHostname,
            self::$SOGoServerURL
                ), self::$tpl);
    }

    /**
     * this template is based on default settings from SOGo install package
     * @var string 
     */
    static $tpl = <<< EOF
#server
#{
#   listen      80 default;
#   server_name {SOGoHostname};
#   ## redirect http to https ##
#   rewrite        ^ `https://\$server_name\$request_uri?` permanent; 
#}
server
{
   listen {SOGoHostPort};
   server_name {SOGoHostname}; 
   root {GnuStepDir}/WebServerResources/; 
   #ssl on;
   #ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
   #ssl_certificate /usr/local/ispconfig/interface/ssl/ispserver.crt;
   #ssl_certificate_key /usr/local/ispconfig/interface/ssl/ispserver.key;
   ## requirement to create new calendars in Thunderbird ##
   proxy_http_version 1.1;
   
   location = / {
      rewrite ^ `http://\$server_name/SOGo`; 
      allow all; 
   }
   # For IOS 7 

   location = /principals/ {
      rewrite ^ `http://\$server_name/SOGo/dav`; 
      allow all; 
   }
   location ^~/SOGo {
      proxy_pass `http://{SOGoListenIpPort}`; 
      proxy_redirect `http://{SOGoListenIpPort}` default; 
      # forward user's IP address 
      proxy_set_header X-Real-IP \$remote_addr; 
      proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for; 
      proxy_set_header Host \$host; 
      proxy_set_header x-webobjects-server-protocol HTTP/1.0; 
      proxy_set_header x-webobjects-remote-host 127.0.0.1; 
      proxy_set_header x-webobjects-server-name \$server_name; 
      proxy_set_header x-webobjects-server-url \$scheme://\$host; 
      proxy_set_header x-webobjects-server-port \$server_port; 
      proxy_connect_timeout 90;
      proxy_send_timeout 90;
      proxy_read_timeout 90;
      proxy_buffer_size 4k;
      proxy_buffers 4 32k;
      proxy_busy_buffers_size 64k;
      proxy_temp_file_write_size 64k;
      client_max_body_size 50m;
      client_body_buffer_size 128k;
      break;
   }
   {EnableMSActiveSync}location ^~/Microsoft-Server-ActiveSync {
   {EnableMSActiveSync}   proxy_pass http://{SOGoListenIpPort}/SOGo/Microsoft-Server-ActiveSync;
   {EnableMSActiveSync}   proxy_redirect http://{SOGoListenIpPort}/Microsoft-Server-ActiveSync /;
   {EnableMSActiveSync}}
   
   location /SOGo.woa/WebServerResources/ {
      alias {GnuStepDir}/WebServerResources/;
      allow all;
   }

   location /SOGo/WebServerResources/ {
      alias {GnuStepDir}/WebServerResources/; 
      allow all; 
   }

   location (^/SOGo/so/ControlPanel/Products/([^/]*)/Resources/(.*)$) {
      alias {GnuStepDir}/$1.SOGo/Resources/$2; 
   }

   location (^/SOGo/so/ControlPanel/Products/[^/]*UI/Resources/.*\.(jpg|png|gif|css|js)$) {
      alias {GnuStepDir}/$1.SOGo/Resources/$2; 
   }
}
EOF;

}
