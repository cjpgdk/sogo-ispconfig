<?php

class sogo_config {

    /**
     * Values available in column SOGoLanguage
     * @var array 
     */
    private $SOGoLanguageAvailable = array(
        'English' => 'English',
        'Arabic' => 'Arabic',
        'BrazilianPortuguese' => 'Brazilian (Portuguese)',
        'Catalan' => 'Catalan',
        'Czech' => 'Czech',
        'Danish' => 'Danish',
        'Dutch' => 'Dutch',
        'French' => 'French',
        'German' => 'German',
        'Hungarian' => 'Hungarian',
        'Icelandic' => 'Icelandic',
        'Italian' => 'Italian',
        'Polish' => 'Polish',
        'Russian' => 'Russian',
        'Slovak' => 'Slovak',
        'Swedish' => 'Swedish',
        'Ukrainian' => 'Ukrainian',
        'Welsh' => 'Welsh',
        'Finnish' => 'Finnish',
        'NorwegianBokmal' => 'Norwegian (Bokm&aring;l)',
        'NorwegianNynorsk' => 'Norwegian (Nynorsk)',
        'SpanishSpain' => 'Spanish (Spain)',
        'SpanishArgentina' => 'Spanish (Argentina)',
    );

    /**
     * Values available in column SOGoCalendarDefaultRoles
     * @var array 
     */
    private $SOGoCalendarDefaultRolesAvailable = array(
        'PublicViewer' => 'PublicViewer',
        'PublicDAndTViewer' => 'PublicDAndTViewer',
        'PublicModifer' => 'PublicModifer',
        'PublicResponder' => 'PublicResponder',
        'ConfidentialViewer' => 'ConfidentialViewer',
        'ConfidentialDAndTViewer' => 'ConfidentialDAndTViewer',
        'ConfidentialModifer' => 'ConfidentialModifer',
        'ConfidentialResponder' => 'ConfidentialResponder',
        'PrivateViewer' => 'PrivateViewer',
        'PrivateDAndTViewer' => 'PrivateDAndTViewer',
        'PrivateModifer' => 'PrivateModifer',
        'PrivateResponder' => 'PrivateResponder',
        'ObjectCreator' => 'ObjectCreator',
        'ObjectEraser' => 'ObjectEraser'
    );

    /**
     * Values available in column SOGoContactsDefaultRoles
     * @var array 
     */
    private $SOGoContactsDefaultRolesAvailable = array(
        'ObjectViewer' => 'ObjectViewer',
        'ObjectEditor' => 'ObjectEditor',
        'ObjectCreator' => 'ObjectCreator',
        'ObjectEraser' => 'ObjectEraser',
    );

    /**
     * Values available in Yes/No columns
     * @var array 
     */
    private $YesNoFields = array(
        'NO' => 'No',
        'YES' => 'Yes',
    );

    /**
     * Values available in SOGoMailingMechanism
     * @var array 
     */
    private $SOGoMailingMechanismFields = array(
        'smtp' => 'SMTP',
        'sendmail' => 'SendMail',
    );

    /**
     * Values available in SOGoMailMessageCheck
     * @var array 
     */
    private $SOGoMailMessageCheckFields = array(
        'once_per_hour' => 'once_per_hour',
        'every_30_minutes' => 'every_30_minutes',
        'every_20_minutes' => 'every_20_minutes',
        'every_10_minutes' => 'every_10_minutes',
        'every_5_minutes' => 'every_5_minutes',
        'every_2_minutes' => 'every_2_minutes',
        'every_minute' => 'every_minute',
        'manually' => 'manually',
    );

    /**
     * Values available in SOGoMailReplyPlacement
     * @var array 
     */
    private $SOGoMailReplyPlacementFields = array(
        'above' => 'Above',
        'below' => 'Below',
    );

    /**
     * Values available in SOGoDefaultCalendar
     * @var array 
     */
    private $SOGoDefaultCalendarFields = array(
        'selected' => 'Selected',
        'personal' => 'Personal',
        'first' => 'First',
    );

    /**
     * Values available in SOGoAuthenticationMethod
     * @var array 
     */
    private $SOGoAuthenticationMethodFields = array(
        'LDAP' => 'LDAP',
        'SQL' => 'MySQL/PostgreSQL',
    );

    /**
     * Values available in SOGoFirstWeekOfYear
     * @var array 
     */
    private $SOGoFirstWeekOfYearFields = array(
        'January1' => 'January 1',
        'First4DayWeek' => 'First 4 Day Week',
        'FirstFullWeek' => 'First Full Week',
    );
    /**
     * the main config.
     * @var string 
     */
    private $newconf = NULL;
    /**
     * the sogod.plist config.
     * @var string 
     */
    private $sogodplist = NULL;

