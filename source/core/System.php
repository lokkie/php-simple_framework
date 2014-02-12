<?php
/**
 * User: A.Rusakevich
 * Date: 08.08.13
 * Time: 13:34

 */

namespace core;

if (!defined('MAIN_CONF')) {
    define('MAIN_CONF', 'main.conf');
}

use core\data\cache\MemcacheDriver;
use core\data\cache\StaticData;
use core\data\ConfigIni;
use core\data\db\DbDriver;
use core\data\db\DbFactory;
use core\i18n\Lang;

/**
 * General core class realisation
 *
 * Helps to exchange between framework parts. Also works as singleton, System::instance() or System::_() return instance
 * @package core
 */
class System
{
    /**
     * @var System
     */
    protected static $_instance;

    /**
     * Returns instance of system (Singleton)
     * @param string $confFile
     * @return System
     * @see System::__construct()
     */
    public static function instance($confFile = MAIN_CONF)
    {
        if (self::$_instance === null) {
            self::$_instance = new System($confFile);
            self::$_instance->detectLanguage();
        }
        return self::$_instance;
    }

    /**
     * Returns instance of system (Singleton)
     *
     * Short alias for System::instance()
     * @param string $configFile
     * @return System
     * @see System::instance()
     * @see System::__construct()
     */
    public static function _($configFile = MAIN_CONF)
    {
        return self::instance($configFile);
    }

    /**
     * @var ConfigIni
     */
    protected $_cfg;
    /**
     * @var DbDriver
     */
    protected $_db;

    /**
     * @var MemcacheDriver
     */
    protected $_memcached;
    /**
     * @var array
     */
    protected $_request;
    /**
     * @var int|null
     */
    protected $_responseType = null;
    /**
     * @var Lang
     */
    protected $_lang;
    /**
     * @var string
     */
    protected $_currentLanguage;


    /**
     * @param string $cfgPath
     */
    function __construct($cfgPath)
    {
        $this->_cfg = new ConfigIni($cfgPath);
        $this->_parseUri();
        if ($this->_cfg->{'Memcached::KeyPrefix'} === null) {
            define('MC_PREFIX', '');
        } else {
            define('MC_PREFIX', $this->_cfg->{'Memcached::KeyPrefix'});
        }
        session_set_cookie_params(60 * 60 * 24 * 365);
        session_name($this->cfg()->{'Web::SessionCookie'});
        session_start();
    }

    /**
     * Returns link to DB instance
     * @return DbDriver
     */
    public function db()
    {
        if ($this->_db === null)
            $this->_initDb();
        return $this->_db;
    }

    /**
     * Returns link to memcached instance
     * @return MemcacheDriver|null
     */
    public function memcache()
    {
        if ($this->_memcached === null) {
            $this->_initMemcached();
        }
        return $this->_memcached;
    }


    /**
     * Returns link to config instance
     * @return ConfigIni
     */
    public function cfg()
    {
        return $this->_cfg;
    }

    /**
     * Returns specified param's value if exists, $defValue otherwise
     * @param $name
     * @param null $defValue
     * @return null
     */
    public function param($name, $defValue = null)
    {
        if (isset($_POST[$name]))
            $value = $_POST[$name];
        else if (isset($this->_request['params'][$name]))
            $value = $this->_request['params'][$name];
        else
            $value = $defValue;
        return $value;
    }

    /**
     * Initialises database
     */
    protected function _initDb()
    {
        if ($this->_db === null) {
            DbFactory::init($this->_cfg);
            $this->_db = DbFactory::_();
        }
    }

    /**
     * Initialises memcached
     */
    protected function _initMemcached()
    {
        if ($this->_memcached === null) {
            $this->_memcached = new MemcacheDriver($this->_cfg);
        }
    }

    /**
     * Checks if specified file is uploaded
     * @param string $name
     * @return bool
     */
    public function hasFile($name)
    {
        return isset($_FILES[$name]);
    }

    /**
     * Tries to moved specified uploaded file to specified location
     * @param string $name
     * @param string $destination
     * @return bool
     */
    public function moveFile($name, $destination)
    {
        //file_put_contents(ROOT_PATH . "/logs/move.log", "{$name} -> {$destination}\n", FILE_APPEND);
        Log::d('System', 'Moving: ' . $name . '(' . $_FILES[$name]['tmp_name'] . ') -> ' . $destination);
        return move_uploaded_file($_FILES[$name]['tmp_name'], $destination);
    }

