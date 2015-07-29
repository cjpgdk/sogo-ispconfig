<?php

require 'Debian.php';

$osInstallerName = "UbuntuInstaller";

/**
 * Copyright (C) 2015 Christian M. Jensen
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
 * @copyright 2015 Christian M. Jensen
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 * @link https://github.com/cmjnisse/sogo-ispconfig original source code for sogo-ispconfig
 * 
 */
class UbuntuInstaller extends DebianInstaller {

    public $os_name = "ubuntu";
    public $os_releases = array(
        'lucid' => 'lucid',
        'trusty' => 'trusty',
        'precise' => 'precise',
        'oneiric' => 'oneiric',
        'natty' => 'natty',
        'maverick' => 'maverick',
    );
    
    protected function debMirrors($os_release) {
        switch ($os_release) {
            case 'lucid':
                $mirror = "deb http://inverse.ca/ubuntu/ lucid main" . PHP_EOL;
                $mirror .= "#deb-src http://inverse.ca/ubuntu/ lucid main" . PHP_EOL . PHP_EOL;
                $mirror .= "#deb http://inverse.ca/ubuntu-nightly/ lucid main" . PHP_EOL;
                $mirror .= "#deb-src http://inverse.ca/ubuntu-nightly/ lucid main";
                return $mirror;
            case 'trusty':
            case 'precise':
            case 'oneiric':
            case 'natty':
            case 'maverick':
            default:
                $mirror = "deb http://inverse.ca/ubuntu/ {$os_release} {$os_release}" . PHP_EOL;
                $mirror .= "#deb-src http://inverse.ca/ubuntu/ {$os_release} {$os_release}" . PHP_EOL . PHP_EOL;
                $mirror .= "#deb http://inverse.ca/ubuntu-nightly/ {$os_release} {$os_release}" . PHP_EOL;
                $mirror .= "#deb-src http://inverse.ca/ubuntu-nightly/ {$os_release} {$os_release}";
                return $mirror;
        }
    }

}