    /**
     * Values available in SOGoFirstDayOfWeek
     * @var array 
     */
    private $SOGoFirstDayOfWeekFields = array(
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    );

    /**
     * Values available in SOGoLoginModule
     * @var array 
     */
    private $SOGoLoginModuleFields = array(
        'Calendar' => 'Calendar',
        'Mail' => 'Mail',
        'Contacts' => 'Contacts',
    );

    /** @var DOMDocument */
    private static $_DOMDocument = NULL;

    /**
     * write config file file to server
     * @param string $name name of the config to write
     * @return int the number of bytes written
     */
    public function writeConfig($name, $sogodplist = false) {
        //* we only write into custom dir.
        if ($sogodplist) {
            if (!isset($this->sogodplist) || empty($this->sogodplist) || is_null($this->sogodplist)) {
                return 0;
            }
            return file_put_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/{$name}", $this->sogodplist);
        } else {
            if (!isset($this->newconf) || empty($this->newconf) || is_null($this->newconf)) {
                return 0;
            }
            return file_put_contents(ISPC_ROOT_PATH . "/../server/conf-custom/sogo/{$name}", $this->newconf);
        }
    }

    /**
     * create the config layout
     * @param array $array
     * @return boolean
     * @todo som sort of walidation..
     */
    public function createConfig($array) {
        //* sogod.plist
        $this->sogodplist = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . PHP_EOL;
        $this->sogodplist .= "<!DOCTYPE plist PUBLIC \"-//GNUstep//DTD plist 0.9//EN\" \"http://www.gnustep.org/plist-0_9.xml\">" . PHP_EOL;
        $this->sogodplist .= "<plist version=\"0.9\">" . PHP_EOL;
        $this->sogodplist .= "\t<key>sogod</key>" . PHP_EOL;
        $this->sogodplist .= "\t<dict>" . PHP_EOL;

        //* sogo.conf
        $this->newconf = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . PHP_EOL
                . "<!DOCTYPE plist PUBLIC \"-//GNUstep//DTD plist 0.9//EN\" \"http://www.gnustep.org/plist-0_9.xml\">" . PHP_EOL
                . "<plist version=\"0.9\">" . PHP_EOL
                . "\t<dict>" . PHP_EOL
                . "\t\t<key>NSGlobalDomain</key>" . PHP_EOL
                . "\t\t<dict></dict>" . PHP_EOL
                . "\t\t<key>sogod</key>" . PHP_EOL
                . "\t\t<dict>" . PHP_EOL;
        foreach ($array['sogod'] as $key => $value) {
            //* we do not write empty values!
            if (!empty($value) && is_string($value)) {
                //* sogo.conf
                $this->newconf .= "\t\t\t<key>{$key}</key>" . PHP_EOL
                        . "\t\t\t<string>{$value}</string>" . PHP_EOL;
                //* sogod.plist
                $this->sogodplist .= "\t\t<key>{$key}</key>" . PHP_EOL
                        . "\t\t<string>{$value}</string>" . PHP_EOL;
            } else if (!empty($value) && is_array($value)) {
                //* sogo.conf
                $this->newconf .= "\t\t\t<key>{$key}</key>" . PHP_EOL
                        . "\t\t\t<array>" . PHP_EOL;
                //* sogod.plist
                $this->sogodplist .= "\t\t<key>{$key}</key>" . PHP_EOL
                        . "\t\t<array>" . PHP_EOL;
                foreach ($value as $k => $v) {
                    //* sogo.conf
                    $this->newconf .= "\t\t\t\t<string>{$v}</string>" . PHP_EOL;
                    //* sogod.plist
                    $this->sogodplist .= "\t\t\t<string>{$v}</string>" . PHP_EOL;
                }
                //* sogo.conf
                $this->newconf .= "\t\t\t</array>" . PHP_EOL;
                //* sogod.plist
                $this->sogodplist .= "\t\t</array>" . PHP_EOL;
            }
        }
        //* sogo.conf
        $this->newconf .= "\t\t\t<key>domains</key>" . PHP_EOL;
        $this->newconf .= "\t\t\t<dict>{{SOGODOMAINSCONF}}</dict>" . PHP_EOL;
        $this->newconf .= "\t\t</dict>" . PHP_EOL
                . "\t</dict>" . PHP_EOL
                . "</plist>" . PHP_EOL;

        //* sogod.plist
        $this->sogodplist .= "\t\t<key>domains</key>" . PHP_EOL;
        $this->sogodplist .= "\t\t<dict>{{SOGODOMAINSCONF}}</dict>" . PHP_EOL;
        $this->sogodplist .= "\t</dict>" . PHP_EOL;
        $this->sogodplist .= "</plist>" . PHP_EOL;

        return true;
    }

