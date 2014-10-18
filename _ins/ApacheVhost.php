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

class ApacheVhost {

    public static function getSOGoGnuStepDir($libdir = "/usr/lib") {
        if (file_exists("{$libdir}/GNUstep/SOGo/") || is_dir("{$libdir}/GNUstep/SOGo/")) {
            //* $libdir is set we must trut the user ´know what the hell he/she is doing
            $GnuStepDir = "{$libdir}/GNUstep/SOGo";
        } else if (file_exists("/usr/lib/GNUstep/SOGo/") || is_dir("/usr/lib/GNUstep/SOGo/")) {
            //* default (Debian && Ubuntu [32Bit & 64Bit])
            $GnuStepDir = "/usr/lib/GNUstep/SOGo";
        } else if (file_exists("/usr/lib64/GNUstep/SOGo/") || is_dir("/usr/lib64/GNUstep/SOGo/")) {
            //* seen on CentOS 64 bit
            $GnuStepDir = "/usr/lib64/GNUstep/SOGo";
        } else {
            $GnuStepDir = "/usr/lib/GNUstep/SOGo";
        }
        echo PHP_EOL . "SOGo Web Resources Dir [{$GnuStepDir}]: ";
        return Installer::readInput($GnuStepDir);
    }

    static $GnuStepDir = "/usr/lib/GNUstep/SOGo";
    static $SOGoListenIpPort = "127.0.0.1:20000";
    static $EnableMSActiveSync = "#";
    static $SOGoHostname = NULL;
    static $SOGoHostPort = "8080";
    static $SOGoServerURL = "https://server1.example.com:8080";

