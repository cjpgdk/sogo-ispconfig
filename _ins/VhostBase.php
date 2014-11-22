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

class VhostBase {

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

    protected static $GnuStepDir = "/usr/lib/GNUstep/SOGo";
    protected static $SOGoListenIpPort = "127.0.0.1:20000";
    protected static $EnableMSActiveSync = "#";
    protected static $SOGoHostname = NULL;
    protected static $SOGoHostPort = "8080";
    protected static $SOGoServerURL = "https://server1.example.com:8080";
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
                return self::$novConf;
            else {
                self::$novConf = self::SOGovHostConfig();
                return self::$novConf;
            }
            self::$novConf = TRUE;
        }
        self::$novConf = FALSE;
        return self::$novConf;
    }

    protected static function execWriteOut($cmd, & $out) {
        exec($cmd, $out);
        if (isset($out) && is_array($out)) {
            foreach ($out as $value) {
                echo PHP_EOL . $value;
            }
        }
        return $out;
    }

}