    /**
     * Returns uploaded file temporary location
     * @param string $name
     * @return string|null
     */
    public function fileLocation($name)
    {
        return isset($_FILES[$name]) ? $_FILES[$name]['tmp_name'] : null;
    }

    /**
     * Reports if required fields are provided in request
     * @param array $fields
     * @return bool
     */
    public function hasRequired($fields = array())
    {
        if (empty($fields))
            return true;
        if (!is_array($fields))
            $fields = array($fields);
        foreach ($fields as $key) {
            if (!isset($_POST[$key]) && !isset($this->_request['params'][$key]))
                return false;
        }
        return true;
    }

    /**
     * Parses url for module/controller/method && params
     * @return System
     */
    protected function _parseUri()
    {
        if ($this->_cfg->{"NodeConfig::RelativeStart"} != '' && strpos(ltrim($this->_cfg->{"NodeConfig::RelativeStart"}, '/'), ltrim($_SERVER['REQUEST_URI'], '/')) == 0) {
            $path = ltrim(substr(ltrim($_SERVER['REQUEST_URI'], '/'), strlen(ltrim($this->_cfg->{"NodeConfig::RelativeStart"}, '/'))), '/');
        } else {
            $path = ltrim($_SERVER['REQUEST_URI'], '/');
        }

        $parts = explode('/', $path);

        $this->_request = array(
            'module' => $parts[0],
            'controller' => ucfirst($parts[1]),
            'method' => $parts[2],
            'params' => array()
        );

        if (count($parts) > 3) {
            for ($index = 3; $index < count($parts); $index++) {
                if ($parts[$index] != "") {
                    $param = explode(':', $parts[$index]);
                    $this->_request['params'][urldecode($param[0])] = isset($param[1]) ? urldecode($param[1]) : NULL;
                }
            }
        }

        return $this;
    }

    /**
     * Returns requested module
     * @return string
     */
    public function getModule()
    {
        if ($this->_request['module'] == '') {
            $this->_request['module'] = $this->_cfg->{'Modules::DefaultModule'};
        }
        return $this->_request['module'];
    }

    /**
     * Returns requested controller
     * @return string
     */
    public function getController()
    {
        if ($this->_request['controller'] == '') {
            $this->_request['controller'] = ucfirst($this->_cfg->{'Modules::DefaultController'});
        }
        return $this->_request['controller'];
    }

    /**
     * Returns requested method
     * @return string
     */
    public function getMethod()
    {
        if ($this->_request['method'] == '') {
            $this->_request['method'] = $this->_cfg->{'Modules::DefaultMethod'};
        }
        return $this->_request['method'];
    }

    /**
     * Returns namespace path to controller
     * @return string
     */
    public function getControllerClass()
    {
        return 'modules\\' . $this->getModule() . '\\control\\' . $this->getController();
    }

    /**
     * @var callable|null
     */
    protected $_delayedExecute = null;

    /**
     * Returns delayed execution function
     * @return callable|null
     */
    public function getDelayedExecute()
    {
        return $this->_delayedExecute;
    }

    /**
     * Sets delayed execution function
     * @param callable $callback
     */
    public function setDelayedExecute($callback)
    {
        $this->_delayedExecute = $callback;
    }

    /**
     * Gets full list of incoming params
     * @return array
     */
    public function getParamList()
    {
        return array_merge(array_keys($this->_request['params']), array_keys($_POST));
    }

