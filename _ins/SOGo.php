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

class SOGo {

    public $source_list = "/etc/apt/sources.list"; 
    public $source_list_dir = "/etc/apt/sources.list.d/";
    public $os = "debian";
    public $os_name = "jessie";
    public $os_supported = array('debian', 'ubuntu');

    public function run() {
        if (!$this->getOS()) {
            $this->echoMessage($this->os . ' is not supported by this installer');
            return;
        }
        if (in_array($this->os, $this->os_supported)) {
            if (!file_exists($this->source_list)) {
                $this->echoMessage($this->source_list . ' is not found on you system ');
                return;
            }
            $sourcelist = file($this->source_list);
            $inverse_mirror = FALSE;
            foreach ($sourcelist as $value) {
                if (preg_match("/inverse\.ca/i", $value)) {
                    $this->echoMessage(PHP_EOL . "Not adding inverse mirror to your source list.!" . PHP_EOL);
                    $inverse_mirror = TRUE;
                    break;
                }
            }
            unset($sourcelist);
            if (!$inverse_mirror) {
                $mirror = $this->debMirrors();
                $this->echoMessage("Saving inverse mirrors for SOGO in ");
                $this->echoMessage("\t-" . $this->source_list_dir . 'inverse.mirror.list');
                file_put_contents($this->source_list_dir . 'inverse.mirror.list', $mirror);
                unset($mirror);
                $this->echoMessage("Adding apt key '0x810273C4' from keyserver keys.gnupg.net");
                echo exec('sudo apt-key adv --keyserver keys.gnupg.net --recv-key 0x810273C4');
                $this->echoMessage(PHP_EOL . "Updating apt package list");
                echo exec('sudo apt-get update');
            }
            unset($inverse_mirror);

            if (!Installer::isSOGoOnServer()) {
                $this->echoMessage(PHP_EOL . "About to install SOGo");
                $this->echoMessage("Shall i install sogo-activesync? (Y/N) [Y]: ", '');
                $pkg = "sogo sope4.9-gdl1-mysql memcached rpl";
                $pkg .= (strtolower(Installer::readInput("y")) == "y" ? " sogo-activesync" : "");
                echo exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $pkg);
                unset($pkg);
            } else {
                $this->echoMessage(PHP_EOL . "SOGo is installed nothing to do");
            }
            if (file_exists('/etc/tmpreaper.conf')) {
                $this->echoMessage("Removeing warnings from tmpreaper");
                $tmp = file_get_contents('/etc/tmpreaper.conf');
                $tmp = str_replace('SHOWWARNING=true', 'SHOWWARNING=false', $tmp);
                file_put_contents('/etc/tmpreaper.conf', $tmp);
                unset($tmp);
            }
            if (file_exists('/etc/memcached.conf')) {
                $this->echoMessage("memcached not happy w. IPv6, setting to 127.0.0.1");
                $tmp = file_get_contents('/etc/memcached.conf');
                $tmp = str_replace('localhost', '127.0.0.1', $tmp);
                file_put_contents('/etc/memcached.conf', $tmp);
                unset($tmp);
            } else {
                $this->echoMessage(PHP_EOL . "memcached config file, not found please make sure it's installed");
            }
            $this->echoMessage(PHP_EOL . "ALL DONE.. SOGo is now installed on your server..!" . PHP_EOL);
        }
    }

    public function getOS() {
        $this->echoMessage(PHP_EOL . "What Operating system are you using?");
        $this->echoMessage("debian, ubuntu [{$this->os}]: ", "");
        $this->os = strtolower(Installer::readInput("{$this->os}"));
        if (!in_array($this->os, $this->os_supported)) {
            return false;
        }
        return true;
    }

    public function echoMessage($msg, $ln = PHP_EOL) {
        echo $msg . $ln;
    }

    public function debMirrors() {
        if ($this->os == "debian") {
            $this->echoMessage(PHP_EOL . "Whats the name of your system?");
            $this->echoMessage("jessie, wheezy, squeeze, lenny [{$this->os_name}]: ", "");
            $this->os_name = strtolower(Installer::readInput("{$this->os_name}"));
            switch ($this->os_name) {
                case 'jessie':
                    return "deb http://inverse.ca/debian-nightly/ {$this->os_name} {$this->os_name}" . PHP_EOL . "#deb-src http://inverse.ca/debian-nightly/ {$this->os_name} {$this->os_name}";
                    break;
                case 'lenny':
                case 'squeeze':
                case 'wheezy':
                default:
                    return "deb http://inverse.ca/debian/ {$this->os_name} {$this->os_name}" . PHP_EOL . "#deb-src http://inverse.ca/debian/ {$this->os_name} {$this->os_name}";
                    break;
            }
        } else if ($this->os == "ubuntu") {
            echo PHP_EOL . $this->echoMessage("Whats the name of your system?");
            $this->echoMessage("lucid, maverick, natty, oneiric, precise, trusty [trusty]");
            $this->os_name = strtolower(Installer::readInput("trusty"));
            switch ($this->os_name) {
                case 'lucid':
                    return "deb http://inverse.ca/ubuntu/ lucid main" . PHP_EOL . "#deb-src http://inverse.ca/ubuntu/ lucid main";
                case 'trusty':
                case 'precise':
                case 'oneiric':
                case 'natty':
                case 'maverick':
                default:
                    return "deb http://inverse.ca/ubuntu/ {$this->os_name} {$this->os_name}" . PHP_EOL . "#deb-src http://inverse.ca/ubuntu/ {$this->os_name} {$this->os_name}";
                    break;
            }
        }
    }

}