    public static function Run() {
        //* locate "usr/lib/GNUstep/SOGo"
        self::$GnuStepDir = self::getSOGoGnuStepDir();
        //* ProxyPass /SOGo http://127.0.0.1:20000/SOGo retry=0
        echo PHP_EOL . "SOGo internaly listen on [" . self::$SOGoListenIpPort . "]: ";
        self::$SOGoListenIpPort = Installer::readInput(self::$SOGoListenIpPort);
        # Microsoft-Server-ActiveSync 
        echo PHP_EOL . "Enable Microsoft ActiveSync (Y/N) [N]: ";
        self::$EnableMSActiveSync = (strtolower(Installer::readInput('n')) == 'n' ? '#' : '');
        self::SOGovHostConfig();
        if (self::$novConf) {
            die(PHP_EOL . "No vhost for SOGo is configured but i left the one that comes with SOGo you need to edit it, before you can access SOGo");
        }


        if (is_dir("/etc/apache2/conf-available/")) {
            $httpconfdir = "/etc/apache2/conf-available/";
        } else if (is_dir("/etc/httpd/conf.d/")) {
            $httpconfdir = "/etc/httpd/conf.d/";
        } else {
            $httpconfdir = "/etc/apache2/conf.d/";
        }
        exec("apache2ctl -v", $out);
        //Server version: Apache/2.2.22 (Debian)
        $apaversion = "2.2";
        if (preg_match("/Apache\/([0-9\.]+)/i", $out[0], $matches)) {
            if (isset($matches[1]))
                $apaversion = substr(trim($matches[1]), 0, 3);
        }
        unset($matches, $out);

        echo PHP_EOL . "All done here select 'Y' to write the new configuration";
        echo PHP_EOL . "select 'N' to just print it so you can do the rest";
        echo PHP_EOL . "(Y/N) [Y]: ";
        if (strtolower(Installer::readInput('y')) == 'y') {
            //* write config file
            @file_put_contents("{$httpconfdir}/SOGo.conf", self::printCronfig());

            $apachebin = exec('which apache2');
            if (empty($apachebin) || !file_exists($apachebin))
                $apachebin = exec('which httpd');

            if (empty($apachebin) || !file_exists($apachebin)) {
                echo PHP_EOL . "I can't locate apache2 or httpd binary where is it?: ";
                $apachebin = Installer::readInput('');
            }

            if (empty($apachebin) || !file_exists($apachebin))
                die(PHP_EOL . "No apache2 or httpd binary" . PHP_EOL . "*Here is the vhost config file i created, installed it manualy" . PHP_EOL . "{$httpconfdir}/SOGo.conf");

            exec('a2enmod proxy proxy_http headers rewrite', $out);
            if (isset($out) && is_array($out)) {
                foreach ($out as $value) {
                    echo PHP_EOL . $value;
                }
            } else {
                echo PHP_EOL . PHP_EOL . 'please verify the following modules for apache is enabled';
                echo PHP_EOL . 'a2enmod proxy proxy_http headers rewrite' . PHP_EOL;
            }
            if ($apaversion != "2.2") {
                exec('a2enconf SOGo', $out);
                if (isset($out) && is_array($out)) {
                    foreach ($out as $value) {
                        echo PHP_EOL . $value;
                    }
                }
            }
            if (file_exists("/etc/init.d/httpd"))
                $init = "/etc/init.d/httpd restart";
            else
                $init = "/etc/init.d/apache2 restart";

            exec($init, $out);
            if (isset($out) && is_array($out)) {
                foreach ($out as $value) {
                    echo PHP_EOL . $value;
                }
            }
            
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

    static $novConf = TRUE;

    public static function SOGovHostConfig() {

        //* SOGo vHost hostname
        $hostname = self::$SOGoHostname == null ? exec('hostname --fqdn') : self::$SOGoHostname;
        echo PHP_EOL . "SOGo Hostname [{$hostname}]: ";
        self::$SOGoHostname = Installer::readInput($hostname);
        //* SOGo port number
        echo PHP_EOL . "SOGo Host Port [" . self::$SOGoHostPort . "]: ";
        self::$SOGoHostPort = Installer::readInput(self::$SOGoHostPort);
        //* SOGo port number
        echo PHP_EOL . "Will you be using (http/https) [https]: ";
        $SOGoHostProto = Installer::readInput('https');
        self::$SOGoServerURL = "{$SOGoHostProto}://" . self::$SOGoHostname . ":" . self::$SOGoHostPort;
        echo PHP_EOL . "is this how you intend to access SOGo " . self::$SOGoServerURL . "/SOGo (Y/N) [Y]: ";
        if ((strtolower(Installer::readInput('y')) == 'n')) {
            echo PHP_EOL . "Okay would you like try again (Y/N) [Y]: ";
            if ((strtolower(Installer::readInput('y')) == 'n'))
                return;
            else {
                self::SOGovHostConfig();
                return;
            }
            self::$novConf = TRUE;
        }
        self::$novConf = FALSE;
    }

    /**
     * this template is based on default settings from SOGo install package
     * @var string 
     */
    static $tpl = <<< EOF
            
Alias /SOGo.woa/WebServerResources/ \
      {GnuStepDir}/WebServerResources/
Alias /SOGo/WebServerResources/ \
      {GnuStepDir}/WebServerResources/

<Directory {GnuStepDir}/>
    AllowOverride None

    <IfVersion < 2.4>
        Order deny,allow
        Allow from all
    </IfVersion>
    <IfVersion >= 2.4>
        Require all granted
    </IfVersion>

    # Explicitly allow caching of static content to avoid browser specific behavior.
    # A resource's URL MUST change in order to have the client load the new version.
    <IfModule expires_module>
      ExpiresActive On
      ExpiresDefault "access plus 1 year"
    </IfModule>
</Directory>

## Uncomment the following to enable proxy-side authentication, you will then
## need to set the "SOGoTrustProxyAuthentication" SOGo user default to YES and
## adjust the "x-webobjects-remote-user" proxy header in the "Proxy" section
## below.
#<Location /SOGo>
#  AuthType XXX
#  Require valid-user
#  SetEnv proxy-nokeepalive 1
#  Allow from all
#</Location>

ProxyRequests Off
SetEnv proxy-nokeepalive 1
ProxyPreserveHost On

# When using CAS, you should uncomment this and install cas-proxy-validate.py
# in /usr/lib/cgi-bin to reduce server overloading
#
# ProxyPass /SOGo/casProxy http://localhost/cgi-bin/cas-proxy-validate.py
# <Proxy http://localhost/app/cas-proxy-validate.py>
#   Order deny,allow
#   Allow from your-cas-host-addr
# </Proxy>

ProxyPass /SOGo http://{SOGoListenIpPort}/SOGo retry=0

# Enable to use Microsoft ActiveSync support
# Note that you MUST have many sogod workers to use ActiveSync.
# See the SOGo Installation and Configuration guide for more details.
{EnableMSActiveSync}ProxyPass /Microsoft-Server-ActiveSync http://{SOGoListenIpPort}/SOGo/Microsoft-Server-ActiveSync retry=60 connectiontimeout=5 timeout=360


<Proxy http://{SOGoListenIpPort}/SOGo>
## adjust the following to your configuration
  RequestHeader set "x-webobjects-server-port" "{SOGoHostPort}"
  RequestHeader set "x-webobjects-server-name" "{SOGoHostname}"
  RequestHeader set "x-webobjects-server-url" "{SOGoServerURL}"

## When using proxy-side autentication, you need to uncomment and
## adjust the following line:
#  RequestHeader set "x-webobjects-remote-user" "%{REMOTE_USER}e"

  RequestHeader set "x-webobjects-server-protocol" "HTTP/1.0"

  AddDefaultCharset UTF-8

  Order allow,deny
  Allow from all
</Proxy>

# For Apple autoconfiguration
<IfModule rewrite_module>
  RewriteEngine On
  RewriteRule ^/.well-known/caldav/?$ /SOGo/dav [R=301]
</IfModule>
            
EOF;

}
