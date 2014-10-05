<?php

/*
 * Copyright (C) 2014 Christian M. Jensen
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
 */

class sogo_config {

    /** @var DOMDocument */
    private static $_DOMDocument = NULL;

    /**
     * holder for sogo.conf
     * @var string
     */
    public $sogod = "";

    /**
     * holder for sogod.plist
     * @var string
     */
    public $sogodplist = "";
    
    /**
     * Bare fordi
     * @param boolean $die
     * @return string
     */
    public function printObject($die = FALSE) {
        if ($die) {
            die('<pre>' . print_r(self::$_DOMDocument, TRUE));
        }
        return print_r(self::$_DOMDocument, TRUE);
    }

    /**
     * create the config layout
     * @param array $array array('sogod'=>array( * FULL SOGO CONF HERE EXCLUDING THE DOMAINS CONFIG * )) !! NSGlobalDomain is written as empty
     * @return boolean
     * @todo som sort of validation..
     */
    public function createConfig($array) {
        //* sogod.plist
        $this->sogodplist = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . PHP_EOL
                . "<!DOCTYPE plist PUBLIC \"-//GNUstep//DTD plist 0.9//EN\" \"http://www.gnustep.org/plist-0_9.xml\">" . PHP_EOL
                . "<plist version=\"0.9\">" . PHP_EOL
                . "\t<key>sogod</key>" . PHP_EOL
                . "\t<dict>" . PHP_EOL;

        //* preserve the tabs "\t" makes it easy to debug the config file.
        //* sogo.conf
        $this->sogod = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . PHP_EOL
                . "<!DOCTYPE plist PUBLIC \"-//GNUstep//DTD plist 0.9//EN\" \"http://www.gnustep.org/plist-0_9.xml\">" . PHP_EOL
                . "<plist version=\"0.9\">" . PHP_EOL
                . "\t<dict>" . PHP_EOL
                . "\t\t<key>NSGlobalDomain</key>" . PHP_EOL
                . "\t\t<dict></dict>" . PHP_EOL
                . "\t\t<key>sogod</key>" . PHP_EOL
                . "\t\t<dict>" . PHP_EOL;
        $SOGoCustomXML = "";
        foreach ($array['sogod'] as $key => $value) {
//            if ($key == 'SOGoSubscriptionFolderFormat') {
//                $value = "";
//            }
            if ($key == 'SOGoCustomXML') {
                $SOGoCustomXML = $value;
                $this->escape_values($SOGoCustomXML);
                continue;
            }
            //* we do not write empty values!
            if (!empty($value) && is_string($value)) {
                $this->escape_values($value);
                //* sogo.conf
                $this->sogod .= "\t\t\t<key>{$key}</key>" . PHP_EOL
                        . "\t\t\t<string>{$value}</string>" . PHP_EOL;
                //* sogod.plist
                $this->sogodplist .= "\t\t<key>{$key}</key>" . PHP_EOL
                        . "\t\t<string>{$value}</string>" . PHP_EOL;
            } else if (!empty($value) && is_array($value)) {
                //* sogo.conf
                $this->sogod .= "\t\t\t<key>{$key}</key>" . PHP_EOL
                        . "\t\t\t<array>" . PHP_EOL;
                //* sogod.plist
                $this->sogodplist .= "\t\t<key>{$key}</key>" . PHP_EOL
                        . "\t\t<array>" . PHP_EOL;
                foreach ($value as $k => $v) {
                    $this->escape_values($v);
                    //* sogo.conf
                    $this->sogod .= "\t\t\t\t<string>{$v}</string>" . PHP_EOL;
                    //* sogod.plist
                    $this->sogodplist .= "\t\t\t<string>{$v}</string>" . PHP_EOL;
                }
                //* sogo.conf
                $this->sogod .= "\t\t\t</array>" . PHP_EOL;
                //* sogod.plist
                $this->sogodplist .= "\t\t</array>" . PHP_EOL;
            }
        }
        //* sogo.conf
        $this->sogod .= "\t\t\t{$SOGoCustomXML}" . PHP_EOL;
        $this->sogod .= "\t\t\t<key>domains</key>" . PHP_EOL;
        $this->sogod .= "\t\t\t<dict>{{SOGODOMAINSCONF}}</dict>" . PHP_EOL;
        $this->sogod .= "\t\t</dict>" . PHP_EOL
                . "\t</dict>" . PHP_EOL
                . "</plist>" . PHP_EOL;

        //* sogod.plist
        $this->sogodplist .= "\t\t\t{$SOGoCustomXML}" . PHP_EOL;
        $this->sogodplist .= "\t\t<key>domains</key>" . PHP_EOL;
        $this->sogodplist .= "\t\t<dict>{{SOGODOMAINSCONF}}</dict>" . PHP_EOL;
        $this->sogodplist .= "\t</dict>" . PHP_EOL;
        $this->sogodplist .= "</plist>" . PHP_EOL;

        return true;
    }