    /**
     * Checks if class uses a trait. Injects class files if $autoLoad is set true
     * @param string $className
     * @param string $traitName
     * @param bool $autoLoad
     * @return bool
     */
    public static function class_uses($className, $traitName, $autoLoad = false)
    {
        $uses = class_uses($className, $autoLoad);
        $result = false;
        foreach ($uses as $use) {
            $parts = explode('\\', $use);
            if ($parts[count($parts) - 1] == $traitName) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Generates path to module directory
     * @param string|null $moduleName
     * @param bool $relative
     * @return string
     */
    public function getModulePath($moduleName = null, $relative = false)
    {
        $path = ($relative ? '' : ROOT_PATH) . '/modules/' . ($moduleName === null ? $this->getModule() : $moduleName) . '/';
        return $path;
    }


    /**
     * Generates path to module template
     * @param string|null $prefix
     * @param string|null $templateName
     * @return string
     */
    public function getTemplate($prefix = null, $templateName = null)
    {
        return $this->includeTemplatePath($this->getModule(), $prefix, $templateName);
    }


    /**
     * Generates template path for including
     * @param string|null $module
     * @param string|null $prefix
     * @param string|null $templateName
     * @return string
     */
    public function includeTemplatePath($module = null, $prefix = null, $templateName = null)
    {
        if ($module === null) {
            $template = ROOT_PATH . '/html/';
        } else {
            $template = $this->getModulePath($module) . 'templates/';
        }
        $template .= ($prefix === null ? '' : $prefix) . ($templateName === null ? $this->getController() . '_' . $this->getMethod() : $templateName) . '.phtml';
        return $template;
    }

    /**
     * Returns main domain of resource
     * @return mixed
     */
    public function getMainDomain()
    {
        if (null == $mainDomain = System::instance()->cfg()->{'Web::MainDomain'}) {
            if (defined('_MAIN_DOMAIN_')) {
                $mainDomain = _MAIN_DOMAIN_;
            } else {
                // @todo write alternative domain getter
            }
        }
        return $mainDomain;
    }

    /**
     * Sets response type
     * @param int|null $type
     */
    public function setResponseType($type = null)
    {
        $this->_responseType = $type;
    }

    /**
     * Returns response type
     * @return int|null
     */
    public function getResponseType()
    {
        return $this->_responseType;
    }

    /**
     * Converts web path to real file path for local files, and does nothing for http[s] files
     * @param string $webPath
     * @return string
     */
    public function webPath2Real($webPath)
    {
        if (!empty($this->_cfg->{'NodeConfig::RelativeStart'}) && strpos($webPath, $this->_cfg->{'NodeConfig::RelativeStart'}) === 0) {
            $path = substr($webPath, strlen($this->_cfg->{'NodeConfig::RelativeStart'}));
        } else {
            $path = $webPath;
        }
        if (substr($path, 0, 4) !== 'http') {
            $path = ROOT_PATH . $path;
        }
        return $path;
    }

    /**
     * Returns debug mode state
     * @return bool
     */
    public function debugAllowed()
    {
        return true;
    }

    /**
     * Returns current language
     * @return string
     */
    public function getCurrentLang()
    {
        return $this->_currentLanguage;
    }

    /**
     * Returns instance of language translator
     * @return Lang
     */
    public function lang()
    {
        if ($this->_lang === null) {
            $this->_lang = new Lang($this);
        }
        return $this->_lang;
    }

    /**
     * Detects current user language
     * @return System
     */
    public function detectLanguage()
    {

        if ($this->hasRequired(array('lang'))) {
            $this->_currentLanguage = $this->param('lang');
        } else if (isset($_SESSION['lang'])) {
            $this->_currentLanguage = $_SESSION['lang'];
        } else if (isset($_COOKIE['lang'])) {
            $this->_currentLanguage = $_COOKIE['lang'];
        } else if (null != $al = $this->findLangInHttpHeader()) {
            $this->_currentLanguage = $al;
        } else {
            $this->_currentLanguage = $this->lang()->getDefaultLang();
        }
        $_SESSION['lang'] = $this->_currentLanguage;
        return $this;
    }

    /**
     * Returns accepted language exists in system and null otherwise
     * @return null|string
     */
    protected function findLangInHttpHeader()
    {
        $parts = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $languagesList = StaticData::loadStaticData('languages', 'lang_code');
        $maxWeight = 0;
        $maxLang = null;
        foreach ($parts as $part) {
            if (preg_match('/([a-z]{2})([-_][a-z]{2}|)(;q=([0-9\.]{1,4})|)/i', $part, $m)) {
                $weight = $m[4] !== null ? $m[4] : 1;
                if (isset($languagesList[$m[1]]) && $weight > $maxWeight) {
                    $maxLang = $m[1];
                }
                if ($maxWeight == 1) {
                    break;
                }
            }
        }
        return $maxLang;
    }
}