    /**
     * translate the strings!
     * @global app $app
     * @param type $param
     * @return type
     */
    private function runTranslate($param) {
        global $app;
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                if (!empty($app->tform->wordbook[$value . '_txt']))
                    $param[$key] = $app->tform->wordbook[$value . '_txt'];
                else
                    $param[$key] = $app->lng($value . '_txt');
            }
            return $param;
        }
        if (!empty($app->tform->wordbook[$param . '_txt']))
            return $app->tform->wordbook[$param . '_txt'];
        else
            return $app->lng($param . '_txt');
    }

    /**
     * get form field definition for the ui template
     * @global app $app
     * @param string $name
     * @param mixed $value
     * @return array
     */
    public function getISPConfigFormField($name, $value) {
        global $app;
        //* default and som required fileds
        $ret = array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => $value,
            'value' => $value,
            'maxlength' => '',
            'required' => 0,
            'width' => 100,
        );
        switch ($name) {
            case 'SOGoPasswordChangeEnabled':
            case 'SOGoMailUseOutlookStyleReplies':
            case 'SOGoMailAuxiliaryUserAccountsEnabled':
            case 'SOGoMailCustomFromEnabled':
            case 'SOGoEnableEMailAlarms':
            case 'SOGoACLsSendEMailNotifcations':
            case 'SOGoAppointmentSendEMailNotifcations':
            case 'SOGoAppointmentSendEMailReceipts':
            case 'SOGoFoldersSendEMailNotifcations':
            case 'WOUseRelativeURLs':
            case 'SOGoVacationEnabled':
            case 'SOGoSieveScriptsEnabled':
            case 'SOGoIMAPAclConformsToIMAPExt':
            case 'SOGoForwardEnabled':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->YesNoFields);
                break;
            case 'SOGoLoginModule':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->SOGoLoginModuleFields);
                break;
            case 'SOGoForceIMAPLoginWithEmail':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->YesNoFields);
                break;
            case 'SOGoLanguage':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->SOGoLanguageAvailable);
                break;
            case 'SOGoMailingMechanism':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->SOGoMailingMechanismFields);
                break;
            case 'SOGoMailMessageCheck':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->SOGoMailMessageCheckFields);
                break;
            case 'SOGoMailReplyPlacement':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->SOGoMailReplyPlacementFields);
                break;
            case 'SOGoDefaultCalendar':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->SOGoDefaultCalendarFields);
                break;
            case 'SOGoAuthenticationMethod':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->SOGoAuthenticationMethodFields);
                break;
            case 'SOGoFirstWeekOfYear':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->SOGoFirstWeekOfYearFields);
                break;
            case 'SOGoFirstDayOfWeek':
                $ret['datatype'] = 'INTEGER';
                $ret['formtype'] = 'SELECT';
                $ret['value'] = $this->runTranslate($this->SOGoFirstDayOfWeekFields);
                break;
            case 'SOGoMailListViewColumnsOrder':
                $ret['datatype'] = 'CUSTOMARRAY';
                $ret['formtype'] = 'CUSTOMFIELDSORTER';
                if (is_array($value))
                    $ret['default'] = implode(',', $value);
                $ret['value'] = implode(',', $value);
                break;
            case 'SOGoCalendarDefaultRoles':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'CHECKBOXARRAY';
                if (is_array($value))
                    $ret['default'] = implode(',', $value);
                $ret['value'] = $this->runTranslate($this->SOGoCalendarDefaultRolesAvailable);
                $ret['separator'] = ',';
//                $ret['validators'] = array(
//                array(
//                    'type' => 'CUSTOM',
//                    'class' => 'validate_sogo',
//                    'function' => 'isValidCalendarDefaultRolesField',
//                    'errmsg' => $app->lng('Calendar Default Roles can\'t be larger than 5'),
//                )
//            ),

                break;
            case 'SOGoContactsDefaultRoles':
                $ret['datatype'] = 'VARCHAR';
                $ret['formtype'] = 'CHECKBOXARRAY';
                if (is_array($value))
                    $ret['default'] = implode(',', $value);
                $ret['value'] = $this->runTranslate($this->SOGoContactsDefaultRolesAvailable);
                $ret['separator'] = ',';
//                $ret['validators'] = array(
//                array(
//                    'type' => 'CUSTOM',
//                    'class' => 'validate_sogo',
//                    'function' => 'isValidCalendarDefaultRolesField',
//                    'errmsg' => $app->lng('Calendar Default Roles can\'t be larger than 5'),
//                )
//            ),

                break;
            case 'domains':
                break;
            default:
                break;
        }
        return $ret;
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