    private function escape_values(& $val) {
        $val = str_replace(
                array(
            '<',
            '>'
                ), array(
            '&lt;',
            '&gt;'
                ), $val);


        //* make sure key, plist, dict, array and string is not manipulated

        $val = str_replace(
                array(
            //* key
            '&lt;key&gt;',
            '&lt;/key&gt;',
            //* array
            '&lt;array&gt;',
            '&lt;/array&gt;',
            //* string
            '&lt;string&gt;',
            '&lt;/string&gt;',
            //* plist
            '&lt;plist&gt;',
            '&lt;/plist&gt;',
            //* dict
            '&lt;dict&gt;',
            '&lt;/dict&gt;', ), array(
            //* key
            '<key>',
            '</key>',
            //* array
            '<array>',
            '</array>',
            //* string
            '<string>',
            '</string>',
            //* plist
            '<plist>',
            '</plist>',
            //* dict
            '<dict>',
            '</dict>', ), $val);
    }

    /**
     * get the loaded sogod config into an array
     * @param string $section the section to return set NULL to return all
     * @return array
     */
    public function getConfigArray($section = NULL) {
        $retarr = array();
        if (self::$_DOMDocument != NULL) {
            $_retarr = $this->parse();
            if ($section == NULL) {
                return $_retarr;
            } else if (isset($_retarr["{$section}"])) {
                return $_retarr["{$section}"];
            }
        }
        return $retarr;
    }

    /**
     * load the SOGo config xml from string
     * @param string $conf
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function loadSOGoConfigString($conf) {
        self::$_DOMDocument = new DOMDocument();
        return self::$_DOMDocument->loadXML($conf);
    }

    /**
     * load the SOGo config xml from file
     * @param string $file
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function loadSOGoConfigFile($file) {
        self::$_DOMDocument = new DOMDocument();
        return self::$_DOMDocument->load($file);
    }

    /**
     * parse the SOGo config xml from file and return the output
     * @param string $file
     * @return array
     */
    public function parseSOGoConfigFile($file) {
        $document = new DOMDocument();
        $document->load($file);
        return $this->parse($document);
    }

    /**
     * parse a plist DOMDocument object into an array
     * @param DOMDocument $document
     * @return array
     */
    public function parse($document = NULL) {
        if ($document == NULL) {
            $document = self::$_DOMDocument;
        }
        $node = $document->documentElement;
        $root = $node->firstChild;
        while ($root->nodeName == "#text")
            $root = $root->nextSibling;
        return $this->_parse_node($root);
    }

    /**
     * pase a plist DOMNode
     * @param DOMNode $node
     * @return type
     */
    private function _parse_node($node) {
        $type = strtolower($node->nodeName);
        $method = '_parse_' . strtolower($type);
        if (method_exists($this, $method)) {
            return $this->$method($node);
        }
    }

    /**
     * parse plist array
     * @param DOMNode $node
     * @return array
     */
    private function _parse_array($node) {
        $array = array();
        for ($node = $node->firstChild; $node != null; $node = $node->nextSibling) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $array[] = $this->_parse_node($node);
            }
        }
        return $array;
    }

    /**
     * parse plist dict
     * @param DOMNode $node
     * @return array
     */
    private function _parse_dict($node) {
        $dict = array();
        for ($node = $node->firstChild; $node != null; $node = $node->nextSibling) {
            if ($node->nodeName == "key") {
                $key = $node->textContent;
                $node2 = $node->nextSibling;
                while ($node2->nodeType == XML_TEXT_NODE)
                    $node2 = $node2->nextSibling;
                $value = $this->_parse_node($node2);
                $dict[$key] = $value;
            }
        }
        return $dict;
    }

    /**
     * parse plist string
     * @param DOMNode $node
     * @return string
     */
    private function _parse_string($node) {
        return $node->textContent;
    }

}
